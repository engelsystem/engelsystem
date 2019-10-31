<?php

namespace Engelsystem\Controllers;

use Carbon\Carbon;
use Engelsystem\Config\Config;
use Engelsystem\Database\Database;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Parsedown;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RegistrationController extends BaseController
{
    /** @var Response */
    protected $response;

    /** @var SessionInterface */
    protected $session;

    /** @var Authenticator */
    protected $auth;

    /** @var EngelsystemMailer */
    protected $mail;

    /** @var LoggerInterface */
    protected $log;

    /** @var Config */
    protected $config;

    /** @var Translator */
    protected $translator;

    /** @var Database */
    protected $db;

    /** @var array */
    protected $permissions = [
        'register'      => 'register',
        'postRegister'  => 'register',
    ];

    /**
     * @param Response $response
     * @param SessionInterface $session
     * @param Authenticator $auth
     * @param EngelsystemMailer $mail
     * @param LoggerInterface $log
     * @param Config $config
     * @param Translator $translator
     * @param Database $db
     */
    public function __construct(
        Response $response,
        SessionInterface $session,
        Authenticator $auth,
        EngelsystemMailer $mail,
        LoggerInterface $log,
        Config $config,
        Translator $translator,
        Database $db
    ) {
        $this->response = $response;
        $this->session = $session;
        $this->auth = $auth;
        $this->mail = $mail;
        $this->log = $log;
        $this->config = $config;
        $this->translator = $translator;
        $this->db = $db;
    }

    /**
     * @return Response
     */
    public function register(): Response
    {
        return $this->showForm();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function postRegister(Request $request): Response
    {
        $isPending = $this->config->get('enable_pending_registrations') && $this->auth->user() != null;

        if (!($isPending || $this->config->get('registration_enabled'))) {
            $this->addMessage('danger', $this->translator->translate('registration.disabled'));

            return $this->response->redirectTo('/');
        }

        $rules = [
            'login'     => 'required|between:1:23|regex:/^([\p{L}\p{N}\-_. ]+)$/ui',
            'email'     => 'required|email',
            'mobile'    => 'optional|phone',
        ];

        if ($this->config->get('enable_tshirt_size')) {
            $rules['tshirt_size'] = 'required|in:' . join(',', array_keys($this->config->get('tshirt_sizes')));
        }
        if ($this->config->get('enable_planned_arrival')) {
            $buildup = $this->config->get('buildup_start');
            $teardown = $this->config->get('teardown_end');
            $boundsRule = '';
            if (!empty($buildup) && !empty($teardown)) {
                $boundsRule = '|between:' . $buildup . ':' . $teardown;
            } elseif (!empty($buildup)) {
                $boundsRule = '|min:' . $buildup;
            } elseif (!empty($teardown)) {
                $boundsRule = '|max:' . $teardown;
            }
            $rules['planned_arrival_date'] = 'required|date:Y-m-d' . $boundsRule;
        }
        if (!$isPending) {
            $rules['password'] = 'required|min:' . $this->config->get('min_password_length');
            $rules['password_confirmation'] = 'required';
        }
        if ($this->config->get('enable_dect')) {
            $rules['dect'] = 'optional|length:0:40';
        }
        if ($this->config->get('enable_user_name')) {
            $rules['firstname'] = 'optional|alpha';
            $rules['lastname'] = 'optional|alpha';
        }

        $data = $this->validate($request, $rules);

        $error = false;

        if (User::whereName($data['login'])->count() > 0) {
            $this->session->set('errors', array_merge($this->session->get('errors', []), ['registration.exists.login']));
            $error = true;
        }

        if (User::whereEmail($data['email'])->first()) {
            $this->session->set('errors', array_merge($this->session->get('errors', []), ['registration.exists.email']));
            $error = true;
        }

        if (!$isPending && $data['password'] !== $data['password_confirmation']) {
            $this->session->set('errors', array_merge($this->session->get('errors', []), ['validation.password.confirmed']));
            $error = true;
        }

        if ($error) {
            return $this->showForm();
        }

        $user = new User([
            'name'          => $data['login'],
            'password'      => '',
            'email'         => $data['email'],
            'api_key'       => '',
            'last_login_at' => null,
        ]);
        $user->save();

        $contact = new Contact([
            'dect'      => $data['dect'] ?? '',
            'mobile'    => $data['mobile'] ?? '',
        ]);
        $contact->user()
            ->associate($user)
            ->save();

        $personalData = new PersonalData([
            'first_name'            => $data['firstname'] ?? '',
            'last_name'             => $data['lastname'] ?? '',
            'shirt_size'            => $data['tshirt_size'] ?? '',
            'planned_arrival_date'  => isset($data['planned_arrival_date'])
                ? Carbon::createFromFormat('Y-m-d', $data['planned_arrival_date'])
                : null,
        ]);
        $personalData->user()
            ->associate($user)
            ->save();

        $settings = new Settings([
            'language'          => $this->session->get('locale'),
            'theme'             => $this->config->get('theme'),
            'email_human'       => $request->has('email_human'),
            'email_shiftinfo'   => $request->has('email_shiftinfo'),
        ]);
        $settings->user()
            ->associate($user)
            ->save();

        if ($this->config->get('autoarrive')) {
            $state = new State([
                'arrived'       => true,
                'arrival_date'  => new Carbon(),
            ]);
            $state->user()
                ->associate($user)
                ->save();
        }

        $this->db->insert('INSERT INTO `UserGroups` (`uid`, `group_id`) VALUES (?, -20)', [$user->id]);
        if (!$isPending) {
            $this->auth->setPassword($user, $data['password']);
        } else {
            $reset = new PasswordReset([]);
            $reset->user_id = $user->id;
            $reset->token = md5(random_bytes(64));
            $reset->save();

            $this->log->info(
                sprintf('Pending registration for %s (%u)', $user->name, $user->id),
                ['user' => $user->toJson()]
            );

            $this->mail->sendViewTranslated(
                $user,
                'auth.password-reset',
                'emails/password-reset',
                ['username' => $user->name, 'reset' => $reset]
            );
        }

        $angelTypes = $this->db->select('SELECT * FROM `AngelTypes` ORDER BY `name`');
        $userAngelTypesInfo = [];
        foreach ($angelTypes as $angelType) {
            $angelTypeId = $angelType['id'];
            if ($request->has('angel_types_' . $angelTypeId)) {
                $this->db->insert(
                    'INSERT INTO `UserAngelTypes` (`user_id`, `angeltype_id`, `supporter`) VALUES (?, ?, FALSE)',
                    [$user->id, $angelTypeId]
                );
                $userAngelTypesInfo[] = $angelType['name'] . ($angelType['restricted'] ? ' (restricted)' : '');
            }
        }

        $this->log->info(
            sprintf(
                'User %s (%u) signed up as: %s',
                $user->name,
                $user->id,
                join(', ', $userAngelTypesInfo)
            )
        );
        $this->addMessage('success', $this->translator->translate('registration.success'));

        if ($message = $this->config->get('welcome_msg')) {
            $this->addMessage('info', (new Parsedown())->text($message));
        }

        return $this->response->redirectTo($this->auth->user() ? '/register' : '/');
    }

    /**
     * @param string $class
     * @param string $message
     */
    protected function addMessage(string $class, string $message): void
    {
        $msg = $this->session->get('msg', '');
        $msg .= '<div class="alert alert-' . $class . '">' . $message . '</div>';

        $this->session->set('msg', $msg);
    }

    /**
     * @return Response
     */
    protected function showForm(): Response
    {
        $errors = Collection::make(Arr::flatten($this->session->get('errors', [])));
        $this->session->remove('errors');

        return $this->response->withView(
            'pages/register',
            ['errors' => $errors]
        );
    }
}
