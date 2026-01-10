<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Translation;

use Engelsystem\Helpers\Translation\GettextTranslator;
use Engelsystem\Helpers\Translation\TranslationNotFound;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use Engelsystem\Test\Utils\ClosureMock;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Translator::class, '__construct')]
#[CoversMethod(Translator::class, 'getLocale')]
#[CoversMethod(Translator::class, 'getLocales')]
#[CoversMethod(Translator::class, 'hasLocale')]
#[CoversMethod(Translator::class, 'setLocale')]
#[CoversMethod(Translator::class, 'setLocales')]
#[CoversMethod(Translator::class, 'translate')]
#[CoversMethod(Translator::class, 'translatePlural')]
#[CoversMethod(Translator::class, 'translateText')]
#[CoversMethod(Translator::class, 'replaceText')]
class TranslatorTest extends ServiceProviderTestCase
{
    public function testInit(): void
    {
        $locales = ['te_ST' => 'Tests', 'fo_OO' => 'SomeFOO'];
        $locale = 'te_ST';

        $localeChange = $this->createPartialMock(ClosureMock::class, ['__invoke']);
        $matcher = $this->exactly(2);
        $localeChange->expects($matcher)
            ->method('__invoke')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('te_ST', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('fo_OO', $parameters[0]);
                }
            });

        $translator = new Translator($locale, 'fo_OO', [$this, 'doNothing'], $locales, $localeChange);

        $this->assertEquals($locales, $translator->getLocales());
        $this->assertEquals($locale, $translator->getLocale());

        $translator->setLocale('fo_OO');
        $this->assertEquals('fo_OO', $translator->getLocale());

        $newLocales = ['lo_RM', 'ip_SU-M'];
        $translator->setLocales($newLocales);
        $this->assertEquals($newLocales, $translator->getLocales());

        $this->assertTrue($translator->hasLocale('ip_SU-M'));
        $this->assertFalse($translator->hasLocale('te_ST'));
    }

    public function testTranslate(): void
    {
        $translator = $this->getMockBuilder(Translator::class)
            ->setConstructorArgs(['de_DE', 'en_US', [$this, 'doNothing'], ['de_DE' => 'Deutsch']])
            ->onlyMethods(['translateText'])
            ->getMock();
        $matcher = $this->exactly(2);
        $translator->expects($matcher)
            ->method('translateText')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('gettext', $parameters[0]);
                    $this->assertSame(['Hello!'], $parameters[1]);
                    $this->assertSame([], $parameters[2]);
                    return 'Hallo!';
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('gettext', $parameters[0]);
                    $this->assertSame(['My favourite number is %u!'], $parameters[1]);
                    $this->assertSame([3], $parameters[2]);
                    return 'Meine Lieblingszahl ist die 3!';
                }
            });

        $return = $translator->translate('Hello!');
        $this->assertEquals('Hallo!', $return);

        $return = $translator->translate('My favourite number is %u!', [3]);
        $this->assertEquals('Meine Lieblingszahl ist die 3!', $return);
    }

    public function testTranslatePlural(): void
    {
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

    public function testReplaceText(): void
    {
        $gtt = $this->createMock(GettextTranslator::class);
        $getTranslator = $this->createPartialMock(ClosureMock::class, ['__invoke']);
        $matcher = $this->exactly(5);
        $getTranslator->expects($matcher)
            ->method('__invoke')->willReturnCallback(function (...$parameters) use ($matcher, $gtt) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('te_ST', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('fo_OO', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame('te_ST', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('fo_OO', $parameters[0]);
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame('te_ST', $parameters[0]);
                }
                return $gtt;
            });

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
