<?php

namespace Engelsystem\Controllers;

use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PasswordResetController extends BaseController
{
    use HasUserNotifications;

    /** @var LoggerInterface */
    protected $log;

    /** @var EngelsystemMailer */
    protected $mail;

    /** @var Response */
    protected $response;

    /** @var SessionInterface */
    protected $session;

    /** @var array */
    protected $permissions = [
        'reset'             => 'login',
        'postReset'         => 'login',
        'resetPassword'     => 'login',
        'postResetPassword' => 'login',
    ];

    /**
     * @param Response          $response
     * @param SessionInterface  $session
     * @param EngelsystemMailer $mail
     * @param LoggerInterface   $log
     */
    public function __construct(
        Response $response,
        SessionInterface $session,
        EngelsystemMailer $mail,
        LoggerInterface $log
    ) {
        $this->log = $log;
        $this->mail = $mail;
        $this->response = $response;
        $this->session = $session;
    }

    /**
     * @return Response
     */
    public function reset(): Response
    {
        return $this->showView('pages/password/reset');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function postReset(Request $request): Response
    {
        $data = $this->validate($request, [
            'email' => 'required|email',
        ]);

        /** @var User $user */
        $user = User::whereEmail($data['email'])->first();
        if ($user) {
            $reset = (new PasswordReset())->findOrNew($user->id);
            $reset->user_id = $user->id;
            $reset->token = md5(random_bytes(64));
            $reset->save();

            $this->log->info(
                sprintf('Password recovery for %s (%u)', $user->name, $user->id),
                ['user' => $user->toJson()]
            );

            $this->mail->sendViewTranslated(
                $user,
                'Password recovery',
                'emails/password-reset',
                ['username' => $user->name, 'reset' => $reset]
            );
        }

        return $this->showView('pages/password/reset-success', ['type' => 'email']);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request): Response
    {
        $this->requireToken($request);

        return $this->showView(
            'pages/password/reset-form',
            ['min_length' => config('min_password_length')]
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function postResetPassword(Request $request): Response
    {
        $reset = $this->requireToken($request);

        $data = $this->validate($request, [
            'password'              => 'required|min:' . config('min_password_length'),
            'password_confirmation' => 'required',
        ]);

        if ($data['password'] !== $data['password_confirmation']) {
            $this->addNotification('validation.password.confirmed', 'errors');

            return $this->showView('pages/password/reset-form');
        }

        auth()->setPassword($reset->user, $data['password']);
        $reset->delete();

        return $this->showView('pages/password/reset-success', ['type' => 'reset']);
    }

    /**
     * @param string $view
     * @param array  $data
     * @return Response
     */
    protected function showView($view = 'pages/password/reset', $data = []): Response
    {
        return $this->response->withView(
            $view,
            array_merge_recursive($this->getNotifications(), $data)
        );
    }

    /**
     * @param Request $request
     * @return PasswordReset
     */
    protected function requireToken(Request $request): PasswordReset
    {
        $token = $request->getAttribute('token');
        /** @var PasswordReset|null $reset */
        $reset = PasswordReset::whereToken($token)->first();

        if (!$reset) {
            throw new HttpNotFound();
        }

        return $reset;
    }
}
