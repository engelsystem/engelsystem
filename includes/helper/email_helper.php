<?php

use Engelsystem\Mail\EngelsystemMailer;

/**
 * @param array  $recipient_user
 * @param string $title
 * @param string $message
 * @param bool   $not_if_its_me
 * @return bool
 */
function engelsystem_email_to_user($recipient_user, $title, $message, $not_if_its_me = false)
{
    $user = Auth()->user();

    if ($not_if_its_me && $user->id == $recipient_user['UID']) {
        return true;
    }

    /** @var \Engelsystem\Helpers\Translator $translator */
    $translator = app()->get('translator');
    $locale = $translator->getLocale();
    /** @var EngelsystemMailer $mailer */
    $mailer = app('mailer');

    $translator->setLocale($recipient_user['Sprache']);
    $status = $mailer->sendView(
        $recipient_user['email'],
        $title,
        'emails/mail',
        ['username' => $recipient_user['Nick'], 'message' => $message]
    );
    $translator->setLocale($locale);

    if (!$status) {
        engelsystem_error('Unable to send email.');
    }

    return (bool)$status;
}
