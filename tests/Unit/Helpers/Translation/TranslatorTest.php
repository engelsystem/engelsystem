<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Translation;

use Engelsystem\Helpers\Translation\GettextTranslator;
use Engelsystem\Helpers\Translation\TranslationNotFound;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

class TranslatorTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\Translation\Translator::__construct
     * @covers \Engelsystem\Helpers\Translation\Translator::getLocale
     * @covers \Engelsystem\Helpers\Translation\Translator::getLocales
     * @covers \Engelsystem\Helpers\Translation\Translator::hasLocale
     * @covers \Engelsystem\Helpers\Translation\Translator::setLocale
     * @covers \Engelsystem\Helpers\Translation\Translator::setLocales
     */
    public function testInit(): void
    {
        $locales = ['te_ST' => 'Tests', 'fo_OO' => 'SomeFOO'];
        $locale = 'te_ST';

        /** @var callable|MockObject $localeChange */
        $localeChange = $this->getMockBuilder(stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
        $localeChange->expects($this->exactly(2))
            ->method('__invoke')
            ->withConsecutive(['te_ST'], ['fo_OO']);

        $translator = new Translator($locale, 'fo_OO', [$this, 'doNothing'], $locales, $localeChange);

        $this->assertEquals($locales, $translator->getLocales());
        $this->assertEquals($locale, $translator->getLocale());

        $translator->setLocale('fo_OO');
        $this->assertEquals('fo_OO', $translator->getLocale());

        $newLocales = ['lo_RM' => 'Lorem', 'ip_SU-M' => 'Ipsum'];
        $translator->setLocales($newLocales);
        $this->assertEquals($newLocales, $translator->getLocales());

        $this->assertTrue($translator->hasLocale('ip_SU-M'));
        $this->assertFalse($translator->hasLocale('te_ST'));
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\Translator::translate
     */
    public function testTranslate(): void
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
            ->setConstructorArgs(['de_DE', 'en_US', [$this, 'doNothing'], ['de_DE' => 'Deutsch']])
            ->onlyMethods(['translateText'])
            ->getMock();
        $translator->expects($this->exactly(2))
            ->method('translateText')
            ->withConsecutive(['gettext', ['Hello!'], []], ['gettext', ['My favourite number is %u!'], [3]])
            ->willReturnOnConsecutiveCalls('Hallo!', 'Meine Lieblingszahl ist die 3!');

        $return = $translator->translate('Hello!');
        $this->assertEquals('Hallo!', $return);

        $return = $translator->translate('My favourite number is %u!', [3]);
        $this->assertEquals('Meine Lieblingszahl ist die 3!', $return);
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\Translator::translatePlural
     */
    public function testTranslatePlural(): void
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
            ->setConstructorArgs(['de_DE', 'en_US', [$this, 'doNothing'], ['de_DE' => 'Deutsch']])
            ->onlyMethods(['translateText'])
            ->getMock();
        $translator->expects($this->once())
            ->method('translateText')
            ->with('ngettext', ['%s apple', '%s apples', 2], [2])
            ->willReturn('2 Äpfel');

        $return = $translator->translatePlural('%s apple', '%s apples', 2, [2]);
        $this->assertEquals('2 Äpfel', $return);
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\Translator::translatePlural
     * @covers \Engelsystem\Helpers\Translation\Translator::translateText
     * @covers \Engelsystem\Helpers\Translation\Translator::replaceText
     */
    public function testReplaceText(): void
    {
        /** @var GettextTranslator|MockObject $gtt */
        $gtt = $this->createMock(GettextTranslator::class);
        /** @var callable|MockObject $getTranslator */
        $getTranslator = $this->getMockBuilder(stdClass::class)
            ->addMethods(['__invoke'])
            ->getMock();
        $getTranslator->expects($this->exactly(5))
            ->method('__invoke')
            ->withConsecutive(['te_ST'], ['fo_OO'], ['te_ST'], ['fo_OO'], ['te_ST'])
            ->willReturn($gtt);

        $i = 0;
        $gtt->expects($this->exactly(4))
            ->method('gettext')
            ->willReturnCallback(function () use (&$i) {
                $i++;
                if ($i != 4) {
                    throw new TranslationNotFound();
                }

                return 'Lorem %s???';
            });
        $this->setExpects($gtt, 'ngettext', ['foo.barf'], 'Lorem %s!');

        $translator = new Translator('te_ST', 'fo_OO', $getTranslator, ['te_ST' => 'Test', 'fo_OO' => 'Foo']);

        // No translation
        $this->assertEquals('foo.bar', $translator->translate('foo.bar'));

        // Fallback translation
        $this->assertEquals('Lorem test2???', $translator->translate('foo.batz', ['test2']));

        // Successful translation, keys in replaces should be ignored
        $this->assertEquals('Lorem test3!', $translator->translatePlural('foo.barf', 'foo.bar2', 3, ['x' => 'test3']));
    }

    /**
     * Does nothing
     */
    public function doNothing(): void
    {
    }
}
