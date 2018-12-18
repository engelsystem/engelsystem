<?php

namespace Engelsystem\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Symfony\Component\HttpFoundation\Session\Session;

class TranslationServiceProvider extends ServiceProvider
{
    public function register()
    {
        /** @var Config $config */
        $config = $this->app->get('config');
        /** @var Session $session */
        $session = $this->app->get('session');

        $locales = $config->get('locales');
        $locale = $config->get('default_locale');

        $sessionLocale = $session->get('locale', $locale);
        if (isset($locales[$sessionLocale])) {
            $locale = $sessionLocale;
        }

        $this->initGettext();
        $session->set('locale', $locale);

        $translator = $this->app->make(
            Translator::class,
            ['locale' => $locale, 'locales' => $locales, 'localeChangeCallback' => [$this, 'setLocale']]
        );
        $this->app->instance(Translator::class, $translator);
        $this->app->instance('translator', $translator);
    }

    /**
     * @param string $textDomain
     * @param string $encoding
     * @codeCoverageIgnore
     */
    protected function initGettext($textDomain = 'default', $encoding = 'UTF-8')
    {
        bindtextdomain($textDomain, $this->app->get('path.lang'));
        bind_textdomain_codeset($textDomain, $encoding);
        textdomain($textDomain);
    }

    /**
     * @param string $locale
     * @codeCoverageIgnore
     */
    public function setLocale($locale)
    {
        // Set the users locale
        putenv('LC_ALL=' . $locale);
        setlocale(LC_ALL, $locale);

        // Reset numeric formatting to allow output of floats
        putenv('LC_NUMERIC=C');
        setlocale(LC_NUMERIC, 'C');
    }
}
