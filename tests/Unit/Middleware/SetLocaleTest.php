<?php

namespace Engelsystem\Test\Unit\Middleware;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Middleware\SetLocale;
use Engelsystem\Models\User\Settings;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class SetLocaleTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Middleware\SetLocale::__construct
     * @covers \Engelsystem\Middleware\SetLocale::process
     */
    public function testRegister()
    {
        $this->initDatabase();

        /** @var Authenticator|MockObject $auth */
        $auth = $this->createMock(Authenticator::class);
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);
        /** @var Session|MockObject $session */
        $session = $this->createMock(Session::class);
        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->getMockForAbstractClass(RequestHandlerInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->getMockForAbstractClass(ResponseInterface::class);

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

        $translator->expects($this->exactly(2))
            ->method('hasLocale')
            ->withConsecutive(
                ['en_US'],
                [$locale]
            )
            ->willReturnOnConsecutiveCalls(
                false,
                true
            );
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
