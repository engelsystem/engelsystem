<?php

namespace Engelsystem\Test\Unit\Helpers\Translation;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Translation\TranslationServiceProvider;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;

class TranslationServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::register
     */
    public function testRegister(): void
    {
        $defaultLocale = 'fo_OO';
        $locale = 'te_ST.WTF-9';
        $locales = ['fo_OO' => 'Foo', 'fo_OO.BAR' => 'Foo (Bar)', 'te_ST.WTF-9' => 'WTF\'s Testing?'];
        $config = new Config(['locales' => $locales, 'default_locale' => $defaultLocale]);

        $app = $this->getApp(['make', 'singleton', 'alias', 'get']);
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);

        /** @var TranslationServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(TranslationServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['setLocale'])
            ->getMock();

        $app->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['config'], ['session'])
            ->willReturnOnConsecutiveCalls($config, $session);

        $session->expects($this->once())
            ->method('get')
            ->with('locale', $defaultLocale)
            ->willReturn($locale);
        $session->expects($this->once())
            ->method('set')
            ->with('locale', $locale);

        $app->expects($this->once())
            ->method('make')
            ->with(
                Translator::class,
                [
                    'locale'                => $locale,
                    'locales'               => $locales,
                    'fallbackLocale'        => 'en_US',
                    'getTranslatorCallback' => [$serviceProvider, 'getTranslator'],
                    'localeChangeCallback'  => [$serviceProvider, 'setLocale'],
                ]
            )
            ->willReturn($translator);

        $app->expects($this->once())
            ->method('singleton')
            ->willReturnCallback(function (string $abstract, callable $callback) use ($translator) {
                $this->assertEquals(Translator::class, $abstract);
                $this->assertEquals($translator, $callback());
            });

        $app->expects($this->once())
            ->method('alias')
            ->with(Translator::class, 'translator');

        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::getTranslator
     */
    public function testGetTranslator(): void
    {
        $app = $this->getApp(['get']);
        $serviceProvider = new TranslationServiceProvider($app);

        $this->setExpects($app, 'get', ['path.lang'], __DIR__ . '/Assets');

        // Get translator
        $translator = $serviceProvider->getTranslator('fo_OO');
        $this->assertEquals('Foo Bar!', $translator->gettext('foo.bar'));

        // Retry from cache
        $serviceProvider->getTranslator('fo_OO');
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::getTranslator
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::getFile
     */
    public function testGetTranslatorFromPo(): void
    {
        $app = $this->getApp(['get']);
        $this->setExpects($app, 'get', ['path.lang'], __DIR__ . '/Assets');

        $serviceProvider = new TranslationServiceProvider($app);

        // Get translator using a .po file
        $translator = $serviceProvider->getTranslator('ba_RR');
        $this->assertEquals('B Arr!', $translator->gettext('foo.bar'));
    }
}
