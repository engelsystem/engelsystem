<?php

use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;
use Psr\Log\LogLevel;

/**
 * @param User   $recipientUser
 * @param string $title
 * @param string $message
 * @param bool   $notIfItsMe
 * @return bool
 */
function engelsystem_email_to_user($recipientUser, $title, $message, $notIfItsMe = false)
{
    if ($notIfItsMe && auth()->user()->id == $recipientUser->id) {
        return true;
    }

    /** @var Translator $translator */
    $translator = app()->get('translator');
    $locale = $translator->getLocale();

    $status = true;
    try {
        /** @var EngelsystemMailer $mailer */
        $mailer = app('mailer');

        $translator->setLocale($recipientUser->settings->language);
        $mailer->sendView(
            $recipientUser->contact->email ?: $recipientUser->email,
            $title,
            'emails/mail',
            ['username' => $recipientUser->name, 'message' => $message]
        );
    } catch (Exception $e) {
        $status = false;
        engelsystem_log(sprintf(
            'An exception occurred while sending a mail to %s in %s:%u: %s',
            $recipientUser->name,
            $e->getFile(),
            $e->getLine(),
            $e->getMessage()
        ), LogLevel::CRITICAL);
    }

    $translator->setLocale($locale);

    if (!$status) {
        error(sprintf(__('User %s could not be notified by email due to an error.'), User_Nick_render($recipientUser)));
        engelsystem_log(sprintf('User %s could not be notified by email due to an error.', $recipientUser->name));
    }

    return $status;
}
