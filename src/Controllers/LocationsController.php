<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Http\Redirector;
use Engelsystem\Http\Response;
use Engelsystem\Models\Location;
use Psr\Log\LoggerInterface;

class LocationsController extends BaseController
{
    use HasUserNotifications;

    /** @var array<string> */
    protected array $permissions = [
        'locations.view',
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
            'pages/locations/index',
            [
                'locations' => $locations,
                'is_index' => true,
            ]
        );
    }
}
