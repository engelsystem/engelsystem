<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Translation;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Http\Request;
use Gettext\Loader\MoLoader;
use Gettext\Loader\PoLoader;
use Gettext\Translations;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Session\Session;

class TranslationServiceProvider extends ServiceProvider
{
    protected GettextTranslator|array $translators = [];

    public function register(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');
        $configOptions = $config['config_options'];

        $langPath = $this->app->get('path.lang');
        $locales = array_diff(scandir($langPath), ['.', '..']);
        $locales = array_values(array_filter($locales, fn($lang) => is_dir($langPath . '/' . $lang)));

        $fallbackLocale = in_array('en_US', $locales) ? 'en_US' : $locales[0];

        $namedLocales = array_flip($locales);
        array_walk($namedLocales, function (&$value, $key): void {
            $value = 'language.' . $key;
        });
        $configOptions['system']['config']['locales']['default'] = $locales;
        $configOptions['system']['config']['locales']['data'] = $namedLocales;
        $configOptions['system']['config']['default_locale']['default'] = $fallbackLocale;
        $configOptions['system']['config']['default_locale']['data'] = $namedLocales;

        $config['locales'] = $configOptions['locales'] ?? $locales;
        $config['default_locale'] = $configOptions['default_locale'] ?? $fallbackLocale;

        $config->set('config_options', $configOptions);

        $translator = $this->app->make(
            Translator::class,
            [
                'locale'                => $fallbackLocale,
                'locales'               => $locales,
                'fallbackLocale'        => $fallbackLocale,
                'getTranslatorCallback' => [$this, 'getTranslator'],
                'localeChangeCallback'  => [$this, 'setLocale'],
            ],
        );
        $this->app->singleton(Translator::class, fn() => $translator);
        $this->app->alias(Translator::class, 'translator');
    }

    public function boot(): void
    {
        /** @var Translator $translator */
        $translator = $this->app->get(Translator::class);

        /** @var Config $config */
        $config = $this->app->get('config');
        /** @var Session $session */
        $session = $this->app->get('session');
        /** @var Request $request */
        $request = $this->app->get('request');

        $locales = $config->get('locales', []);
        $defaultLocale = $config->get('default_locale');
        $locale = $request->getPreferredLanguage(array_merge([$defaultLocale], $locales));

        $sessionLocale = $session->get('locale', $locale);
        if (in_array($sessionLocale, $locales)) {
            $locale = $sessionLocale;
        }

        $session->set('locale', $locale);
        $translator->setLocale($locale);
    }

    /**
     * @codeCoverageIgnore
     */
    public function setLocale(string $locale): void
    {
        $locale .= '.UTF-8';
        // Set the users locale
        putenv('LC_ALL=' . $locale);
        setlocale(LC_ALL, $locale);

        // Reset numeric formatting to allow output of floats
        putenv('LC_NUMERIC=C');
        setlocale(LC_NUMERIC, 'C');
    }

    public function getTranslator(string $locale): GettextTranslator
    {
        if (isset($this->translators[$locale])) {
            return $this->translators[$locale];
        }

        $names = ['default', 'additional'];

        /** @var Translations $translations */
        $translations = $this->app->call([Translations::class, 'create']);
        $path = $this->app->get('path.lang');
        foreach ($names as $name) {
            $file = $this->getFile($locale, $path, $name);
            $translations = $this->loadFile($file, $translations);
        }

        foreach ($this->app->tagged('plugin.path') as $path) {
            $filepath = $path . 'lang/' . $locale;
            $file = $filepath . '.mo';

            if (!file_exists($file)) {
                $file = $filepath . '.po';
            }

            $translations = $this->loadFile($file, $translations);
        }

        $file = $this->getFile($locale, $this->app->get('path.config') . '/lang', 'custom');
        $translations = $this->loadFile($file, $translations);

        /** @var GettextTranslator $translator */
        $translator = GettextTranslator::createFromTranslations($translations);
        $this->translators[$locale] = $translator;

        return $this->translators[$locale];
    }

    protected function loadFile(string $file, Translations $translations): Translations
    {
        if (!file_exists($file)) {
            return $translations;
        }

        $loader = $this->getFileLoader($file);

        return $loader->loadFile($file, $translations);
    }

    protected function getFile(string $locale, string $basePath, string $name = 'default'): string
    {
        $filepath = $basePath . '/' . $locale . '/' . $name;
        $file = $filepath . '.mo';

        if (!file_exists($file)) {
            $file = $filepath . '.po';
        }

        return $file;
    }

    /**
     * @throws BindingResolutionException
     */
    protected function getFileLoader(string $file): MoLoader|PoLoader
    {
        if (Str::endsWith($file, '.mo')) {
            /** @var MoLoader $loader */
            return $this->app->make(MoLoader::class);
        } else {
            /** @var PoLoader $loader */
            return $this->app->make(PoLoader::class);
        }
    }
}
