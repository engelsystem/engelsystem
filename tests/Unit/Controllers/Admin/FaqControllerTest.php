<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\FaqController;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Faq;
use Engelsystem\Models\Tag;
use Engelsystem\Test\Unit\Controllers\ControllerTest;

class FaqControllerTest extends ControllerTest
{
    protected array $data = [
        'question' => 'Foo?',
        'text'     => 'Bar!',
        'tags'     => 'Lorem, Lorem, Ipsum! ,',
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

                $this->assertNotEmpty($data['faq']);

                return $this->response;
            });

        /** @var FaqController $controller */
        $controller = $this->app->make(FaqController::class);

        $controller->edit($this->request);
        $this->assertHasNoNotifications(NotificationType::WARNING);
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

        $this->assertTrue($this->log->hasInfoThatContains('Saved'));

        $faq = (new Faq())->find(2);
        $this->assertEquals('Foo?', $faq->question);
        $this->assertEquals('Bar!', $faq->text);
        $this->assertHasNotification('faq.edit.success');
        $this->assertCount(2, Tag::all());
        $this->assertTrue(Tag::whereName('Ipsum!')->get()->isNotEmpty());
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
            'tags'     => 'Foo, Bar',
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
                $this->assertEquals('Foo, Bar', $data['tags']);
                $this->assertEquals(collect([new Tag(['name' => 'Foo']), new Tag(['name' => 'Bar'])]), $faq->tags);

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
        $this->assertEmpty(Tag::all());
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\FaqController::save
     * @covers \Engelsystem\Controllers\Admin\FaqController::delete
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

        $this->assertHasNotification('faq.delete.success');
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
