<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

# Legacy functions
/* Doing their job, but adding a proper namespaces breaks legacy code :( */
use function Shifts_by_user;
use function Shifts_by_angeltype;
use function Shifts_free;
use function AngelType;
use function AngelTypes;
use function User_angeltypes;

class ApiController extends BaseController
{
    /** @var Response */
    protected $response;
    protected $user;
    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response->withHeader('content-type', 'application/json');
        $this->user = auth()->apiUser('key');
        $this->permissions = ['view_api'];
    }

    /**
     * @return Response
     */
    public function index()
    {
        return $this->response
            ->setStatusCode(501)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode(['error' => 'Not implemented']));
    }


    /**
     * @return User
     *
     * Warning: I am still no php coder. This quickly implemented api may be abandoned at any time.
     * Code is mostly harvested from /includes/controller/shifts_controller.php/shifts_json_export_controller()
     * may or may not be unnecessary
     * currently used by both getMy[Shifts|AngelTypes] because $this->user is NULL if no api_key provided
     * (while session based auth is working fine) .
     */
    protected function doApiAuth()
    {
        $user = auth()->apiUser('key');

        if (!auth()->can('view_api')) {
            throw new HttpForbidden('{"error":"Not allowed"}', ['content-type' => 'application/json']);
        }

        return $user;
    }

    /**
     * @Route("/api/v2019-alpha/shifts/my")
     */
    public function getMyShifts(Request $request)
    {
        $user = $this->doApiAuth();
        $shifts = Shifts_by_user($user->id);

        return $this->response->withContent(json_encode($shifts));
    }

    /**
     * @Route("/api/v2019-alpha/shifts/by/angeltype/{angeltypeid:\d+}")
     */
    public function getShiftsByAngelType(Request $request)
    {
        // Shifts_by_angeltype uses only the id, required a AngelType though.
        $angelTypeId = (int)$request->getAttribute('angeltypeid');
        $angelType = AngelType($angelTypeId);
        $shifts = Shifts_by_angeltype($angelType);

        return $this->response->withContent(json_encode($shifts));
    }

    /**
     * @Route("/api/v2019-alpha/shifts/free")
     */
    public function getShiftsFree(Request $request)
    {
        // Shifts_by_angeltype uses only the id, required a AngelType though.
        $start = (int)$request->getAttribute('start');
        $stop = (int)$request->getAttribute('stop');
        $shifts = Shifts_free($start, $stop);

        return $this->response->withContent(json_encode($shifts));
    }

    /**
     * @Route("/api/v2019-alpha/angeltypes/my")
     */
    public function getMyAngelTypes(Request $request)
    {
        $user = $this->doApiAuth();
        $angelTypes = User_angeltypes($user->id);

        return $this->response->withContent(json_encode($angelTypes));
    }

    /**
     * @Route("/api/v2019-alpha/angeltypes")
     */
    public function getAngelTypes(Request $request)
    {
        $angelTypes = AngelTypes();

        return $this->response->withContent(json_encode($angelTypes));
    }
}
