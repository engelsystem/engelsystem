<?php

/**
 * Return currently active locale
 *
 * @return string
 */
function locale()
{
    return session()->get('locale');
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
    $request = request();
    $session = session();

    if ($request->has('set_locale') && isset($locales[$request->input('set_locale')])) {
        $session->set('locale', $request->input('set_locale'));
    } elseif (!$session->has('locale')) {
        $session->set('locale', config('default_locale'));
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
        $locale = session()->get('locale');
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
    $request = app('request');

    $items = [];
    foreach (config('locales') as $locale => $name) {
        $url = url($request->getPathInfo(), ['set_locale' => $locale]);

        $items[] = toolbar_item_link(
            htmlspecialchars($url),
            '',
            sprintf(
                '<img src="%s" alt="%s" title="%2$s"> %2$s',
                url('pic/flag/' . $locale . '.png'),
                $name
            )
        );
    }
    return $items;
}
