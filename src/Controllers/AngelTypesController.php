<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use DomainException;
use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\JWT;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class AngelTypesController extends BaseController
{
    use HasUserNotifications;

    public function __construct(
        protected Response $response,
        protected Config $config,
        protected Authenticator $auth,
        protected LoggerInterface $log,
        protected AngelType $angelType,
        protected JWT $jwt,
    ) {
    }

    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        return match ($method) {
            'qrCode' =>
                $this->auth->user()?->isAngelTypeSupporter($this->getAngelType($request))
                || $this->auth->can('user.angeltypes.edit'),
            'about' => parent::hasPermission($request, $method),
            default => (bool) $this->auth->user(),
        };
    }

    public function about(): Response
    {
        $angeltypes = AngelType::all();

        return $this->response->withView(
            'pages/angeltypes/about',
            ['angeltypes' => $angeltypes]
        );
    }

    public function index(): Response
    {
        $angelTypes = $this->angelType
            ->with(['userAngelTypes' => function ($query): void {
                $query->where('user_id', '=', auth()->user()->id);
            }])
            ->orderBy('name')
            ->get();

        return $this->response->withView(
            'pages/angeltypes/index',
            [
                'angelTypes' => $angelTypes,
                'is_index' => true,
            ]
        );
    }

    public function qrCode(Request $request): Response
    {
        $this->qrJoinEnabled();
        $angelType = $this->getAngelType($request);
        $jwtExpirationMax = $this->config->get('jwt_expiration_time');
        $minutes = 5;
        $qrData = null;
        $data = [];

        if ($request->isMethod('post')) {
            $data = $this->validate($request, [
                'minutes' => 'required|int|min:1|max:' . $jwtExpirationMax,
            ]);
            $minutes = (int) $data['minutes'];

            $jwt = $this->jwt->encode([
                'sub' => 'join_angel_type',
                'id' => $angelType->id,
                'by' => $this->auth->user()->id,
            ], $minutes);

            $qrData = url('/angeltypes/' . $angelType->id . '/join', ['token' => $jwt]);
        }

        return $this->response->withInput($data)->withView(
            'pages/angeltypes/qr',
            [
                'angel_type' => $angelType,
                'qr_data' => $qrData,
                'qr_max_expiration_minutes' => $jwtExpirationMax,
                'expires_at' => Carbon::now()->addMinutes($minutes),
            ],
        );
    }

    public function join(Request $request): Response
    {
        $this->qrJoinEnabled();
        $angelType = $this->getAngelType($request);

        $jwt = $request->get('token', '');

        try {
            $decoded = $this->jwt->decode($jwt);
        } catch (DomainException $e) {
            throw new HttpNotFound($e->getMessage(), [], 0, $e);
        }

        $type = $decoded->sub ?? null;
        $id = $decoded->id ?? null;
        $jti = $decoded->jti ?? null;
        if ($type !== 'join_angel_type' || $id !== $angelType->id) {
            throw new HttpNotFound();
        }

        /** @var User $confirmingUser */
        $confirmingUser = User::findOrFail($decoded->by ?? null);
        /** @var UserAngelType $userAngelType */
        $userAngelType = UserAngelType::firstOrNew([
            'user_id' => $this->auth->user()->id,
            'angel_type_id' => $angelType->id,
        ]);

        if (!$userAngelType->confirmUser) {
            $userAngelType->confirmUser()->associate($confirmingUser);

            $this->log->info(
                'Joined angel type {type} ({type_id}) via QR token {token_id} '
                . 'created by {confirming_user} ({confirming_id})',
                [
                    'type' => $angelType->name,
                    'type_id' => $angelType->id,
                    'token_id' => $jti,
                    'confirming_user' => $confirmingUser->name,
                    'confirming_id' => $confirmingUser->id,
                ]
            );

            $userAngelType->save();
        }

        $this->addNotification('angeltype.add.success');

        return redirect(url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angelType->id]));
    }

    protected function qrJoinEnabled(): void
    {
        if ($this->config->get('app_key') && $this->config->get('join_qr_code', true)) {
            return;
        }

        throw new HttpNotFound();
    }

    protected function getAngelType(ServerRequestInterface $request): AngelType
    {
        $angelTypeId = (int) $request->getAttribute('angel_type_id');
        /** @var AngelType $angelType */
        $angelType = AngelType::findOrFail($angelTypeId);
        return $angelType;
    }
}
