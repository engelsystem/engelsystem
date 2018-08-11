<?php

/**
 * Returns messages from session and removes them from the stack
 *
 * @return string
 */
function msg()
{
    $session = session();

    $message = $session->get('msg', '');
    $session->set('msg', '');

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
