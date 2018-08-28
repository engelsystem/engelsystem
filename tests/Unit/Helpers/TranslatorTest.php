<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Translator;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

class TranslatorTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\Translator::__construct()
     * @covers \Engelsystem\Helpers\Translator::setLocale()
     * @covers \Engelsystem\Helpers\Translator::setLocales()
     * @covers \Engelsystem\Helpers\Translator::getLocale()
     * @covers \Engelsystem\Helpers\Translator::getLocales()
     * @covers \Engelsystem\Helpers\Translator::hasLocale()
     */
    public function testInit()
    {
        $locales = ['te_ST.ER-01' => 'Tests', 'fo_OO' => 'SomeFOO'];
        $locale = 'te_ST.ER-01';

        /** @var callable|MockObject $callable */
        $callable = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $callable->expects($this->exactly(2))
            ->method('__invoke')
            ->withConsecutive(['te_ST.ER-01'], ['fo_OO']);

        $translator = new Translator($locale, $locales, $callable);

        $this->assertEquals($locales, $translator->getLocales());
        $this->assertEquals($locale, $translator->getLocale());

        $translator->setLocale('fo_OO');
        $this->assertEquals('fo_OO', $translator->getLocale());

        $newLocales = ['lo_RM' => 'Lorem', 'ip_SU-M' => 'Ipsum'];
        $translator->setLocales($newLocales);
        $this->assertEquals($newLocales, $translator->getLocales());

        $this->assertTrue($translator->hasLocale('ip_SU-M'));
        $this->assertFalse($translator->hasLocale('te_ST.ER-01'));
    }

    /**
     * @covers \Engelsystem\Helpers\Translator::translate()
     */
    public function testTranslate()
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->getMockBuilder(Translator::class)
            ->setConstructorArgs(['de_DE.UTF-8', ['de_DE.UTF-8' => 'Deutsch']])
            ->setMethods(['translateGettext'])
            ->getMock();
        $translator->expects($this->once())
            ->method('translateGettext')
            ->with('My favourite number is %u!')
            ->willReturn('Meine Lieblingszahl ist die %u!');

        $return = $translator->translate('My favourite number is %u!', [3]);
        $this->assertEquals('Meine Lieblingszahl ist die 3!', $return);
    }
}


