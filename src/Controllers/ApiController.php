<?php

namespace Engelsystem\Controllers;

use DateTime;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Response;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\HttpException;
use Engelsystem\Models\User\User;
use Symfony\Component\HttpFoundation\Request;

/* Legacy functions
 * Doing their job, but adding a proper namespaces breaks legacy code :(
 */
use function Shift;
use function Shifts_by_user;
use function Shifts_by_angeltype;
use function Shifts_free;
use function AngelType;
use function AngelTypes;
use function User_angeltypes;

/**
 * Class ApiController
 * @package Engelsystem\Controllers
 *
 * Warning: I am still no php coder. This quickly implemented api may be abandoned at any time.
 * Code is mostly harvested from /includes/controller/shifts_controller.php/shifts_json_export_controller()
 */
class ApiController extends BaseController
{
    /** @var Response */
    protected $response;
    protected $user;

    /** @var Authenticator */
    protected $auth;
    /**
     * @param Response              $response
     * @param Authenticator         $auth
     */
    public function __construct(Response $response, Authenticator $auth)
    {
        $this->response = $response->withHeader('content-type', 'application/json');
        $this->auth = $auth;
        $this->user = $this->auth->apiUser('key');
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
     * @return User|null
     *
     * currently used by both getMy[Shifts|AngelTypes] because $this->user is NULL if no api_key provided
     * (while session based auth is still working fine) .
     * may or may not be unnecessary and removed.
     */
    protected function doApiAuth()
    {
        $user = $this->auth->apiUser('key');

        if (!$this->auth > can('view_api')) {
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
     * @Route("/api/v2019-alpha/shift/{shiftid:\d+}")
     */
    public function getShiftById(Request $request)
    {
        // Shifts_by_angeltype uses only the id, required a AngelType though.
        $shiftId = (int)$request->getAttribute('shiftid');

        $shift = Shift($shiftId);

        return $this->response->withContent(json_encode($shift));
    }

    /**
     * @Route("/api/v2019-alpha/shifts/free")
     */
    public function getShiftsFree(Request $request)
    {
        /*
         * @return  Integer
         * Check if the given attribute is already an unix timestamp, otherwise try to convert it to a unix timestamp.
         */
        function getTimestamp(string $attribute, Request $request)
        {
            $rawValue = $request->getAttribute($attribute);
            if (is_numeric($rawValue)) {
                return (int)$rawValue;
            } else {
                /*
                 * Parse the attribute as RFC3339 / Y-m-d\TH:i:sP formatted string instead of a raw unix timestamp
                 */
                $parsedValue = DateTime::createFromFormat("Y-m-d\TH:i:sP", $rawValue);
                if ($parsedValue === false) {
                    throw new HttpException(
                        400,
                        '{"error":"Not a valid timestamp or RFC3339 formatted string"}',
                        ['content-type' => 'application/json']
                    );
                }
                return $parsedValue->getTimestamp();
            }
        }
        $start = getTimestamp('start', $request);
        $stop = getTimestamp('stop', $request);
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
