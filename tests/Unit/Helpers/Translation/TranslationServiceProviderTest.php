<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Translation;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Helpers\Translation\TranslationServiceProvider;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Http\Request;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;

#[CoversMethod(TranslationServiceProvider::class, 'register')]
#[CoversMethod(TranslationServiceProvider::class, 'boot')]
#[CoversMethod(TranslationServiceProvider::class, 'getTranslator')]
#[CoversMethod(TranslationServiceProvider::class, 'getFile')]
#[CoversMethod(TranslationServiceProvider::class, 'getFileLoader')]
#[CoversMethod(TranslationServiceProvider::class, 'loadFile')]
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

        $matcher = $this->exactly(6);
        $app->expects($matcher)
            ->method('get')
            ->willReturnCallback(function (...$parameters) use ($matcher, $config, $translator, $session, $request) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('config', $parameters[0]);
                    return $config;
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('path.lang', $parameters[0]);
                    return __DIR__ . '/Assets';
                }
                if ($matcher->numberOfInvocations() === 3) {
                    $this->assertSame(Translator::class, $parameters[0]);
                    return $translator;
                }
                if ($matcher->numberOfInvocations() === 4) {
                    $this->assertSame('config', $parameters[0]);
                    return $config;
                }
                if ($matcher->numberOfInvocations() === 5) {
                    $this->assertSame('session', $parameters[0]);
                    return $session;
                }
                if ($matcher->numberOfInvocations() === 6) {
                    $this->assertSame('request', $parameters[0]);
                    return $request;
                }
            });


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
        $matcher = $this->exactly(2);
        $app->expects($matcher)
            ->method('get')->willReturnCallback(function (...$parameters) use ($matcher) {
                if ($matcher->numberOfInvocations() === 1) {
                    $this->assertSame('path.lang', $parameters[0]);
                    return __DIR__ . '/Assets';
                }
                if ($matcher->numberOfInvocations() === 2) {
                    $this->assertSame('path.config', $parameters[0]);
                    return __DIR__ . '/Assets/config';
                }
            });

        return $app;
    }
}
