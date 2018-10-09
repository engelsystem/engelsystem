<?php

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\User\User;

/**
 * @param array|User $recipientUser
 * @param string     $title
 * @param string     $message
 * @param bool       $notIfItsMe
 * @return bool
 */
function engelsystem_email_to_user($recipientUser, $title, $message, $notIfItsMe = false)
{
    $user = Auth()->user();

    if ($recipientUser instanceof User) {
        $id = $user->id;
        $lang = $user->settings->language;
        $email = $user->contact->email ? $user->contact->email : $user->email;
        $username = $user->name;
    } else {
        $id = $recipientUser['UID'];
        $lang = $recipientUser['Sprache'];
        $email = $recipientUser['email'];
        $username = $recipientUser['Nick'];
    }

    if ($notIfItsMe && $user->id == $id) {
        return true;
    }

    /** @var \Engelsystem\Helpers\Translator $translator */
    $translator = app()->get('translator');
    $locale = $translator->getLocale();
    /** @var EngelsystemMailer $mailer */
    $mailer = app('mailer');

    $translator->setLocale($lang);
    $status = $mailer->sendView(
        $email,
        $title,
        'emails/mail',
        ['username' => $username, 'message' => $message]
    );
    $translator->setLocale($locale);

    if (!$status) {
        engelsystem_error('Unable to send email.');
    }

    return (bool)$status;
}
