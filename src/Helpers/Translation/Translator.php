<?php

namespace Engelsystem\Helpers\Translation;

class Translator
{
    /** @var string[] */
    protected $locales;

    /** @var string */
    protected $locale;

    /** @var string */
    protected $fallbackLocale;

    /** @var callable */
    protected $getTranslatorCallback;

    /** @var callable */
    protected $localeChangeCallback;

    /**
     * Translator constructor.
     *
     * @param string[] $locales
     */
    public function __construct(
        string $locale,
        string $fallbackLocale,
        callable $getTranslatorCallback,
        array $locales = [],
        callable $localeChangeCallback = null
    ) {
        $this->localeChangeCallback = $localeChangeCallback;
        $this->getTranslatorCallback = $getTranslatorCallback;

        $this->setLocale($locale);
        $this->fallbackLocale = $fallbackLocale;
        $this->locales = $locales;
    }

    /**
     * Get the translation for a given key
     *
     * @param array  $replace
     */
    public function translate(string $key, array $replace = []): string
    {
        return $this->translateText('gettext', [$key], $replace);
    }

    /**
     * Get the translation for a given key
     *
     * @param array  $replace
     */
    public function translatePlural(string $key, string $pluralKey, int $number, array $replace = []): string
    {
        return $this->translateText('ngettext', [$key, $pluralKey, $number], $replace);
    }

    /**
     * @param array  $parameters
     * @param array  $replace
     */
    protected function translateText(string $type, array $parameters, array $replace = []): mixed
    {
        $translated = $parameters[0];

        foreach ([$this->locale, $this->fallbackLocale] as $lang) {
            /** @var GettextTranslator $translator */
            $translator = call_user_func($this->getTranslatorCallback, $lang);

            try {
                $translated = call_user_func_array([$translator, $type], $parameters);
                break;
            } catch (TranslationNotFound $e) {
            }
        }

        return $this->replaceText($translated, $replace);
    }

    /**
     * Replace placeholders
     *
     * @param array  $replace
     */
    protected function replaceText(string $key, array $replace = []): mixed
    {
        if (empty($replace)) {
            return $key;
        }

        return call_user_func_array('sprintf', array_merge([$key], array_values($replace)));
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;

        if (is_callable($this->localeChangeCallback)) {
            call_user_func_array($this->localeChangeCallback, [$locale]);
        }
    }

    /**
     * @return string[]
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    public function hasLocale(string $locale): bool
    {
        return isset($this->locales[$locale]);
    }

    /**
     * @param string[] $locales
     */
    public function setLocales(array $locales): void
    {
        $this->locales = $locales;
    }
}
