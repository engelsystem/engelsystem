<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin\Schedule;

use Carbon\Carbon;
use DateTimeInterface;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Schedule\Event;
use Engelsystem\Helpers\Schedule\Room;
use Engelsystem\Helpers\Schedule\Schedule;
use Engelsystem\Helpers\Schedule\XmlParser;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Room as RoomModel;
use Engelsystem\Models\Shifts\Schedule as ScheduleUrl;
use Engelsystem\Models\Shifts\ScheduleShift;
use ErrorException;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Database\Connection as DatabaseConnection;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Collection as DatabaseCollection;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ImportSchedule extends BaseController
{
    use HasUserNotifications;

    /** @var DatabaseConnection */
    protected $db;

    /** @var LoggerInterface */
    protected $log;

    /** @var array */
    protected $permissions = [
        'schedule.import',
    ];

    /** @var XmlParser */
    protected $parser;

    /** @var Response */
    protected $response;

    /** @var SessionInterface */
    protected $session;

    /** @var string */
    protected $url = '/admin/schedule';

    /** @var GuzzleClient */
    protected $guzzle;

    /**
     * @param Response           $response
     * @param SessionInterface   $session
     * @param GuzzleClient       $guzzle
     * @param XmlParser          $parser
     * @param DatabaseConnection $db
     * @param LoggerInterface    $log
     */
    public function __construct(
        Response $response,
        SessionInterface $session,
        GuzzleClient $guzzle,
        XmlParser $parser,
        DatabaseConnection $db,
        LoggerInterface $log
    ) {
        $this->guzzle = $guzzle;
        $this->parser = $parser;
        $this->response = $response;
        $this->session = $session;
        $this->db = $db;
        $this->log = $log;
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->response->withView(
            'admin/schedule/index.twig',
            [
                'is_index'  => true,
                'schedules' => ScheduleUrl::all(),
            ] + $this->getNotifications()
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Request $request): Response
    {
        $schedule = ScheduleUrl::find($request->getAttribute('id'));

        return $this->response->withView(
            'admin/schedule/edit.twig',
            [
                'schedule'    => $schedule,
                'shift_types' => $this->getShiftTypes(),
            ] + $this->getNotifications()
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function save(Request $request): Response
    {
        $id = $request->getAttribute('id');
        /** @var ScheduleUrl $schedule */
        $schedule = ScheduleUrl::findOrNew($id);

        $data = $this->validate($request, [
            'name'           => 'required',
            'url'            => 'required',
            'shift_type'     => 'required|int',
            'minutes_before' => 'int',
            'minutes_after'  => 'int',
        ]);

        if (!isset($this->getShiftTypes()[$data['shift_type']])) {
            throw new ErrorException('schedule.import.invalid-shift-type');
        }

        $schedule->name = $data['name'];
        $schedule->url = $data['url'];
        $schedule->shift_type = $data['shift_type'];
        $schedule->minutes_before = $data['minutes_before'];
        $schedule->minutes_after = $data['minutes_after'];

        $schedule->save();

        $this->log->info(
            'Schedule {name}: Url {url}, Shift Type {shift_type}, minutes before/after {before}/{after}',
            [
                'name'       => $schedule->name,
                'url'        => $schedule->name,
                'shift_type' => $schedule->shift_type,
                'before'     => $schedule->minutes_before,
                'after'      => $schedule->minutes_after,
            ]
        );

        $this->addNotification('schedule.edit.success');

        return redirect('/admin/schedule/load/' . $schedule->id);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function loadSchedule(Request $request): Response
    {
        try {
            /**
             * @var Event[]     $newEvents
             * @var Event[]     $changeEvents
             * @var Event[]     $deleteEvents
             * @var Room[]      $newRooms
             * @var int         $shiftType
             * @var ScheduleUrl $scheduleUrl
             * @var Schedule    $schedule
             * @var int         $minutesBefore
             * @var int         $minutesAfter
             */
            list(
                $newEvents,
                $changeEvents,
                $deleteEvents,
                $newRooms,
                ,
                $scheduleUrl,
                $schedule
                ) = $this->getScheduleData($request);
        } catch (ErrorException $e) {
            $this->addNotification($e->getMessage(), 'errors');
            return back();
        }

        return $this->response->withView(
            'admin/schedule/load.twig',
            [
                'schedule_id'    => $scheduleUrl->id,
                'schedule'       => $schedule,
                'rooms'          => [
                    'add' => $newRooms,
                ],
                'shifts'         => [
                    'add'    => $newEvents,
                    'update' => $changeEvents,
                    'delete' => $deleteEvents,
                ],
            ] + $this->getNotifications()
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function importSchedule(Request $request): Response
    {
        try {
            /**
             * @var Event[]     $newEvents
             * @var Event[]     $changeEvents
             * @var Event[]     $deleteEvents
             * @var Room[]      $newRooms
             * @var int         $shiftType
             * @var ScheduleUrl $scheduleUrl
             */
            list(
                $newEvents,
                $changeEvents,
                $deleteEvents,
                $newRooms,
                $shiftType,
                $scheduleUrl
                ) = $this->getScheduleData($request);
        } catch (ErrorException $e) {
            $this->addNotification($e->getMessage(), 'errors');
            return back();
        }

        $this->log('Started schedule "{name}" import', ['name' => $scheduleUrl->name]);

        foreach ($newRooms as $room) {
            $this->createRoom($room);
        }

        $rooms = $this->getAllRooms();
        foreach ($newEvents as $event) {
            $this->createEvent(
                $event,
                $shiftType,
                $rooms
                    ->where('name', $event->getRoom()->getName())
                    ->first(),
                $scheduleUrl
            );
        }

        foreach ($changeEvents as $event) {
            $this->updateEvent(
                $event,
                $shiftType,
                $rooms
                    ->where('name', $event->getRoom()->getName())
                    ->first()
            );
        }

        foreach ($deleteEvents as $event) {
            $this->deleteEvent($event);
        }

        $scheduleUrl->touch();
        $this->log('Ended schedule "{name}" import', ['name' => $scheduleUrl->name]);

        return redirect($this->url, 303)
            ->with('messages', ['schedule.import.success']);
    }

    /**
     * @param Room $room
     */
    protected function createRoom(Room $room): void
    {
        $roomModel = new RoomModel();
        $roomModel->name = $room->getName();
        $roomModel->save();

        $this->log('Created schedule room "{room}"', ['room' => $room->getName()]);
    }

    /**
     * @param Event       $shift
     * @param int         $shiftTypeId
     * @param RoomModel   $room
     * @param ScheduleUrl $scheduleUrl
     */
    protected function createEvent(Event $shift, int $shiftTypeId, RoomModel $room, ScheduleUrl $scheduleUrl): void
    {
        $user = auth()->user();

        $this->db
            ->table('Shifts')
            ->insert(
                [
                    'title'                => $shift->getTitle(),
                    'shifttype_id'         => $shiftTypeId,
                    'start'                => $shift->getDate()->unix(),
                    'end'                  => $shift->getEndDate()->unix(),
                    'RID'                  => $room->id,
                    'URL'                  => $shift->getUrl(),
                    'created_by_user_id'   => $user->id,
                    'created_at_timestamp' => time(),
                    'edited_by_user_id'    => null,
                    'edited_at_timestamp'  => 0,
                ]
            );

        $shiftId = $this->db->getDoctrineConnection()->lastInsertId();

        $scheduleShift = new ScheduleShift(['shift_id' => $shiftId, 'guid' => $shift->getGuid()]);
        $scheduleShift->schedule()->associate($scheduleUrl);
        $scheduleShift->save();

        $this->log(
            'Created schedule shift "{shift}" in "{room}" ({from} {to}, {guid})',
            [
                'shift' => $shift->getTitle(),
                'room'  => $room->name,
                'from'  => $shift->getDate()->format(DateTimeInterface::RFC3339),
                'to'    => $shift->getEndDate()->format(DateTimeInterface::RFC3339),
                'guid'  => $shift->getGuid(),
            ]
        );
    }

    /**
     * @param Event     $shift
     * @param int       $shiftTypeId
     * @param RoomModel $room
     */
    protected function updateEvent(Event $shift, int $shiftTypeId, RoomModel $room): void
    {
        $user = auth()->user();

        $this->db
            ->table('Shifts')
            ->join('schedule_shift', 'Shifts.SID', 'schedule_shift.shift_id')
            ->where('schedule_shift.guid', $shift->getGuid())
            ->update(
                [
                    'title'               => $shift->getTitle(),
                    'shifttype_id'        => $shiftTypeId,
                    'start'               => $shift->getDate()->unix(),
                    'end'                 => $shift->getEndDate()->unix(),
                    'RID'                 => $room->id,
                    'URL'                 => $shift->getUrl(),
                    'edited_by_user_id'   => $user->id,
                    'edited_at_timestamp' => time(),
                ]
            );

        $this->log(
            'Updated schedule shift "{shift}" in "{room}" ({from} {to}, {guid})',
            [
                'shift' => $shift->getTitle(),
                'room'  => $room->name,
                'from'  => $shift->getDate()->format(DateTimeInterface::RFC3339),
                'to'    => $shift->getEndDate()->format(DateTimeInterface::RFC3339),
                'guid'  => $shift->getGuid(),
            ]
        );
    }

    /**
     * @param Event $shift
     */
    protected function deleteEvent(Event $shift): void
    {
        $this->db
            ->table('Shifts')
            ->join('schedule_shift', 'Shifts.SID', 'schedule_shift.shift_id')
            ->where('schedule_shift.guid', $shift->getGuid())
            ->delete();

        $this->log(
            'Deleted schedule shift "{shift}" ({from} {to}, {guid})',
            [
                'shift' => $shift->getTitle(),
                'from'  => $shift->getDate()->format(DateTimeInterface::RFC3339),
                'to'    => $shift->getEndDate()->format(DateTimeInterface::RFC3339),
                'guid'  => $shift->getGuid(),
            ]
        );
    }

    /**
     * @param Request $request
     * @return Event[]|Room[]|RoomModel[]
     * @throws ErrorException
     */
    protected function getScheduleData(Request $request)
    {
        $id = $request->getAttribute('id');
        /** @var ScheduleUrl $scheduleUrl */
        $scheduleUrl = ScheduleUrl::findOrFail($id);

        $scheduleResponse = $this->guzzle->get($scheduleUrl->url);
        if ($scheduleResponse->getStatusCode() != 200) {
            throw new ErrorException('schedule.import.request-error');
        }

        $scheduleData = (string)$scheduleResponse->getBody();
        if (!$this->parser->load($scheduleData)) {
            throw new ErrorException('schedule.import.read-error');
        }

        $shiftType = $scheduleUrl->shift_type;
        $schedule = $this->parser->getSchedule();
        $minutesBefore = $scheduleUrl->minutes_before;
        $minutesAfter = $scheduleUrl->minutes_after;
        $newRooms = $this->newRooms($schedule->getRooms());
        return array_merge(
            $this->shiftsDiff($schedule, $scheduleUrl, $shiftType, $minutesBefore, $minutesAfter),
            [$newRooms, $shiftType, $scheduleUrl, $schedule, $minutesBefore, $minutesAfter]
        );
    }

    /**
     * @param Room[] $scheduleRooms
     * @return Room[]
     */
    protected function newRooms(array $scheduleRooms): array
    {
        $newRooms = [];
        $allRooms = $this->getAllRooms();

        foreach ($scheduleRooms as $room) {
            if ($allRooms->where('name', $room->getName())->count()) {
                continue;
            }

            $newRooms[] = $room;
        }

        return $newRooms;
    }

    /**
     * @param Schedule    $schedule
     * @param ScheduleUrl $scheduleUrl
     * @param int         $shiftType
     * @param int         $minutesBefore
     * @param int         $minutesAfter
     * @return Event[]
     */
    protected function shiftsDiff(
        Schedule $schedule,
        ScheduleUrl $scheduleUrl,
        int $shiftType,
        int $minutesBefore,
        int $minutesAfter
    ): array {
        /** @var Event[] $newEvents */
        $newEvents = [];
        /** @var Event[] $changeEvents */
        $changeEvents = [];
        /** @var Event[] $scheduleEvents */
        $scheduleEvents = [];
        /** @var Event[] $deleteEvents */
        $deleteEvents = [];
        $rooms = $this->getAllRooms();

        foreach ($schedule->getDay() as $day) {
            foreach ($day->getRoom() as $room) {
                foreach ($room->getEvent() as $event) {
                    $scheduleEvents[$event->getGuid()] = $event;

                    $event->getDate()->subMinutes($minutesBefore);
                    $event->getEndDate()->addMinutes($minutesAfter);
                    $event->setTitle(sprintf('%s [%s]', $event->getTitle(), $event->getLanguage()));
                }
            }
        }

        $scheduleEventsGuidList = array_keys($scheduleEvents);
        $existingShifts = $this->getScheduleShiftsByGuid($scheduleUrl, $scheduleEventsGuidList);
        foreach ($existingShifts as $shift) {
            $guid = $shift->guid;
            $shift = $this->loadShift($shift->shift_id);
            $event = $scheduleEvents[$guid];
            $room = $rooms->where('name', $event->getRoom()->getName())->first();

            if (
                $shift->title != $event->getTitle()
                || $shift->shift_type_id != $shiftType
                || Carbon::createFromTimestamp($shift->start) != $event->getDate()
                || Carbon::createFromTimestamp($shift->end) != $event->getEndDate()
                || $shift->room_id != ($room->id ?? '')
                || $shift->url != $event->getUrl()
            ) {
                $changeEvents[$guid] = $event;
            }

            unset($scheduleEvents[$guid]);
        }

        foreach ($scheduleEvents as $scheduleEvent) {
            $newEvents[$scheduleEvent->getGuid()] = $scheduleEvent;
        }

        $scheduleShifts = $this->getScheduleShiftsWhereNotGuid($scheduleUrl, $scheduleEventsGuidList);
        foreach ($scheduleShifts as $shift) {
            $event = $this->eventFromScheduleShift($shift);
            $deleteEvents[$event->getGuid()] = $event;
        }

        return [$newEvents, $changeEvents, $deleteEvents];
    }

    /**
     * @param ScheduleShift $scheduleShift
     * @return Event
     */
    protected function eventFromScheduleShift(ScheduleShift $scheduleShift): Event
    {
        $shift = $this->loadShift($scheduleShift->shift_id);
        $start = Carbon::createFromTimestamp($shift->start);
        $end = Carbon::createFromTimestamp($shift->end);
        $duration = $start->diff($end);

        return new Event(
            $scheduleShift->guid,
            0,
            new Room($shift->room_name),
            $shift->title,
            '',
            'n/a',
            Carbon::createFromTimestamp($shift->start),
            $start->format('H:i'),
            $duration->format('%H:%I'),
            '',
            '',
            ''
        );
    }

    /**
     * @return RoomModel[]|Collection
     */
    protected function getAllRooms(): Collection
    {
        return RoomModel::all();
    }

    /**
     * @param ScheduleUrl $scheduleUrl
     * @param string[]    $events
     * @return QueryBuilder[]|DatabaseCollection|Collection|ScheduleShift[]
     */
    protected function getScheduleShiftsByGuid(ScheduleUrl $scheduleUrl, array $events)
    {
        return ScheduleShift::query()
            ->whereIn('guid', $events)
            ->where('schedule_id', $scheduleUrl->id)
            ->get();
    }

    /**
     * @param ScheduleUrl $scheduleUrl
     * @param string[]    $events
     * @return QueryBuilder[]|DatabaseCollection|Collection|ScheduleShift[]
     */
    protected function getScheduleShiftsWhereNotGuid(ScheduleUrl $scheduleUrl, array $events)
    {
        return ScheduleShift::query()
            ->whereNotIn('guid', $events)
            ->where('schedule_id', $scheduleUrl->id)
            ->get();
    }

    /**
     * @param $id
     * @return stdClass|null
     */
    protected function loadShift($id): ?stdClass
    {
        return $this->db->selectOne(
            '
            SELECT
                s.SID AS id,
                s.title,
                s.start,
                s.end,
                s.shifttype_id AS shift_type_id,
                s.RID AS room_id,
                r.Name AS room_name,
                s.URL as url
            FROM Shifts AS s
            LEFT JOIN rooms r on s.RID = r.id
            WHERE SID = ?
            ',
            [$id]
        );
    }

    /**
     * @return string[]
     */
    protected function getShiftTypes()
    {
        $return = [];
        /** @var stdClass[] $shiftTypes */
        $shiftTypes = $this->db->select('SELECT t.id, t.name FROM ShiftTypes AS t');

        foreach ($shiftTypes as $shiftType) {
            $return[$shiftType->id] = $shiftType->name;
        }

        return $return;
    }

    /**
     * @param string $message
     * @param array  $context
     */
    protected function log(string $message, array $context = []): void
    {
        $this->log->info($message, $context);
    }
}
