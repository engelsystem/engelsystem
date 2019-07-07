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
     * @param string   $locale
     * @param string   $fallbackLocale
     * @param callable $getTranslatorCallback
     * @param string[] $locales
     * @param callable $localeChangeCallback
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
     * @param string $key
     * @param array  $replace
     * @return string
     */
    public function translate(string $key, array $replace = []): string
    {
        return $this->translateText('gettext', [$key], $replace);
    }

    /**
     * Get the translation for a given key
     *
     * @param string $key
     * @param string $pluralKey
     * @param int    $number
     * @param array  $replace
     * @return string
     */
    public function translatePlural(string $key, string $pluralKey, int $number, array $replace = []): string
    {
        return $this->translateText('ngettext', [$key, $pluralKey, $number], $replace);
    }

    /**
     * @param string $type
     * @param array  $parameters
     * @param array  $replace
     * @return mixed|string
     */
    protected function translateText(string $type, array $parameters, array $replace = [])
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
     * @param string $key
     * @param array  $replace
     * @return mixed|string
     */
    protected function replaceText(string $key, array $replace = [])
    {
        if (empty($replace)) {
            return $key;
        }

        return call_user_func_array('sprintf', array_merge([$key], $replace));
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale)
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

    /**
     * @param string $locale
     * @return bool
     */
    public function hasLocale(string $locale): bool
    {
        return isset($this->locales[$locale]);
    }

    /**
     * @param string[] $locales
     */
    public function setLocales(array $locales)
    {
        $this->locales = $locales;
    }
}
