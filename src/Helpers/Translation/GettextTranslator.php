<?php

namespace Engelsystem\Helpers\Translation;

use Gettext\Translator;

class GettextTranslator extends Translator
{
    /**
     * @param string|null $domain
     * @param string|null $context
     * @param string      $original
     * @return string
     * @throws TranslationNotFound
     */
    protected function translate(?string $domain, ?string $context, string $original): string
    {
        $this->assertHasTranslation($domain, $context, $original);

        return parent::translate($domain, $context, $original);
    }

    /**
     * @param string|null $domain
     * @param string|null $context
     * @param string      $original
     * @param string      $plural
     * @param int         $value
     * @return string
     * @throws TranslationNotFound
     */
    protected function translatePlural(
        ?string $domain,
        ?string $context,
        string $original,
        string $plural,
        int $value
    ): string {
        $this->assertHasTranslation($domain, $context, $original);

        return parent::translatePlural($domain, $context, $original, $plural, $value);
    }

    /**
     * @param string $domain
     * @param string $context
     * @param string $original
     * @throws TranslationNotFound
     */
    protected function assertHasTranslation($domain, $context, $original)
    {
        if ($this->getTranslation($domain, $context, $original)) {
            return;
        }

        throw new TranslationNotFound(implode('/', [$domain, $context, $original]));
    }
}
