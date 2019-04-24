<?php

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Config\Config;
use Engelsystem\Helpers\TranslationServiceProvider;
use Engelsystem\Helpers\Translator;
use Engelsystem\Test\Unit\ServiceProviderTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;

class TranslationServiceProviderTest extends ServiceProviderTest
{
    /**
     * @covers \Engelsystem\Helpers\TranslationServiceProvider::register()
     */
    public function testRegister()
    {
        $app = $this->getApp(['make', 'instance', 'get']);
        /** @var Config|MockObject $config */
        $config = $this->createMock(Config::class);
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);

        /** @var TranslationServiceProvider|MockObject $serviceProvider */
        $serviceProvider = $this->getMockBuilder(TranslationServiceProvider::class)
            ->setConstructorArgs([$app])
            ->setMethods(['initGettext', 'setLocale'])
            ->getMock();

        $serviceProvider->expects($this->once())
            ->method('initGettext');

        $app->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['config'], ['session'])
            ->willReturnOnConsecutiveCalls($config, $session);

        $defaultLocale = 'fo_OO';
        $locale = 'te_ST.WTF-9';
        $locales = ['fo_OO' => 'Foo', 'fo_OO.BAR' => 'Foo (Bar)', 'te_ST.WTF-9' => 'WTF\'s Testing?'];
        $config->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['locales'],
                ['default_locale']
            )
            ->willReturnOnConsecutiveCalls(
                $locales,
                $defaultLocale
            );

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
                    'locale'               => $locale,
                    'locales'              => $locales,
                    'localeChangeCallback' => [$serviceProvider, 'setLocale'],
                ]
            )
            ->willReturn($translator);

        $app->expects($this->exactly(2))
            ->method('instance')
            ->withConsecutive(
                [Translator::class, $translator],
                ['translator', $translator]
            );

        $serviceProvider->register();
    }
}
