<?php

/**
 * Return currently active locale
 *
 * @return string
 */
function locale()
{
    return $_SESSION['locale'];
}

/**
 * Returns two letter language code from currently active locale
 *
 * @return string
 */
function locale_short()
{
    return substr(locale(), 0, 2);
}

/**
 * Initializes gettext for internationalization and updates the sessions locale to use for translation.
 */
function gettext_init()
{
    $locales = config('locales');
    $default_locale = config('default_locale');

    if (isset($_REQUEST['set_locale']) && isset($locales[$_REQUEST['set_locale']])) {
        $_SESSION['locale'] = $_REQUEST['set_locale'];
    } elseif (!isset($_SESSION['locale'])) {
        $_SESSION['locale'] = $default_locale;
    }

    gettext_locale();
    bindtextdomain('default', realpath(__DIR__ . '/../../locale'));
    bind_textdomain_codeset('default', 'UTF-8');
    textdomain('default');
}

/**
 * Swich gettext locale.
 *
 * @param string $locale
 */
function gettext_locale($locale = null)
{
    if ($locale == null) {
        $locale = $_SESSION['locale'];
    }

    putenv('LC_ALL=' . $locale);
    setlocale(LC_ALL, $locale);
}

/**
 * Renders language selection.
 *
 * @return array
 */
function make_langselect()
{
    $url = $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') > 0 ? '&' : '?') . 'set_locale=';

    $items = [];
    foreach (config('locales') as $locale => $name) {
        $items[] = toolbar_item_link(
            htmlspecialchars($url) . $locale,
            '',
            '<img src="pic/flag/' . $locale . '.png" alt="' . $name . '" title="' . $name . '"> ' . $name
        );
    }
    return $items;
}
