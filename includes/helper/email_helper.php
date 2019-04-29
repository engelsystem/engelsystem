<?php

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

    /** @var \Engelsystem\Helpers\Translator $translator */
    $translator = app()->get('translator');
    $locale = $translator->getLocale();

    try {
        /** @var EngelsystemMailer $mailer */
        $mailer = app('mailer');

        $translator->setLocale($recipientUser->settings->language);
        $status = $mailer->sendView(
            $recipientUser->contact->email ? $recipientUser->contact->email : $recipientUser->email,
            $title,
            'emails/mail',
            ['username' => $recipientUser->name, 'message' => $message]
        );
    } catch (Exception $e) {
        $status = 0;
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

    return (bool)$status;
}
