<?php

namespace Engelsystem\Helpers;

class Translator
{
    /** @var string[] */
    protected $locales;

    /** @var string */
    protected $locale;

    /** @var callable */
    protected $localeChangeCallback;

    /**
     * Translator constructor.
     *
     * @param string   $locale
     * @param string[] $locales
     * @param callable $localeChangeCallback
     */
    public function __construct(string $locale, array $locales = [], callable $localeChangeCallback = null)
    {
        $this->localeChangeCallback = $localeChangeCallback;

        $this->setLocale($locale);
        $this->setLocales($locales);
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
        $translated = $this->translateGettext($key);

        if (!empty($replace)) {
            $translated = call_user_func_array('sprintf', array_merge([$translated], $replace));
        }

        return $translated;
    }

    /**
     * Translate the key via gettext
     *
     * @param string $key
     * @return string
     * @codeCoverageIgnore
     */
    protected function translateGettext(string $key): string
    {
        return _($key);
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
