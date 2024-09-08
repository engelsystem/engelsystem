<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\FaqController;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Faq;
use Engelsystem\Models\Tag;
use Engelsystem\Test\Unit\HasDatabase;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FaqControllerTest extends TestCase
{
    use HasDatabase;

    protected Config $config;

    /**
     * @covers \Engelsystem\Controllers\FaqController::__construct
     * @covers \Engelsystem\Controllers\FaqController::index
     * @covers \Engelsystem\Controllers\FaqController::showFaqs
     */
    public function testIndex(): void
    {
        $this->createFaq(['question' => 'Xyz', 'text' => 'Abc']);
        $this->createFaq(['question' => 'Something\'s wrong?', 'text' => 'Nah!'], ['SomeTag']);

        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) use ($response) {
                $this->assertEquals('pages/faq/index', $view);
                $this->assertArrayHasKey('text', $data);
                $this->assertEquals('Some Text', $data['text']);
                $this->assertArrayHasKey('tags', $data);
                $this->assertArrayHasKey('items', $data);
                $this->assertCount(2, $data['items']);
                /** @var Faq $firstEntry */
                $firstEntry = $data['items'][0];
                $this->assertEquals('Nah!', $firstEntry->text);
                $this->assertCount(1, $firstEntry->tags);
                $this->assertEquals('SomeTag', $firstEntry->tags[0]->name);

                return $response;
            });

        $controller = new FaqController($this->config, new Faq(), $response);
        $controller->index();
    }

    /**
     * @covers \Engelsystem\Controllers\FaqController::tagged
     */
    public function testTagged(): void
    {
        $this->createFaq(['question' => 'Xyz', 'text' => 'Abc']);
        $faq = $this->createFaq(['question' => 'Something\'s wrong?', 'text' => 'Nah!'], ['SomeTag']);
        $tag = $faq->tags[0];

        $request = (new Request())->withAttribute('tag_id', $tag->id);

        /** @var Response|MockObject $response */
        $response = $this->createMock(Response::class);
        $response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) use ($response) {
                $this->assertEquals('pages/faq/index', $view);
                $this->assertArrayHasKey('text', $data);
                $this->assertArrayHasKey('tag', $data);
                $this->assertInstanceOf(Tag::class, $data['tag']);
                $this->assertArrayHasKey('items', $data);
                $this->assertCount(1, $data['items']);
                $this->assertEquals('Nah!', $data['items'][0]->text);

                return $response;
            });

        $controller = new FaqController($this->config, new Faq(), $response);
        $controller->tagged($request);
    }

    /**
     * @covers \Engelsystem\Controllers\FaqController::tagged
     */
    public function testTaggedNotFound(): void
    {
        $request = (new Request())->withAttribute('tag_id', 42);

        $controller = new FaqController($this->config, new Faq(), new Response());

        $this->expectException(ModelNotFoundException::class);
        $controller->tagged($request);
    }

    /**
     * @covers \Engelsystem\Controllers\FaqController::tagged
     */
    public function testTaggedNoFaqFound(): void
    {
        $tag = Tag::factory()->create();

        $request = (new Request())->withAttribute('tag_id', $tag->id);

        $controller = new FaqController($this->config, new Faq(), new Response());

        $this->expectException(HttpNotFound::class);
        $controller->tagged($request);
    }

    protected function createFaq(array $data, array $tags = []): Faq
    {
        $faq = Faq::factory()->create($data);
        foreach ($tags as $tag) {
            $tag = Tag::whereName($tag)->firstOrCreate(['name' => $tag]);
            $faq->tags()->attach($tag);
        }

        return $faq;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->initDatabase();

        $this->app->instance('session', new Session(new MockArraySessionStorage()));
        $this->config = new Config(['faq_text' => 'Some Text']);
    }
}
