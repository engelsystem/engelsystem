<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Translation;

use Engelsystem\Helpers\Translation\GettextTranslator;
use Engelsystem\Helpers\Translation\TranslationNotFound;
use Engelsystem\Test\Unit\ServiceProviderTest;
use Gettext\Translation;
use Gettext\Translations;

class GettextTranslatorTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\Translation\GettextTranslator::assertHasTranslation()
     */
    public function testNoTranslation(): void
    {
        $translations = $this->getTranslations();
        $translator = GettextTranslator::createFromTranslations($translations);

        $this->assertEquals('Translation!', $translator->gettext('test.value'));

        $this->expectException(TranslationNotFound::class);
        $this->expectExceptionMessage('//foo.bar');

        $translator->gettext('foo.bar');
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\GettextTranslator::translate()
     */
    public function testTranslate(): void
    {
        $translations = $this->getTranslations();
        $translator = GettextTranslator::createFromTranslations($translations);

        $this->assertEquals('Translation!', $translator->gettext('test.value'));
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\GettextTranslator::translatePlural
     */
    public function testTranslatePlural(): void
    {
        $translations = $this->getTranslations();
        $translator = GettextTranslator::createFromTranslations($translations);

        $this->assertEquals('Translations!', $translator->ngettext('test.value', 'test.value', 2));
    }

    protected function getTranslations(): Translations
    {
        $translation = Translation::create(null, 'test.value')
            ->translate('Translation!')
            ->translatePlural('Translations!');

        $translations = Translations::create();
        $translations->add($translation);

        return $translations;
    }
}
