<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\FaqController;
use Engelsystem\Http\Response;
use Engelsystem\Models\Faq;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FaqControllerTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Controllers\FaqController::__construct
     * @covers \Engelsystem\Controllers\FaqController::index
     */
    public function testIndex(): void
    {
        $this->initDatabase();
        (new Faq(['question' => 'Xyz', 'text' => 'Abc']))->save();
        (new Faq(['question' => 'Something\'s wrong?', 'text' => 'Nah!']))->save();

        $this->app->instance('session', new Session(new MockArraySessionStorage()));
        $config = new Config(['faq_text' => 'Some Text']);
        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) use ($response) {
                $this->assertEquals('pages/faq/overview.twig', $view);
                $this->assertEquals('Some Text', $data['text']);
                $this->assertEquals('Nah!', $data['items'][0]->text);

                return $response;
            });

        $controller = new FaqController($config, new Faq(), $response);
        $controller->index();
    }
}
