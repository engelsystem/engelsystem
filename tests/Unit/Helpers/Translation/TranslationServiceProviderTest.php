<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Translation;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Helpers\Translation\TranslationServiceProvider;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;

#[CoversMethod(TranslationServiceProvider::class, 'register')]
#[CoversMethod(TranslationServiceProvider::class, 'boot')]
#[CoversMethod(TranslationServiceProvider::class, 'getTranslator')]
#[CoversMethod(TranslationServiceProvider::class, 'getFile')]
#[CoversMethod(TranslationServiceProvider::class, 'getFileLoader')]
#[CoversMethod(TranslationServiceProvider::class, 'loadFile')]
#[AllowMockObjectsWithoutExpectations]
class TranslationServiceProviderTest extends ServiceProviderTestCase
{
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

        $app = $this->getAppMock(['make', 'singleton', 'alias', 'get']);
        $session = $this->createMock(Session::class);
        $translator = $this->createMock(Translator::class);

        $serviceProvider = $this->getStubBuilder(TranslationServiceProvider::class)
            ->setConstructorArgs([$app])
            ->onlyMethods(['setLocale'])
            ->getStub();

        $request = (new Request())->withAddedHeader('Accept-Language', 'te_ST');

        $app->method('get')
            ->willReturnMap([
                ['config', $config],
                ['path.lang', __DIR__ . '/Assets'],
                [Translator::class, $translator],
                ['session', $session],
                ['request', $request],
            ]);

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

    public function testGetTranslator(): void
    {
        $app = $this->getConfiguredApp();

        $serviceProvider = new TranslationServiceProvider($app);

        // Get translator
        $translator = $serviceProvider->getTranslator('fo_OO');
        $this->assertEquals('Foo Bar!', $translator->gettext('foo.bar'));
        $this->assertEquals('Foo Bar required!', $translator->gettext('validation.foo.bar'));

        // Retry from cache
        $serviceProvider->getTranslator('fo_OO');
    }

    public function testGetTranslatorFromPo(): void
    {
        $app = $this->getConfiguredApp();

        $serviceProvider = new TranslationServiceProvider($app);

        // Get translator using a .po file
        $translator = $serviceProvider->getTranslator('ba_RR');
        $this->assertEquals('B Arr!', $translator->gettext('foo.bar'));
        $this->assertEquals('B Arr required!', $translator->gettext('validation.foo.bar'));
    }

    public function testGetTranslatorCustom(): void
    {
        $app = $this->getConfiguredApp();

        $serviceProvider = new TranslationServiceProvider($app);

        // Get translation from the custom.po file
        $translator = $serviceProvider->getTranslator('ba_RR');
        $this->assertEquals('Custom default', $translator->gettext('msg.default'));
        $this->assertEquals('Custom additional', $translator->gettext('msg.additional'));
        $this->assertEquals('Custom overwritten', $translator->gettext('msg.overwritten'));
    }

    private function getConfiguredApp(): Application&MockObject
    {
        $app = $this->getAppMock(['get']);
        $app->method('get')
            ->willReturnMap([
                ['path.lang', __DIR__ . '/Assets'],
                ['path.config', __DIR__ . '/Assets/config'],
            ]);

        return $app;
    }
}
