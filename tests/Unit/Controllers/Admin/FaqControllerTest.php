<?php

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\FaqController;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Faq;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Session\Session;

class FaqControllerTest extends ControllerTest
{
    /** @var array */
    protected array $data = [
        'question' => 'Foo?',
        'text'     => 'Bar!',
    ];

    /**
     * @covers \Engelsystem\Controllers\Admin\FaqController::__construct
     * @covers \Engelsystem\Controllers\Admin\FaqController::edit
     * @covers \Engelsystem\Controllers\Admin\FaqController::showEdit
     */
    public function testEdit(): void
    {
        $this->request->attributes->set('faq_id', 1);
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
    public function testSaveCreateInvalid(): void
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
    public function testSaveCreateEdit(): void
    {
        $this->request->attributes->set('faq_id', 2);
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
    public function testSavePreview(): void
    {
        $this->request->attributes->set('faq_id', 1);
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
    public function testSaveDelete(): void
    {
        $this->request->attributes->set('faq_id', 1);
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

        (new Faq([
            'question' => 'Lorem',
            'text'     => 'Ipsum!',
        ]))->save();
    }
}
