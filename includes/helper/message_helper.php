<?php

use Engelsystem\Controllers\NotificationType;

/**
 * Returns messages from session and removes them from the stack by rendering the messages twig template
 * @return string
 * @see \Engelsystem\Controllers\HasUserNotifications
 */
function msg()
{
    return view('layouts/parts/messages.twig');
}

/**
 * Renders an information message
 *
 * @param string $msg
 * @param bool   $immediately
 * @return string
 */
function info($msg, $immediately = false)
{
    return alert(NotificationType::INFORMATION, $msg, $immediately);
}

/**
 * Renders a warning message
 *
 * @param string $msg
 * @param bool   $immediately
 * @return string
 */
function warning($msg, $immediately = false)
{
    return alert(NotificationType::WARNING, $msg, $immediately);
}

/**
 * Renders an error message
 *
 * @param string $msg
 * @param bool   $immediately
 * @return string
 */
function error($msg, $immediately = false)
{
    return alert(NotificationType::ERROR, $msg, $immediately);
}

/**
 * Renders a success message
 *
 * @param string $msg
 * @param bool   $immediately
 * @return string
 */
function success($msg, $immediately = false)
{
    return alert(NotificationType::MESSAGE, $msg, $immediately);
}

/**
 * Renders an alert message with the given alert-* class or sets it in session
 *
 * @see \Engelsystem\Controllers\HasUserNotifications
 *
 * @param NotificationType $type
 * @param string           $msg
 * @param bool             $immediately
 * @return string
 */
function alert(NotificationType $type, $msg, $immediately = false)
{
    if (empty($msg)) {
        return '';
    }

    if ($immediately) {
        $type = str_replace(
            [
                NotificationType::ERROR->value,
                NotificationType::WARNING->value,
                NotificationType::INFORMATION->value,
                NotificationType::MESSAGE->value,
            ],
            ['danger', 'warning', 'info', 'success'],
            $type->value
        );
        return '<div class="alert alert-' . $type . '" role="alert">' . $msg . '</div>';
    }

    $type = 'messages.' . $type->value;
    $session = session();
    $messages = $session->get($type, []);
    $messages[] = $msg;
    $session->set($type, $messages);

    return '';
}
