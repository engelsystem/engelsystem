<?php

namespace Engelsystem\Test\Unit\Helpers\Translation;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Translation\TranslationServiceProvider;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use Symfony\Component\HttpFoundation\Session\Session;

class TranslationServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::register
     */
    public function testRegister(): void
    {
        $locale = 'te_ST';
        $locales = ['fo_OO' => 'Foo', 'fo_OO.BAR' => 'Foo (Bar)', 'te_ST' => 'WTF\'s Testing?'];
        $config = new Config(['locales' => $locales, 'default_locale' => 'fo_OO']);

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

        $request = (new Request())->withAddedHeader('Accept-Language', 'te_ST');

        $app->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['config'], ['session'], ['request'])
            ->willReturnOnConsecutiveCalls($config, $session, $request);

        $session->expects($this->once())
            ->method('get')
            ->with('locale', 'te_ST')
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
            ->willReturnCallback(function (string $abstract, callable $callback) use ($translator): void {
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

        $this->setExpects($app, 'get', ['path.lang'], __DIR__ . '/Assets', new InvokedCount(2));

        // Get translator
        $translator = $serviceProvider->getTranslator('fo_OO');
        $this->assertEquals('Foo Bar!', $translator->gettext('foo.bar'));
        $this->assertEquals('Foo Bar required!', $translator->gettext('validation.foo.bar'));

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
        $this->setExpects($app, 'get', ['path.lang'], __DIR__ . '/Assets', new InvokedCount(2));

        $serviceProvider = new TranslationServiceProvider($app);

        // Get translator using a .po file
        $translator = $serviceProvider->getTranslator('ba_RR');
        $this->assertEquals('B Arr!', $translator->gettext('foo.bar'));
        $this->assertEquals('B Arr required!', $translator->gettext('validation.foo.bar'));
    }
}
