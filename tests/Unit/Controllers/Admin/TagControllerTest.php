<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\TagController;
use Engelsystem\Controllers\NotificationType;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\Tag;
use Engelsystem\Test\Unit\Controllers\ControllerTest;

class TagControllerTest extends ControllerTest
{
    /**
     * @covers \Engelsystem\Controllers\Admin\TagController::__construct
     * @covers \Engelsystem\Controllers\Admin\TagController::list
     */
    public function testList(): void
    {
        /** @var TagController $controller */
        $controller = $this->app->make(TagController::class);

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/tag/index.twig', $view);

                $this->assertNotEmpty($data['items']);

                return $this->response;
            });

        $controller->list();
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\TagController::edit
     * @covers \Engelsystem\Controllers\Admin\TagController::showEdit
     */
    public function testEdit(): void
    {
        $this->request->attributes->set('tag_id', 1);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/tag/edit.twig', $view);

                $this->assertNotEmpty($data['tag']);

                return $this->response;
            });

        /** @var TagController $controller */
        $controller = $this->app->make(TagController::class);

        $controller->edit($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\TagController::save
     */
    public function testSaveCreateInvalid(): void
    {
        /** @var TagController $controller */
        $this->expectException(ValidationException::class);

        $controller = $this->app->make(TagController::class);
        $controller->setValidator(new Validator());
        $controller->save($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\TagController::save
     */
    public function testSaveDuplicate(): void
    {
        $body = ['name' => 'Lorem'];

        $this->request = $this->request->withParsedBody($body);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function ($view, $data) {
                $this->assertEquals('pages/tag/edit.twig', $view);

                $this->assertNotEmpty($data['tag']);

                return $this->response;
            });

        /** @var TagController $controller */
        $controller = $this->app->make(TagController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertHasNotification('tag.edit.duplicate', NotificationType::ERROR);
        $this->assertCount(1, Tag::all());
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\TagController::save
     */
    public function testSaveCreateEdit(): void
    {
        $body = ['name' => 'Foo?'];

        $this->request = $this->request->withParsedBody($body);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/admin/tags')
            ->willReturn($this->response);

        /** @var TagController $controller */
        $controller = $this->app->make(TagController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Saved'));

        /** @var Tag $tag */
        $tag = (new Tag())->find(2);
        $this->assertEquals('Foo?', $tag->name);
        $this->assertHasNotification('tag.edit.success');
        $this->assertCount(2, Tag::all());
        $this->assertTrue(Tag::whereName('Lorem')->get()->isNotEmpty());
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\TagController::save
     * @covers \Engelsystem\Controllers\Admin\TagController::delete
     */
    public function testSaveDelete(): void
    {
        $this->request->attributes->set('tag_id', 1);
        $this->request = $this->request->withParsedBody([
            'delete'   => '1',
        ]);
        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/admin/tags')
            ->willReturn($this->response);

        /** @var TagController $controller */
        $controller = $this->app->make(TagController::class);
        $controller->setValidator(new Validator());

        $controller->save($this->request);

        $this->assertTrue($this->log->hasInfoThatContains('Deleted'));

        $this->assertHasNotification('tag.delete.success');
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();

        (new Tag([
            'name' => 'Lorem',
        ]))->save();
    }
}
