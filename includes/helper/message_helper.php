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
 * @param bool   $immediatelyRaw
 * @return string
 */
function info($msg, $immediately = false, $immediatelyRaw = false)
{
    return alert(NotificationType::INFORMATION, $msg, $immediately, $immediatelyRaw);
}

/**
 * Renders a warning message
 *
 * @param string $msg
 * @param bool   $immediately
 * @param bool   $immediatelyRaw
 * @return string
 */
function warning($msg, $immediately = false, $immediatelyRaw = false)
{
    return alert(NotificationType::WARNING, $msg, $immediately, $immediatelyRaw);
}

/**
 * Renders an error message
 *
 * @param string $msg
 * @param bool   $immediately
 * @param bool   $immediatelyRaw
 * @return string
 */
function error($msg, $immediately = false, $immediatelyRaw = false)
{
    return alert(NotificationType::ERROR, $msg, $immediately, $immediatelyRaw);
}

/**
 * Renders a success message
 *
 * @param string $msg
 * @param bool   $immediately
 * @param bool   $immediatelyRaw
 * @return string
 */
function success($msg, $immediately = false, $immediatelyRaw = false)
{
    return alert(NotificationType::MESSAGE, $msg, $immediately, $immediatelyRaw);
}

/**
 * Renders an alert message with the given alert-* class or sets it in session
 *
 * @param NotificationType $type
 * @param string           $msg
 * @param bool             $immediately
 * @param bool             $immediatelyRaw
 * @return string
 *
 * @see \Engelsystem\Controllers\HasUserNotifications
 *
 */
function alert(NotificationType $type, $msg, $immediately = false, $immediatelyRaw = false)
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
        $msg = $immediatelyRaw ? $msg : htmlspecialchars($msg);
        return '<div class="alert alert-' . $type . '" role="alert">' . $msg . '</div>';
    }

    $type = 'messages.' . $type->value;
    $session = session();
    $messages = $session->get($type, []);
    $messages[] = $msg;
    $session->set($type, $messages);

    return '';
}
