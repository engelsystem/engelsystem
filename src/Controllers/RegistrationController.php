<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Config\GoodieType;
use Engelsystem\Events\Listener\OAuth2;
use Engelsystem\Factories\User;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RegistrationController extends BaseController
{
    use HasUserNotifications;

    public function __construct(
        private Config $config,
        private Response $response,
        private Redirector $redirect,
        private SessionInterface $session,
        private Authenticator $auth,
        private OAuth2 $oAuth,
        private User $userFactory
    ) {
    }

    public function view(): Response
    {
        if ($this->determineRegistrationDisabled()) {
            return $this->notifySignUpDisabledAndRedirectToHome();
        }

        return $this->renderSignUpPage();
    }

    public function save(Request $request): Response
    {
        if ($this->determineRegistrationDisabled()) {
            return $this->notifySignUpDisabledAndRedirectToHome();
        }

        $rawData = $request->getParsedBody();
        $user = $this->userFactory->createFromData($rawData);

        if (!$this->auth->user()) {
            $this->addNotification('registration.successful');
        } else {
            $this->addNotification('registration.successful.supporter');
        }

        if ($this->config->get('welcome_msg')) {
            // Set a session marker to display the welcome message on the next page
            $this->session->set('show_welcome', true);
        }

        if ($user->oauth?->count() > 0) {
            // User has OAuth configured. Log in directly.
            $provider = $user->oauth->first();
            return $this->redirect->to('/oauth/' . $provider->provider);
        }

        if ($this->auth->user()) {
            // User is already logged in - that means a supporter has registered an angel. Return to register page.
            return $this->redirect->to('/register');
        }

        return $this->redirect->to('/');
    }

    private function notifySignUpDisabledAndRedirectToHome(): Response
    {
        $this->addNotification('registration.disabled', NotificationType::INFORMATION);
        return $this->redirect->to('/');
    }

    private function renderSignUpPage(): Response
    {
        $goodieType = GoodieType::from($this->config->get('goodie_type'));
        $preselectedAngelTypes = $this->determinePreselectedAngelTypes();
        $requiredFields = $this->config->get('required_user_fields');

        // form-data-register-submit is a marker, that the form was submitted.
        // It will be used for instance to use the default angel types or the user selected ones.
        // Clear it before render to reset the marker state.
        $this->session->remove('form-data-register-submit');

        return $this->response->withView(
            'pages/registration',
            [
                'minPasswordLength' => $this->config->get('password_min_length'),
                'tShirtSizes' => $this->config->get('tshirt_sizes'),
                'tShirtLink' => $this->config->get('tshirt_link'),
                'angelTypes' => AngelType::whereHideRegister(false)->get(),
                'preselectedAngelTypes' => $preselectedAngelTypes,
                'buildUpStartDate' => $this->userFactory->determineBuildUpStartDate()->format('Y-m-d'),
                'tearDownEndDate' => $this->config->get('teardown_end')?->format('Y-m-d'),
                'isPasswordEnabled' => $this->userFactory->determineIsPasswordEnabled(),
                'isDECTEnabled' => $this->config->get('enable_dect'),
                'isShowMobileEnabled' => $this->config->get('enable_mobile_show'),
                'isGoodieEnabled' => $goodieType !== GoodieType::None && config('enable_email_goodie'),
                'isVoucherEnabled' => $this->config->get('enable_voucher'),
                'isForceFoodEnabled' => $this->config->get('enable_force_food'),
                'isGoodieTShirt' => $goodieType === GoodieType::Tshirt,
                'isPronounEnabled' => $this->config->get('enable_pronoun'),
                'isFullNameEnabled' => $this->config->get('enable_full_name'),
                'isPlannedArrivalDateEnabled' => $this->config->get('enable_planned_arrival'),
                'isPronounRequired' => $requiredFields['pronoun'],
                'isFirstnameRequired' => $requiredFields['firstname'],
                'isLastnameRequired' => $requiredFields['lastname'],
                'isTShirtSizeRequired' => $requiredFields['tshirt_size'],
                'isMobileRequired' => $requiredFields['mobile'],
                'isDectRequired' => $requiredFields['dect'],
            ],
        );
    }

    /**
     * @return Array<string, 1> Checkbox field name/id →  1
     */
    private function determinePreselectedAngelTypes(): array
    {
        if ($this->session->has('form-data-register-submit')) {
            // form-data-register-submit means a user just submitted the page.
            // Preselect the angel types from the persisted session form data.
            return $this->loadAngelTypesFromSessionFormData();
        }

        $preselectedAngelTypes = [];

        if ($this->session->has('oauth2_connect_provider')) {
            $preselectedAngelTypes = $this->loadAngelTypesFromSessionOAuthGroups();
        }

        foreach (AngelType::whereRestricted(false)->whereHideRegister(false)->get() as $angelType) {
            // preselect every angel type without restriction
            $preselectedAngelTypes['angel_types_' . $angelType->id] = 1;
        }

        return $preselectedAngelTypes;
    }

    /**
     * @return Array<string, 1>
     */
    private function loadAngelTypesFromSessionOAuthGroups(): array
    {
        $oAuthAngelTypes = [];
        $ssoTeams = $this->oAuth->getSsoTeams($this->session->get('oauth2_connect_provider'));
        $oAuth2Groups = $this->session->get('oauth2_groups');

        foreach ($ssoTeams as $name => $team) {
            if (in_array($name, $oAuth2Groups)) {
                // preselect angel type from oauth
                $oAuthAngelTypes['angel_types_' . $team['id']] = 1;
            }
        }

        return $oAuthAngelTypes;
    }

    /**
     * @return Array<string, 1>
     */
    private function loadAngelTypesFromSessionFormData(): array
    {
        $angelTypes = AngelType::whereHideRegister(false)->get();
        $selectedAngelTypes = [];

        foreach ($angelTypes as $angelType) {
            $sessionKey = 'form-data-angel_types_' . $angelType->id;

            if ($this->session->has($sessionKey)) {
                $selectedAngelTypes['angel_types_' . $angelType->id] = 1;
                // remove from session so that it doesn't stay there forever
                $this->session->remove($sessionKey);
            }
        }

        return $selectedAngelTypes;
    }

    private function determineRegistrationDisabled(): bool
    {
        $authUser = $this->auth->user();
        $isOAuth = $this->session->get('oauth2_connect_provider');
        $isPasswordEnabled = $this->userFactory->determineIsPasswordEnabled();

        return !auth()->can('register') // No registration permission
            // Not authenticated and
            // Registration disabled
            || (
                !$authUser
                && !$this->config->get('registration_enabled')
                && !$this->session->get('oauth2_allow_registration')
            )
            // Password disabled and not oauth
            || (!$authUser && !$isPasswordEnabled && !$isOAuth);
    }
}
