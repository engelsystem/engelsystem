<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Translation;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\Translation\TranslationServiceProvider;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;

class TranslationServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::register
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::boot
     */
    public function testRegisterBoot(): void
    {
        $languages = [
            'ba_RR' => 'language.ba_RR',
            'config' => 'language.config',
            'fo_OO' => 'language.fo_OO',
        ];
        $config = new Config([
            'config_options' => [
                'system' => [
                    'config' => [
                        'locales' => [],
                        'default_locale' => [],
                    ],
                ],
            ],
        ]);

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

        $app->expects($this->exactly(6))
            ->method('get')
            ->withConsecutive(['config'], ['path.lang'], [Translator::class], ['config'], ['session'], ['request'])
            ->willReturnOnConsecutiveCalls($config, __DIR__ . '/Assets', $translator, $config, $session, $request);


        $app->expects($this->once())
            ->method('make')
            ->with(
                Translator::class,
                [
                    'locale'                => 'ba_RR',
                    'locales'               => ['ba_RR', 'config', 'fo_OO'],
                    'fallbackLocale'        => 'ba_RR',
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

        $this->assertEquals(
            ['default' => ['ba_RR', 'config', 'fo_OO'], 'data' => $languages],
            $config->get('config_options')['system']['config']['locales']
        );
        $this->assertEquals(
            ['default' => 'ba_RR', 'data' => $languages],
            $config->get('config_options')['system']['config']['default_locale']
        );

        $session->expects($this->once())
            ->method('get')
            ->with('locale', 'fo_OO')
            ->willReturn('ba_RR');
        $session->expects($this->once())
            ->method('set')
            ->with('locale', 'ba_RR');
        $this->setExpects($translator, 'setLocale', ['ba_RR']);

        $config->set('locales', ['ba_RR', 'fo_OO']);
        $config->set('default_locale', 'fo_OO');

        $serviceProvider->boot();
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::getTranslator
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::getFile
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::getFileLoader
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::loadFile
     */
    public function testGetTranslator(): void
    {
        $serviceProvider = new TranslationServiceProvider($this->app);

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
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::getFileLoader
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::loadFile
     */
    public function testGetTranslatorFromPo(): void
    {
        $serviceProvider = new TranslationServiceProvider($this->app);

        // Get translator using a .po file
        $translator = $serviceProvider->getTranslator('ba_RR');
        $this->assertEquals('B Arr!', $translator->gettext('foo.bar'));
        $this->assertEquals('B Arr required!', $translator->gettext('validation.foo.bar'));
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::getTranslator
     */
    public function testGetTranslatorCustom(): void
    {
        $serviceProvider = new TranslationServiceProvider($this->app);

        // Get translation from the custom.po file
        $translator = $serviceProvider->getTranslator('ba_RR');
        $this->assertEquals('Custom default', $translator->gettext('msg.default'));
        $this->assertEquals('Custom additional', $translator->gettext('msg.additional'));
        $this->assertEquals('Custom overwritten', $translator->gettext('msg.overwritten'));
    }

    /**
     * @covers \Engelsystem\Helpers\Translation\TranslationServiceProvider::getTranslator
     */
    public function testGetTranslatorPlugins(): void
    {
        $this->app->instance('a', __DIR__ . '/Stub/TestLangPlugin/');
        $this->app->tag('a', 'plugin.path');

        $serviceProvider = new TranslationServiceProvider($this->app);

        $translator = $serviceProvider->getTranslator('de_DE');
        $this->assertEquals('from de po', $translator->gettext('some.test.content'));
        $this->assertEquals('Plugin DE foo.bar', $translator->gettext('foo.bar'));

        $translator = $serviceProvider->getTranslator('te_ST');
        $this->assertEquals('from_mo', $translator->gettext('some.test.content'));
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->app->instance('path.lang', __DIR__ . '/Assets');
        $this->app->instance('path.config', __DIR__ . '/Assets/config');
    }
}
