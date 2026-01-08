<?php

declare(strict_types=1);

namespace Engelsystem\Factories;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Engelsystem\Config\Config;
use Engelsystem\Config\GoodieType;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Group;
use Engelsystem\Models\OAuth;
use Engelsystem\Models\User\Contact;
use Engelsystem\Models\User\PersonalData;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User as EngelsystemUser;
use Illuminate\Database\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class User
{
    public function __construct(
        private Authenticator $authenticator,
        private Config $config,
        private Connection $dbConnection,
        private LoggerInterface $logger,
        private SessionInterface $session,
        private Validator $validator,
    ) {
    }

    /**
     * Takes some arbitrary data, validates it and tries to create a user from it.
     *
     * @param Array<string, mixed> $rawData Raw data from which a user should be created
     * @return EngelsystemUser The user if successful
     * @throws
     */
    public function createFromData(array $rawData): EngelsystemUser
    {
        $data = $this->validateUser($rawData);
        return  $this->createUser($data, $rawData);
    }

    public function determineIsPasswordEnabled(): bool
    {
        $isPasswordEnabled = $this->config->get('enable_password');
        $oAuthEnablePassword = $this->session->get('oauth2_enable_password');

        if (!is_null($oAuthEnablePassword)) {
            // o-auth overwrites config
            $isPasswordEnabled = $oAuthEnablePassword;
        }

        return $isPasswordEnabled;
    }

    public function determineBuildUpStartDate(): DateTimeInterface
    {
        return $this->config->get('buildup_start') ?? CarbonImmutable::now();
    }

    private function isRequired(string $key): string
    {
        $requiredFields = $this->config->get('required_user_fields');
        return in_array($key, $requiredFields) ? 'required' : 'optional';
    }

    /**
     * @param Array<string, mixed> $rawData
     * @throws ValidationException
     */
    private function validateUser(array $rawData): array
    {
        $validationRules = [
            'username' => 'required|username',
            'email' => 'required|email',
            'email_system'  => 'optional|checked',
            'email_shiftinfo' => 'optional|checked',
            'email_by_human_allowed' => 'optional|checked',
            'email_messages' => 'optional|checked',
            'email_news' => 'optional|checked',
            'email_goodie' => 'optional|checked',
            // Using length here, because min/max would validate dect/mobile as numbers.
            'mobile' => $this->isRequired('mobile') . '|length:0:40',
        ];

        $isPasswordEnabled = $this->determineIsPasswordEnabled();

        if ($isPasswordEnabled) {
            $minPasswordLength = $this->config->get('password_min_length');
            $validationRules['password'] = 'required|length:' . $minPasswordLength;
            $validationRules['password_confirmation'] = 'required';
        }

        $isFullNameEnabled = $this->config->get('enable_full_name');

        if ($isFullNameEnabled) {
            $validationRules['firstname'] = $this->isRequired('firstname') . '|length:0:64';
            $validationRules['lastname'] = $this->isRequired('lastname') . '|length:0:64';
        }

        $isPronounEnabled = $this->config->get('enable_pronoun');

        if ($isPronounEnabled) {
            $validationRules['pronoun'] = $this->isRequired('pronoun') . '|max:15';
        }

        $isShowMobileEnabled = $this->config->get('enable_mobile_show');

        if ($isShowMobileEnabled) {
            $validationRules['mobile_show'] = 'optional|checked';
        }

        $isPlannedArrivalDateEnabled = $this->config->get('enable_planned_arrival');

        if ($isPlannedArrivalDateEnabled) {
            $isoBuildUpStartDate = $this->determineBuildUpStartDate();
            /** @var DateTimeInterface|null $tearDownEndDate */
            $tearDownEndDate = $this->config->get('teardown_end');

            if ($tearDownEndDate) {
                $validationRules['planned_arrival_date'] = sprintf(
                    'required|date|between:%s:%s',
                    $isoBuildUpStartDate->format('Y-m-d'),
                    $tearDownEndDate->format('Y-m-d')
                );
            } else {
                $validationRules['planned_arrival_date'] = sprintf(
                    'required|date|min:%s',
                    $isoBuildUpStartDate->format('Y-m-d'),
                );
            }
        }

        $isDECTEnabled = $this->config->get('enable_dect');

        if ($isDECTEnabled) {
            // Using length here, because min/max would validate dect/mobile as numbers.
            $validationRules['dect'] = $this->isRequired('dect') . '|length:0:40';
        }

        $goodieType = GoodieType::from($this->config->get('goodie_type'));
        $isGoodieTShirt = $goodieType === GoodieType::Tshirt;

        if ($isGoodieTShirt) {
            $validationRules['tshirt_size'] = $this->isRequired('tshirt_size') . '|shirt-size';
        }

        // Minor registration support
        $isMinorRegistrationEnabled = $this->config->get('enable_minor_registration', true);
        if ($isMinorRegistrationEnabled) {
            $validationRules['minor_category_id'] = 'optional|int';
        }

        $data = $this->validate($rawData, $validationRules);

        // additional validations
        $this->validateUniqueUsername($data['username']);
        $this->validateUniqueEmail($data['email']);

        // simplified e-mail preferences
        if ($data['email_system']) {
            $data['email_shiftinfo'] = true;
            $data['email_messages'] = true;
            $data['email_news'] = true;
        }

        if ($isPasswordEnabled) {
            // Finally, validate that password matches password_confirmation.
            // The respect keyValue validation does not seem to work.
            $this->validatePasswordMatchesConfirmation($rawData);
        }

        return $data;
    }

    /**
     * @param Array<string, mixed> $rawData
     */
    private function validatePasswordMatchesConfirmation(array $rawData): void
    {
        if ($rawData['password'] !== $rawData['password_confirmation']) {
            throw new ValidationException(
                (new Validator())->addErrors(['password' => [
                    'settings.password.confirmation-does-not-match',
                ]])
            );
        }
    }

    private function validateUniqueUsername(string $username): void
    {
        if (EngelsystemUser::whereName($username)->exists()) {
            throw new ValidationException(
                (new Validator())->addErrors(['username' => [
                    'settings.profile.nick.already-taken',
                ]])
            );
        }
    }

    private function validateUniqueEmail(string $email): void
    {
        if (EngelsystemUser::whereEmail($email)->exists()) {
            throw new ValidationException(
                (new Validator())->addErrors(['email' => [
                    'settings.profile.email.already-taken',
                ]])
            );
        }
    }

    /**
     * @param Array<string, mixed> $data
     * @param Array<string, mixed> $rawData
     */
    private function createUser(array $data, array $rawData): EngelsystemUser
    {
        // Ensure all user entries got created before saving
        $this->dbConnection->beginTransaction();

        $user = new EngelsystemUser([
            'name'              => $data['username'],
            'password'          => '',
            'email'             => $data['email'],
            'api_key'           => '',
            'last_login_at'     => null,
            'minor_category_id' => $data['minor_category_id'] ?? null,
        ]);
        $user->save();

        $contact = new Contact([
            'dect'   => $data['dect'] ?? null,
            'mobile' => $data['mobile'],
        ]);
        $contact->user()
            ->associate($user)
            ->save();

        $isPlannedArrivalDateEnabled = $this->config->get('enable_planned_arrival');
        $plannedArrivalDate = null;

        if ($isPlannedArrivalDateEnabled) {
            $plannedArrivalDate = Carbon::createFromFormat('Y-m-d', $data['planned_arrival_date']);
        }

        $personalData = new PersonalData([
            'first_name'           => $data['firstname'] ?? null,
            'last_name'            => $data['lastname'] ?? null,
            'pronoun'              => $data['pronoun'] ?? null,
            'shirt_size'           => $data['tshirt_size'] ?? null,
            'planned_arrival_date' => $plannedArrivalDate,
        ]);
        $personalData->user()
            ->associate($user)
            ->save();

        $isShowMobileEnabled = $this->config->get('enable_mobile_show');

        $settings = new Settings([
            'language'        => $this->session->get('locale') ?? 'en_US',
            'theme'           => $this->config->get('theme'),
            'email_human'     => $data['email_by_human_allowed'] ?? false,
            'email_messages'  => $data['email_messages'] ?? false,
            'email_goodie'     => $data['email_goodie'] ?? false,
            'email_shiftinfo' => $data['email_shiftinfo'] ?? false,
            'email_news'      => $data['email_news'] ?? false,
            'mobile_show'     => $isShowMobileEnabled && $data['mobile_show'],
        ]);
        $settings->user()
            ->associate($user)
            ->save();

        $state = new State([]);

        if ($this->config->get('autoarrive')) {
            $state->arrival_date = CarbonImmutable::now();
        }

        $state->user()
            ->associate($user)
            ->save();

        // Handle OAuth registration
        if ($this->session->has('oauth2_connect_provider') && $this->session->has('oauth2_user_id')) {
            $oauth = new OAuth([
                'provider'      => $this->session->get('oauth2_connect_provider'),
                'identifier'    => $this->session->get('oauth2_user_id'),
                'access_token'  => $this->session->get('oauth2_access_token'),
                'refresh_token' => $this->session->get('oauth2_refresh_token'),
                'expires_at'    => $this->session->get('oauth2_expires_at'),
            ]);
            $oauth->user()
                ->associate($user)
                ->save();

            $this->session->remove('oauth2_connect_provider');
            $this->session->remove('oauth2_user_id');
            $this->session->remove('oauth2_access_token');
            $this->session->remove('oauth2_refresh_token');
            $this->session->remove('oauth2_expires_at');

            $this->logger->info(
                '{user} connected OAuth user {oauth_user} using {provider}',
                [
                    'provider' => $oauth->provider,
                    'user' => sprintf('%s (%u)', $user->displayName, $user->id),
                    'oauth_user' => $oauth->identifier,
                ]
            );
        }

        $defaultGroup = Group::find($this->authenticator->getDefaultRole());
        $user->groups()->attach($defaultGroup);

        auth()->resetApiKey($user);
        if ($this->determineIsPasswordEnabled() && array_key_exists('password', $data)) {
            auth()->setPassword($user, $data['password']);
        }

        $assignedAngelTypeNames = $this->assignAngelTypes($user, $rawData);

        $this->logger->info(
            'User {user} registered and signed up as: {angeltypes}',
            [
                'user' => sprintf('%s (%u)', $user->displayName, $user->id),
                'angeltypes' => join(', ', $assignedAngelTypeNames),
            ]
        );

        $this->dbConnection->commit();

        return $user;
    }

    private function assignAngelTypes(EngelsystemUser $user, array $rawData): array
    {
        $possibleAngelTypes = AngelType::whereHideRegister(false)->get();
        $assignedAngelTypeNames = [];

        foreach ($possibleAngelTypes as $possibleAngelType) {
            $angelTypeCheckboxId = 'angel_types_' . $possibleAngelType->id;

            if (array_key_exists($angelTypeCheckboxId, $rawData)) {
                $user->userAngelTypes()->attach($possibleAngelType);
                $assignedAngelTypeNames[] = $possibleAngelType->name;
            }
        }

        return $assignedAngelTypeNames;
    }

    private function validate(array $rawData, array $rules): array
    {
        $isValid = $this->validator->validate($rawData, $rules);

        if (!$isValid) {
            throw new ValidationException($this->validator);
        }

        return $this->validator->getData();
    }
}
