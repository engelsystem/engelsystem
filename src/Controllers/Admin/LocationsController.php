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
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\NeededAngelType;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;

class LocationsController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'admin_locations',
    ];

    public function __construct(
        protected LoggerInterface $log,
        protected Location $location,
        protected Redirector $redirect,
        protected Response $response
    ) {
    }

    public function index(): Response
    {
        $locations = $this->location
            ->withCount('shifts')
            ->orderBy('name')
            ->get();

        return $this->response->withView(
            'admin/locations/index',
            [
                'locations' => $locations,
                'is_index' => true,
            ]
        );
    }

    public function edit(Request $request): Response
    {
        $locationId = (int) $request->getAttribute('location_id');

        $location = $this->location->find($locationId);

        return $this->showEdit($location);
    }

    public function save(Request $request): Response
    {
        $locationId = (int) $request->getAttribute('location_id');

        /** @var Location $location */
        $location = $this->location->findOrNew($locationId);
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
                'name'        => 'required|max:35',
                'description' => 'required|optional',
                'dect'        => 'required|optional',
                'map_url'     => 'optional|url',
            ] + $validation
        );

        if (Location::whereName($data['name'])->where('id', '!=', $location->id)->exists()) {
            throw new ValidationException((new Validator())->addErrors(['name' => ['validation.name.exists']]));
        }

        $location->name = $data['name'];
        $location->description = $data['description'];
        $location->dect = $data['dect'];
        $location->map_url = $data['map_url'];

        $location->save();
        $location->neededAngelTypes()->getQuery()->delete();
        $angelsInfo = '';

        // Associate angel types with the room
        foreach ($angelTypes as $angelType) {
            $count = $data['angel_type_' . $angelType->id];
            if (!$count) {
                continue;
            }

            $neededAngelType = new NeededAngelType();

            $neededAngelType->location()->associate($location);
            $neededAngelType->angelType()->associate($angelType);

            $neededAngelType->count = $data['angel_type_' . $angelType->id];

            $neededAngelType->save();

            $angelsInfo .= sprintf(', %s: %s', $angelType->name, $count);
        }

        $this->log->info(
            'Updated location "{name}" ({id}): {description} {dect} {map_url} {angels}',
            [
                'id'          => $location->id,
                'name'        => $location->name,
                'description' => $location->description,
                'dect'        => $location->dect,
                'map_url'     => $location->map_url,
                'angels'      => $angelsInfo,
            ]
        );

        $this->addNotification('location.edit.success');

        return $this->redirect->to('/admin/locations');
    }

    public function delete(Request $request): Response
    {
        $data = $this->validate($request, [
            'id'     => 'required|int',
            'delete' => 'checked',
        ]);

        $location = $this->location->findOrFail($data['id']);

        $shifts = $location->shifts;
        foreach ($shifts as $shift) {
            event('shift.deleting', ['shift' => $shift]);
        }
        $location->delete();

        $this->log->info('Deleted location {location}', ['location' => $location->name]);
        $this->addNotification('location.delete.success');

        return $this->redirect->to('/admin/locations');
    }

    protected function showEdit(?Location $location): Response
    {
        $angeltypes = AngelType::all()
            ->sortBy('name');

        return $this->response->withView(
            'admin/locations/edit',
            [
                'location' => $location,
                'angel_types' => $angeltypes,
                'needed_angel_types' => $location?->neededAngelTypes,
            ]
        );
    }
}
