<?php

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\Admin\FaqController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Faq;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FaqControllerTest extends TestCase
{
    use HasDatabase;

    /** @var array */
    protected $data = [
        'question' => 'Foo?',
        'text'     => 'Bar!',
    ];

    /** @var TestLogger */
    protected $log;

    /** @var Response|MockObject */
    protected $response;

    /** @var Request */
    protected $request;

    /**
     * @covers \Engelsystem\Controllers\Admin\FaqController::__construct
     * @covers \Engelsystem\Controllers\Admin\FaqController::edit
     * @covers \Engelsystem\Controllers\Admin\FaqController::showEdit
     */
    public function testEdit()
    {
        $this->request->attributes->set('id', 1);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/faq/edit.twig', $view);

                /** @var Collection $warnings */
                $warnings = $data['messages'];
                $this->assertNotEmpty($data['faq']);
                $this->assertTrue($warnings->isEmpty());

                return $this->response;
            });

        /** @var FaqController $controller */
        $controller = $this->app->make(FaqController::class);

        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\FaqController::save
     */
    public function testSaveCreateInvalid()
    {
        /** @var FaqController $controller */
        $this->expectException(ValidationException::class);

        $controller = $this->app->make(FaqController::class);
        $controller->setValidator(new Validator());
        $controller->save($this->request);
    }

    /**
     * @covers       \Engelsystem\Controllers\Admin\FaqController::save
     */
    public function testSaveCreateEdit()
    {
        $this->request->attributes->set('id', 2);
        $body = $this->data;

        $this->request = $this->request->withParsedBody($body);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/faq#faq-2')
            ->willReturn($this->response);

        /** @var FaqController $controller */
        $controller = $this->app->make(FaqController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Updated'));

        /** @var Session $session */
        $session = $this->app->get('session');
        $messages = $session->get('messages');
        $this->assertEquals('faq.edit.success', $messages[0]);

        $faq = (new Faq())->find(2);
        $this->assertEquals('Foo?', $faq->question);
        $this->assertEquals('Bar!', $faq->text);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\FaqController::save
     */
    public function testSavePreview()
    {
        $this->request->attributes->set('id', 1);
        $this->request = $this->request->withParsedBody([
            'question' => 'New question',
            'text'     => 'New text',
            'preview'  => '1',
        ]);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/faq/edit.twig', $view);

                /** @var Faq $faq */
                $faq = $data['faq'];
                // Contains new text
                $this->assertEquals('New question', $faq->question);
                $this->assertEquals('New text', $faq->text);

                return $this->response;
            });

        /** @var FaqController $controller */
        $controller = $this->app->make(FaqController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        // Assert no changes
        $faq = Faq::find(1);
        $this->assertEquals('Lorem', $faq->question);
        $this->assertEquals('Ipsum!', $faq->text);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\FaqController::save
     */
    public function testSaveDelete()
    {
        $this->request->attributes->set('id', 1);
        $this->request = $this->request->withParsedBody([
            'question' => '.',
            'text'     => '.',
            'delete'   => '1',
        ]);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/faq')
            ->willReturn($this->response);

        /** @var FaqController $controller */
        $controller = $this->app->make(FaqController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Deleted'));

        /** @var Session $session */
        $session = $this->app->get('session');
        $messages = $session->get('messages');
        $this->assertEquals('faq.delete.success', $messages[0]);
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->request = Request::create('http://localhost');
        $this->app->instance('request', $this->request);

        $this->response = $this->createMock(Response::class);
        $this->app->instance(Response::class, $this->response);

        $this->log = new TestLogger();
        $this->app->instance(LoggerInterface::class, $this->log);

        $this->app->instance('session', new Session(new MockArraySessionStorage()));

        $this->app->bind(UrlGeneratorInterface::class, UrlGenerator::class);

        $this->app->instance('config', new Config());

        (new Faq([
            'question' => 'Lorem',
            'text'     => 'Ipsum!',
        ]))->save();
    }
}
