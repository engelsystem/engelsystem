<?php

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;

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
    /** @var EngelsystemMailer $mailer */
    $mailer = app('mailer');

    $translator->setLocale($recipientUser->settings->language);
    $status = $mailer->sendView(
        $recipientUser->contact->email ? $recipientUser->contact->email : $recipientUser->email,
        $title,
        'emails/mail',
        ['username' => $recipientUser->name, 'message' => $message]
    );
    $translator->setLocale($locale);

    if (!$status) {
        engelsystem_error('Unable to send email.');
    }

    return (bool)$status;
}
