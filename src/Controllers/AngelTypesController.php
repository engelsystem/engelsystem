<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;
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
    ) {
    }

    public function hasPermission(ServerRequestInterface $request, string $method): ?bool
    {
        return match ($method) {
            'qrCode' =>
                $this->auth->user()?->isAngelTypeSupporter($this->getAngelType($request))
                || $this->auth->can('admin_user_angeltypes'),
            'join' => (bool) $this->auth->user(),
            default => parent::hasPermission($request, $method),
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

    public function qrCode(Request $request): Response
    {
        $this->qrJoinEnabled();
        $angelType = $this->getAngelType($request);
        $jwtExpirationMin = $this->config->get('jwt_expiration_time');
        $qrData = null;
        $data = [];

        if ($request->isMethod('post')) {
            $data = $this->validate($request, [
                'minutes' => 'required|int|min:1|max:' . $jwtExpirationMin,
            ]);
            $minutes = (int) $data['minutes'];
            $time = Carbon::now();

            $key = $this->config->get('app_key');
            $alg = $this->config->get('jwt_algorithm');
            $jti = Str::random();

            $iat = $time->timestamp;
            $exp = $time->addMinutes($minutes)->timestamp;
            $data['expires'] = $time;

            $payload = [
                'sub' => 'join_angel_type',
                'iat' => $iat,
                'exp' => $exp,
                'jti' => $jti,
                'id' => $angelType->id,
                'by' => $this->auth->user()->id,
            ];
            $jwt = JWT::encode($payload, $key, $alg);
            $qrData = url('/angeltypes/' . $angelType->id . '/join', ['token' => $jwt]);
        }

        return $this->response->withInput($data)->withView(
            'pages/angeltypes/qr',
            ['angel_type' => $angelType, 'qr_data' => $qrData, 'qr_max_expiration_minutes' => $jwtExpirationMin],
        );
    }

    public function join(Request $request): Response
    {
        $this->qrJoinEnabled();
        $angelType = $this->getAngelType($request);

        $jwt = $request->get('token', '');

        $key = $this->config->get('app_key');
        $alg = $this->config->get('jwt_algorithm');

        try {
            $decoded = JWT::decode($jwt, new Key($key, $alg));
        } catch (BeforeValidException | ExpiredException) {
            throw new HttpNotFound();
        } catch (Exception $e) {
            $this->log->error('JWT Error', ['exception' => $e]);
            throw new HttpNotFound();
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
