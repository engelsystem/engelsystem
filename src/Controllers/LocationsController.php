<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;
use Engelsystem\Models\Location;

class LocationsController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'locations.view',
    ];

    public function __construct(
        protected Location $location,
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
            'pages/locations/index',
            [
                'locations' => $locations,
                'is_index' => true,
            ]
        );
    }
}
