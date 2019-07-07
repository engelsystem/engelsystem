<?php

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
    public function testNoTranslation()
    {
        $translations = $this->getTranslations();

        $translator = new GettextTranslator();
        $translator->loadTranslations($translations);

        $this->assertEquals('Translation!', $translator->gettext('test.value'));

        $this->expectException(TranslationNotFound::class);
        $this->expectExceptionMessage('//foo.bar');

        $translator->gettext('foo.bar');
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\GettextTranslator::dpgettext()
     */
    public function testDpgettext()
    {
        $translations = $this->getTranslations();

        $translator = new GettextTranslator();
        $translator->loadTranslations($translations);

        $this->assertEquals('Translation!', $translator->dpgettext(null, null, 'test.value'));
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\GettextTranslator::dnpgettext()
     */
    public function testDnpgettext()
    {
        $translations = $this->getTranslations();

        $translator = new GettextTranslator();
        $translator->loadTranslations($translations);

        $this->assertEquals('Translations!', $translator->dnpgettext(null, null, 'test.value', 'test.values', 2));
    }

    protected function getTranslations(): Translations
    {
        $translations = new Translations();
        $translations[] =
            (new Translation(null, 'test.value', 'test.values'))
                ->setTranslation('Translation!')
                ->setPluralTranslations(['Translations!']);

        return $translations;
    }
}
