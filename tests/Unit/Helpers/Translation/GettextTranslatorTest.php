<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Translation;

use Engelsystem\Helpers\Translation\GettextTranslator;
use Engelsystem\Helpers\Translation\TranslationNotFound;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use Gettext\Translation;
use Gettext\Translations;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(GettextTranslator::class, 'assertHasTranslation')]
#[CoversMethod(GettextTranslator::class, 'translate')]
#[CoversMethod(GettextTranslator::class, 'translatePlural')]
class GettextTranslatorTest extends ServiceProviderTestCase
{
    public function testNoTranslation(): void
    {
        $translations = $this->getTranslations();
        $translator = GettextTranslator::createFromTranslations($translations);

        $this->assertEquals('Translation!', $translator->gettext('test.value'));

        $this->expectException(TranslationNotFound::class);
        $this->expectExceptionMessage('//foo.bar');

        $translator->gettext('foo.bar');
    }

    public function testTranslate(): void
    {
        $translations = $this->getTranslations();
        $translator = GettextTranslator::createFromTranslations($translations);

        $this->assertEquals('Translation!', $translator->gettext('test.value'));
    }

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
