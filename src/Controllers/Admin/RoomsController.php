<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
use Engelsystem\Models\Shifts\NeededAngelType;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;

class RoomsController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'admin_rooms',
    ];

    public function __construct(
        protected LoggerInterface $log,
        protected Room $room,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function index(): Response
    {
        $rooms = $this->room
            ->orderBy('name')
            ->get();

        return $this->response->withView(
            'admin/rooms/index',
            ['rooms' => $rooms, 'is_index' => true]
        );
    }

    public function edit(Request $request): Response
    {
        $roomId = (int) $request->getAttribute('room_id');

        $room = $this->room->find($roomId);

        return $this->showEdit($room);
    }

    public function save(Request $request): Response
    {
        $roomId = (int) $request->getAttribute('room_id');

        /** @var Room $room */
        $room = $this->room->findOrNew($roomId);
        /** @var Collection|AngelType[] $angelTypes */
        $angelTypes = AngelType::all();
        $validation = [];
        foreach ($angelTypes as $angelType) {
            $validation['angel_type_' . $angelType->id] = 'optional|int';
        }

        if ($request->request->has('delete')) {
            return $this->delete($request);
        }

        $data = $this->validate(
            $request,
            [
                'name'        => 'required',
                'description' => 'required|optional',
                'dect'        => 'required|optional',
                'map_url'     => 'optional|url',
            ] + $validation
        );

        if (Room::whereName($data['name'])->where('id', '!=', $room->id)->exists()) {
            throw new ValidationException((new Validator())->addErrors(['name' => ['validation.name.exists']]));
        }

        $room->name = $data['name'];
        $room->description = $data['description'];
        $room->dect = $data['dect'];
        $room->map_url = $data['map_url'];

        $room->save();
        $room->neededAngelTypes()->getQuery()->delete();
        $angelsInfo = '';

        foreach ($angelTypes as $angelType) {
            $count = $data['angel_type_' . $angelType->id];
            if (!$count) {
                continue;
            }

            $neededAngelType = new NeededAngelType();

            $neededAngelType->room()->associate($room);
            $neededAngelType->angelType()->associate($angelType);

            $neededAngelType->count = $data['angel_type_' . $angelType->id];

            $neededAngelType->save();

            $angelsInfo .= sprintf(', %s: %s', $angelType->name, $count);
        }

        $this->log->info(
            'Updated room "{name}": {description} {dect} {map_url} {angels}',
            [
                'name'        => $room->name,
                'description' => $room->description,
                'dect'        => $room->dect,
                'map_url'     => $room->map_url,
                'angels'      => $angelsInfo,
            ]
        );

        $this->addNotification('room.edit.success');

        return $this->redirect->to('/admin/rooms');
    }

    public function delete(Request $request): Response
    {
        $data = $this->validate($request, [
            'id'     => 'required|int',
            'delete' => 'checked',
        ]);

        $room = $this->room->findOrFail($data['id']);

        $shifts = $room->shifts;
        foreach ($shifts as $shift) {
            foreach ($shift->shiftEntries as $entry) {
                event('shift.entry.deleting', [
                    'user'       => $entry->user,
                    'start'      => $shift->start,
                    'end'        => $shift->end,
                    'name'       => $shift->shiftType->name,
                    'title'      => $shift->title,
                    'type'       => $entry->angelType->name,
                    'room'       => $room,
                    'freeloaded' => $entry->freeloaded,
                ]);
            }
        }
        $room->delete();

        $this->log->info('Deleted room {room}', ['room' => $room->name]);
        $this->addNotification('room.delete.success');

        return $this->redirect->to('/admin/rooms');
    }

    protected function showEdit(?Room $room): Response
    {
        $angeltypes = AngelType::all()
            ->sortBy('name');

        return $this->response->withView(
            'admin/rooms/edit',
            ['room' => $room, 'angel_types' => $angeltypes, 'needed_angel_types' => $room?->neededAngelTypes]
        );
    }
}
