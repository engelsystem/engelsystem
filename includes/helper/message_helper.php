<?php

/**
 * Gibt zwischengespeicherte Fehlermeldungen zurück und löscht den Zwischenspeicher
 *
 * @return string
 */
function msg()
{
    if (!isset($_SESSION['msg'])) {
        return "";
    }
    $msg = $_SESSION['msg'];
    $_SESSION['msg'] = "";
    return $msg;
}

/**
 * Rendert eine Information
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
 * Rendert eine Fehlermeldung
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
 * Rendert eine Erfolgsmeldung
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
 * Renders an alert with given alert-* class.
 *
 * @param string $class
 * @param string $msg
 * @param bool   $immediately
 * @return string|null
 */
function alert($class, $msg, $immediately = false)
{
    if ($immediately) {
        if ($msg == "") {
            return "";
        }
        return '<div class="alert alert-' . $class . '">' . $msg . '</div>';
    }

    if (!isset($_SESSION['msg'])) {
        $_SESSION['msg'] = "";
    }
    $_SESSION['msg'] .= alert($class, $msg, true);

    return null;
}
