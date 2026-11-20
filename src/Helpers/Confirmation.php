<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use DomainException;
use Engelsystem\Config\Config;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;
use Psr\Log\LoggerInterface;

class Confirmation
{
    public function __construct(
        protected Config $config,
        protected EngelsystemMailer $mailer,
        protected LoggerInterface $log,
        protected UrlGenerator $url,
        protected JWT $jwt,
    ) {
    }

    public function confirmationAvailable(): bool
    {
        return
            $this->config->get('email_confirmation')
            && $this->config->get('url')
            && $this->config->get('app_key');
    }

    public function sendEmailConfirmationLink(User $user): bool
    {
        if (!$this->confirmationAvailable()) {
            return false;
        }

        $url = $this->getEmailConfirmationLink($user);

        return $this->mailer->sendViewTranslated(
            $user,
            'email.user.email-confirmation',
            'emails/user-email-confirmation',
            [
                'user' => $user,
                'username' => $user->displayName,
                'url' => $url,
            ],
        );
    }

    public function getEmailConfirmationLink(User $user): string
    {
        $jwt = $this->jwt->encode([
            'sub' => 'email_address_confirmation',
            'id' => $user->id,
            'address' => $user->email,
        ], 15);

        return $this->url->to('/email/confirm/' . $jwt);
    }

    public function confirmEmailAddress(string $jwt): bool
    {
        if (!$this->confirmationAvailable()) {
            return false;
        }

        try {
            $decoded = $this->jwt->decode($jwt);
        } catch (DomainException $e) {
            throw new HttpNotFound($e->getMessage(), [], 0, $e);
        }

        $type = $decoded->sub ?? null;
        $email = $decoded->address ?? null;
        $jti = $decoded->jti ?? null;
        $id = $decoded->id ?? null;
        /** @var User|null $user */
        $user = User::where('id', $id)->where('email', $email)->first();
        if ($type !== 'email_address_confirmation' || !$user) {
            throw new HttpNotFound();
        }

        if (!$user->state->email_confirmed) {
            $user->state->email_confirmed = true;

            $this->log->info(
                'Confirmed email address for {user} via token {token_id}',
                [
                    'user' => sprintf('%s (%u)', $user->displayName, $user->id),
                    'token_id' => $jti,
                ]
            );

            $user->state->save();
        }

        return true;
    }
}
