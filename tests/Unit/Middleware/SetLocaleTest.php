<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Middleware\SetLocale;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

#[CoversMethod(SetLocale::class, '__construct')]
#[CoversMethod(SetLocale::class, 'process')]
class SetLocaleTest extends TestCase
{
    use HasDatabase;

    public function testRegister(): void
    {
        $this->initDatabase();

        $auth = $this->createMock(Authenticator::class);
        $translator = $this->createMock(Translator::class);
        $session = $this->createMock(Session::class);
        $request = $this->getMockBuilder(ServerRequestInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $response = $this->getStubBuilder(ResponseInterface::class)->getStub();

        /** @var User $user */
        $user = User::factory([
                'name'  => 'user',
                'email' => 'foo@bar.baz',
            ])
            ->has(Settings::factory(['language' => 'uf_UF']))
            ->create();

        $locale = 'te_ST';

        $request->expects($this->exactly(3))
            ->method('getQueryParams')
            ->willReturnOnConsecutiveCalls(
                [],
                ['set-locale' => 'en_US'],
                ['set-locale' => $locale]
            );

        $translator
            ->method('hasLocale')
            ->willReturnMap([
                ['en_US', false],
                [$locale, true],
            ]);
        $translator->expects($this->once())
            ->method('setLocale')
            ->with($locale);

        $session->expects($this->once())
            ->method('set')
            ->with('locale', $locale);

        $handler->expects($this->exactly(3))
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $this->setExpects($auth, 'user', null, $user);

        $middleware = new SetLocale($translator, $session, $auth);
        $middleware->process($request, $handler);

        $middleware->process($request, $handler);
        $this->assertEquals('uf_UF', $user->settings->language);

        $middleware->process($request, $handler);
        $this->assertEquals('te_ST', $user->settings->language);
    }
}
