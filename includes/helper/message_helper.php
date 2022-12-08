<?php

/**
 * Returns messages from session and removes them from the stack
 * @param bool $includeMessagesFromNewProcedure
 *      If set, the messages from the new procedure are also included.
 *      The output will be similar to how it would be with messages.twig.
 * @see \Engelsystem\Controllers\HasUserNotifications
 * @return string
 */
function msg(bool $includeMessagesFromNewProcedure = false)
{
    $session = session();

    $message = $session->get('msg', '');
    $session->set('msg', '');

    if ($includeMessagesFromNewProcedure) {
        foreach (session()->get('errors', []) as $msg) {
            $message .= error(__($msg), true);
        }
        foreach (session()->get('warnings', []) as $msg) {
            $message .= warning(__($msg), true);
        }
        foreach (session()->get('information', []) as $msg) {
            $message .= info(__($msg), true);
        }
        foreach (session()->get('messages', []) as $msg) {
            $message .= success(__($msg), true);
        }

        foreach (['errors', 'warnings', 'information', 'messages'] as $type) {
            session()->remove($type);
        }
    }

    return $message;
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
    return alert('info', $msg, $immediately);
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
    return alert('warning', $msg, $immediately);
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
    return alert('danger', $msg, $immediately);
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
    return alert('success', $msg, $immediately);
}

/**
 * Renders an alert message with the given alert-* class.
 *
 * @param string $class
 * @param string $msg
 * @param bool   $immediately
 * @return string
 */
function alert($class, $msg, $immediately = false)
{
    if (empty($msg)) {
        return '';
    }

    if ($immediately) {
        return '<div class="alert alert-' . $class . '">' . $msg . '</div>';
    }

    $session = session();
    $message = $session->get('msg', '');
    $message .= alert($class, $msg, true);
    $session->set('msg', $message);

    return '';
}
