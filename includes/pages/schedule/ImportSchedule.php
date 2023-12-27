<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin\Schedule;

use Engelsystem\Controllers\NotificationType;
use Engelsystem\Helpers\Carbon;
use DateTimeInterface;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Schedule\Event;
use Engelsystem\Helpers\Schedule\Room;
use Engelsystem\Helpers\Schedule\Schedule;
use Engelsystem\Helpers\Schedule\XmlParser;
use Engelsystem\Helpers\Uuid;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\Schedule as ScheduleUrl;
use Engelsystem\Models\Shifts\ScheduleShift;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftType;
use Engelsystem\Models\User\User;
use ErrorException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Database\Connection as DatabaseConnection;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Collection as DatabaseCollection;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ImportSchedule extends BaseController
{
    use HasUserNotifications;

    /** @var DatabaseConnection */
    protected $db;

    /** @var LoggerInterface */
    protected $log;

    protected array $permissions = [
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

    public function index(): Response
    {
        return $this->response->withView(
            'admin/schedule/index.twig',
            [
                'is_index'  => true,
                'schedules' => ScheduleUrl::all(),
            ]
        );
    }

    public function edit(Request $request): Response
    {
        $scheduleId = $request->getAttribute('schedule_id'); // optional
        $schedule = ScheduleUrl::find($scheduleId);

        return $this->response->withView(
            'admin/schedule/edit.twig',
            [
                'schedule'    => $schedule,
                'shift_types' => ShiftType::all()->sortBy('name')->pluck('name', 'id'),
                'locations'   => Location::all()->sortBy('name')->pluck('name', 'id'),
            ]
        );
    }

    public function save(Request $request): Response
    {
        $scheduleId = $request->getAttribute('schedule_id'); // optional

        /** @var ScheduleUrl $schedule */
        $schedule = ScheduleUrl::findOrNew($scheduleId);

        if ($request->request->has('delete')) {
            return $this->delete($schedule);
        }

        $locationsList = Location::all()->pluck('id');
        $locationsValidation = [];
        foreach ($locationsList as $id) {
            $locationsValidation['location_' . $id] = 'optional|checked';
        }

        $data = $this->validate($request, [
            'name'           => 'required',
            'url'            => 'required',
            'shift_type'     => 'required|int',
            'needed_from_shift_type' => 'optional|checked',
            'minutes_before' => 'int',
            'minutes_after'  => 'int',
        ] + $locationsValidation);

        if (!ShiftType::find($data['shift_type'])) {
            throw new ErrorException('schedule.import.invalid-shift-type');
        }

        $schedule->name = $data['name'];
        $schedule->url = $data['url'];
        $schedule->shift_type = $data['shift_type'];
        $schedule->needed_from_shift_type = (bool) $data['needed_from_shift_type'];
        $schedule->minutes_before = $data['minutes_before'];
        $schedule->minutes_after = $data['minutes_after'];

        $schedule->save();
        $schedule->activeLocations()->detach();

        $for = new Collection();
        foreach ($locationsList as $id) {
            if (!$data['location_' . $id]) {
                continue;
            }

            $location = Location::find($id);
            $schedule->activeLocations()->attach($location);
            $for[] = $location->name;
        }

        $this->log->info(
            'Schedule {name}: Url {url}, Shift Type {shift_type}, ({need}), '
            . 'minutes before/after {before}/{after}, for: {locations}',
            [
                'name'       => $schedule->name,
                'url'        => $schedule->name,
                'shift_type' => $schedule->shift_type,
                'need'       => $schedule->needed_from_shift_type ? 'from shift type' : 'from room',
                'before'     => $schedule->minutes_before,
                'after'      => $schedule->minutes_after,
                'locations'  => $for->implode(', '),
            ]
        );

        $this->addNotification('schedule.edit.success');

        return redirect('/admin/schedule/load/' . $schedule->id);
    }

    protected function delete(ScheduleUrl $schedule): Response
    {
        foreach ($schedule->scheduleShifts as $scheduleShift) {
            // Only guid is needed here
            $event = new Event(
                $scheduleShift->guid,
                0,
                new Room(''),
                '',
                '',
                '',
                Carbon::now(),
                '',
                '',
                '',
                '',
                ''
            );

            $this->deleteEvent($event, $schedule);
        }
        $schedule->delete();

        $this->addNotification('schedule.delete.success');
        return redirect('/admin/schedule');
    }

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
            $this->addNotification($e->getMessage(), NotificationType::ERROR);
            return back();
        }

        return $this->response->withView(
            'admin/schedule/load.twig',
            [
                'schedule_id' => $scheduleUrl->id,
                'schedule'    => $schedule,
                'locations'   => [
                    'add' => $newRooms,
                ],
                'shifts'      => [
                    'add'    => $newEvents,
                    'update' => $changeEvents,
                    'delete' => $deleteEvents,
                ],
            ]
        );
    }

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
            $this->addNotification($e->getMessage(), NotificationType::ERROR);
            return back();
        }

        $this->log('Started schedule "{name}" import', ['name' => $scheduleUrl->name]);

        foreach ($newRooms as $room) {
            $this->createLocation($room);
        }

        $locations = $this->getAllLocations();
        foreach ($newEvents as $event) {
            $this->createEvent(
                $event,
                $shiftType,
                $locations
                    ->where('name', $event->getRoom()->getName())
                    ->first(),
                $scheduleUrl
            );
        }

        foreach ($changeEvents as $event) {
            $this->updateEvent(
                $event,
                $shiftType,
                $locations
                    ->where('name', $event->getRoom()->getName())
                    ->first(),
                $scheduleUrl
            );
        }

        foreach ($deleteEvents as $event) {
            $this->deleteEvent($event, $scheduleUrl);
        }

        $scheduleUrl->touch();
        $this->log('Ended schedule "{name}" import', ['name' => $scheduleUrl->name]);

        $this->addNotification('schedule.import.success');
        return redirect($this->url, 303);
    }

    protected function createLocation(Room $room): void
    {
        $location = new Location();
        $location->name = $room->getName();
        $location->save();

        $this->log('Created schedule location "{location}"', ['location' => $room->getName()]);
    }

    protected function fireDeleteShiftEntryEvents(Event $event, ScheduleUrl $schedule): void
    {
        $shiftEntries = $this->db
            ->table('shift_entries')
            ->select([
                'shift_types.name', 'shifts.title', 'angel_types.name AS type', 'locations.id AS location_id',
                'shifts.start', 'shifts.end', 'shift_entries.user_id', 'shift_entries.freeloaded',
            ])
            ->join('shifts', 'shifts.id', 'shift_entries.shift_id')
            ->join('schedule_shift', 'shifts.id', 'schedule_shift.shift_id')
            ->join('locations', 'locations.id', 'shifts.location_id')
            ->join('angel_types', 'angel_types.id', 'shift_entries.angel_type_id')
            ->join('shift_types', 'shift_types.id', 'shifts.shift_type_id')
            ->where('schedule_shift.guid', $event->getGuid())
            ->where('schedule_shift.schedule_id', $schedule->id)
            ->get();

        foreach ($shiftEntries as $shiftEntry) {
            event('shift.entry.deleting', [
                'user'       => User::find($shiftEntry->user_id),
                'start'      => Carbon::make($shiftEntry->start),
                'end'        => Carbon::make($shiftEntry->end),
                'name'       => $shiftEntry->name,
                'title'      => $shiftEntry->title,
                'type'       => $shiftEntry->type,
                'location'   => Location::find($shiftEntry->location_id),
                'freeloaded' => $shiftEntry->freeloaded,
            ]);
        }
    }

    protected function createEvent(Event $event, int $shiftTypeId, Location $location, ScheduleUrl $scheduleUrl): void
    {
        $user = auth()->user();
        $eventTimeZone = Carbon::now()->timezone;

        $shift = new Shift();
        $shift->title = $event->getTitle();
        $shift->shift_type_id = $shiftTypeId;
        $shift->start = $event->getDate()->copy()->timezone($eventTimeZone);
        $shift->end = $event->getEndDate()->copy()->timezone($eventTimeZone);
        $shift->location()->associate($location);
        $shift->url = $event->getUrl() ?? '';
        $shift->transaction_id = Uuid::uuidBy($scheduleUrl->id, '5c4ed01e');
        $shift->createdBy()->associate($user);
        $shift->save();

        $scheduleShift = new ScheduleShift(['guid' => $event->getGuid()]);
        $scheduleShift->schedule()->associate($scheduleUrl);
        $scheduleShift->shift()->associate($shift);
        $scheduleShift->save();

        $this->log(
            'Created schedule shift "{shift}" in "{location}" ({from} {to}, {guid})',
            [
                'shift'    => $shift->title,
                'location' => $shift->location->name,
                'from'     => $shift->start->format(DateTimeInterface::RFC3339),
                'to'       => $shift->end->format(DateTimeInterface::RFC3339),
                'guid'     => $scheduleShift->guid,
            ]
        );
    }

    protected function updateEvent(Event $event, int $shiftTypeId, Location $location, ScheduleUrl $schedule): void
    {
        $user = auth()->user();
        $eventTimeZone = Carbon::now()->timezone;

        /** @var ScheduleShift $scheduleShift */
        $scheduleShift = ScheduleShift::whereGuid($event->getGuid())->where('schedule_id', $schedule->id)->first();
        $shift = $scheduleShift->shift;
        $oldShift = Shift::find($shift->id);
        $shift->title = $event->getTitle();
        $shift->shift_type_id = $shiftTypeId;
        $shift->start = $event->getDate()->copy()->timezone($eventTimeZone);
        $shift->end = $event->getEndDate()->copy()->timezone($eventTimeZone);
        $shift->location()->associate($location);
        $shift->url = $event->getUrl() ?? '';
        $shift->updatedBy()->associate($user);
        $shift->save();

        $this->fireUpdateShiftUpdateEvent($oldShift, $shift);

        $this->log(
            'Updated schedule shift "{shift}" in "{location}" ({from} {to}, {guid})',
            [
                'shift'    => $shift->title,
                'location' => $shift->location->name,
                'from'     => $shift->start->format(DateTimeInterface::RFC3339),
                'to'       => $shift->end->format(DateTimeInterface::RFC3339),
                'guid'     => $scheduleShift->guid,
            ]
        );
    }

    protected function deleteEvent(Event $event, ScheduleUrl $schedule): void
    {
        /** @var ScheduleShift $scheduleShift */
        $scheduleShift = ScheduleShift::whereGuid($event->getGuid())->where('schedule_id', $schedule->id)->first();
        $shift = $scheduleShift->shift;
        $shift->delete();
        $scheduleShift->delete();

        $this->fireDeleteShiftEntryEvents($event, $schedule);

        $this->log(
            'Deleted schedule shift "{shift}" in {location} ({from} {to}, {guid})',
            [
                'shift'    => $shift->title,
                'location' => $shift->location->name,
                'from'     => $shift->start->format(DateTimeInterface::RFC3339),
                'to'       => $shift->end->format(DateTimeInterface::RFC3339),
                'guid'     => $scheduleShift->guid,
            ]
        );
    }

    protected function fireUpdateShiftUpdateEvent(Shift $oldShift, Shift $newShift): void
    {
        event('shift.updating', [
            'shift' => $newShift,
            'oldShift' => $oldShift,
        ]);
    }

    /**
     * @param Request $request
     * @return Event[]|Room[]|Location[]
     * @throws ErrorException
     */
    protected function getScheduleData(Request $request)
    {
        $scheduleId = (int) $request->getAttribute('schedule_id');

        /** @var ScheduleUrl $scheduleUrl */
        $scheduleUrl = ScheduleUrl::findOrFail($scheduleId);

        try {
            $scheduleResponse = $this->guzzle->get($scheduleUrl->url);
        } catch (ConnectException $e) {
            throw new ErrorException('schedule.import.request-error');
        }

        if ($scheduleResponse->getStatusCode() != 200) {
            throw new ErrorException('schedule.import.request-error');
        }

        $scheduleData = (string) $scheduleResponse->getBody();
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
        $allLocations = $this->getAllLocations();

        foreach ($scheduleRooms as $room) {
            if ($allLocations->where('name', $room->getName())->count()) {
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
        $locations = $this->getAllLocations();
        $eventTimeZone = Carbon::now()->timezone;

        foreach ($schedule->getDay() as $day) {
            foreach ($day->getRoom() as $room) {
                if (!$scheduleUrl->activeLocations->where('name', $room->getName())->count()) {
                    continue;
                }

                foreach ($room->getEvent() as $event) {
                    $scheduleEvents[$event->getGuid()] = $event;

                    $event->getDate()->timezone($eventTimeZone)->subMinutes($minutesBefore);
                    $event->getEndDate()->timezone($eventTimeZone)->addMinutes($minutesAfter);
                    $event->setTitle(
                        $event->getLanguage()
                            ? sprintf('%s [%s]', $event->getTitle(), $event->getLanguage())
                            : $event->getTitle()
                    );
                }
            }
        }

        $scheduleEventsGuidList = array_keys($scheduleEvents);
        $existingShifts = $this->getScheduleShiftsByGuid($scheduleUrl, $scheduleEventsGuidList);
        foreach ($existingShifts as $scheduleShift) {
            $guid = $scheduleShift->guid;
            $shift = $scheduleShift->shift;
            $event = $scheduleEvents[$guid];
            $location = $locations->where('name', $event->getRoom()->getName())->first();

            if (
                $shift->title != $event->getTitle()
                || $shift->shift_type_id != $shiftType
                || $shift->start != $event->getDate()
                || $shift->end != $event->getEndDate()
                || $shift->location_id != ($location->id ?? '')
                || $shift->url != ($event->getUrl() ?? '')
            ) {
                $changeEvents[$guid] = $event;
            }

            unset($scheduleEvents[$guid]);
        }

        foreach ($scheduleEvents as $scheduleEvent) {
            $newEvents[$scheduleEvent->getGuid()] = $scheduleEvent;
        }

        $scheduleShifts = $this->getScheduleShiftsWhereNotGuid($scheduleUrl, $scheduleEventsGuidList);
        foreach ($scheduleShifts as $scheduleShift) {
            $event = $this->eventFromScheduleShift($scheduleShift);
            $deleteEvents[$event->getGuid()] = $event;
        }

        return [$newEvents, $changeEvents, $deleteEvents];
    }

    protected function eventFromScheduleShift(ScheduleShift $scheduleShift): Event
    {
        $shift = $scheduleShift->shift;
        $duration = $shift->start->diff($shift->end);

        return new Event(
            $scheduleShift->guid,
            0,
            new Room($shift->location->name),
            $shift->title,
            '',
            'n/a',
            $shift->start,
            $shift->start->format('H:i'),
            $duration->format('%H:%I'),
            '',
            '',
            ''
        );
    }

    /**
     * @return Location[]|Collection
     */
    protected function getAllLocations(): Collection
    {
        return Location::all();
    }

    /**
     * @param ScheduleUrl $scheduleUrl
     * @param string[]    $events
     * @return QueryBuilder[]|DatabaseCollection|Collection|ScheduleShift[]
     */
    protected function getScheduleShiftsByGuid(ScheduleUrl $scheduleUrl, array $events)
    {
        return ScheduleShift::with('shift.location')
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
        return ScheduleShift::with('shift.location')
            ->whereNotIn('guid', $events)
            ->where('schedule_id', $scheduleUrl->id)
            ->get();
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
