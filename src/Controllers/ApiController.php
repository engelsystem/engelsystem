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

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
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
     * Warning: I am no php coder. This code isnt intended for eternity but making our lives @36c3 a bit easier.
     * Code is mostly harvested from /includes/controller/shifts_controller.php/shifts_json_export_controller()
     */
    protected function doApiAuth($key)
    {
        $user = auth()->apiUser('key');
        if (
            !preg_match('/^[\da-f]{32}$/', $key)
            || !$user
        ) {
            throw new HttpForbidden('{"error":"Missing or invalid key"}', ['content-type' => 'application/json']);
        }

        if (!auth()->can('view_api')) {
            throw new HttpForbidden('{"error":"Not allowed"}', ['content-type' => 'application/json']);
        }

        return $user;
    }

    /**
     * @Route(“/api/shifts/my”)
     */
    public function getMyShifts(Request $request)
    {
        $key = $request->input('key');
        $user = $this->doApiAuth($key);

        $shifts = Shifts_by_user($user->id);

        return $this->response
            ->setStatusCode(200)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode($shifts));
    }

    /**
     * @Route(“/api/shifts/my”)
     * doesnt work? dunno why result is empty.
     */
    public function getShiftsByAngelType(Request $request)
    {
        $key = $request->input('key');
        $user = $this->doApiAuth($key);

        // Shifts_by_angeltype uses only the id, required a AngelType though.
        $angelTypeId = (int)$request->getAttribute('angeltypeid');
        $angelType = AngelType($angelTypeId);
        $shifts = Shifts_by_angeltype($angelType);

        return $this->response
            ->setStatusCode(200)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode($shifts));
    }

    /**
     * @Route(“/api/shifts/free”)
     */
    public function getShiftsFree(Request $request)
    {
        $key = $request->input('key');
        $user = $this->doApiAuth($key);
        // Shifts_by_angeltype uses only the id, required a AngelType though.
        $start = (int)$request->getAttribute('start');
        $stop = (int)$request->getAttribute('stop');
        $shifts = Shifts_free($start, $stop);

        return $this->response
            ->setStatusCode(200)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode($shifts));
    }

    /**
     * @Route(“/api/angeltypes/my”)
     */
    public function getMyAngelTypes(Request $request)
    {
        $key = $request->input('key');
        $user = $this->doApiAuth($key);

        $angelTypes = User_angeltypes($user->id);

        return $this->response
            ->setStatusCode(200)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode($angelTypes));
    }

    /**
     * @Route(“/api/angeltypes”)
     */
    public function getAngelTypes(Request $request)
    {
        $key = $request->input('key');
        $user = $this->doApiAuth($key);

        $angelTypes = AngelTypes();

        return $this->response
            ->setStatusCode(200)
            ->withHeader('content-type', 'application/json')
            ->withContent(json_encode($angelTypes));
    }
}
