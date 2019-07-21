<?php

namespace Engelsystem\Helpers\Translation;

use Gettext\Translator;

class GettextTranslator extends Translator
{
    /**
     * @param string $domain
     * @param string $context
     * @param string $original
     * @return string
     * @throws TranslationNotFound
     */
    public function dpgettext($domain, $context, $original)
    {
        $this->assertHasTranslation($domain, $context, $original);

        return parent::dpgettext($domain, $context, $original);
    }

    /**
     * @param string $domain
     * @param string $context
     * @param string $original
     * @param string $plural
     * @param string $value
     * @return string
     * @throws TranslationNotFound
     */
    public function dnpgettext($domain, $context, $original, $plural, $value)
    {
        $this->assertHasTranslation($domain, $context, $original);

        return parent::dnpgettext($domain, $context, $original, $plural, $value);
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
