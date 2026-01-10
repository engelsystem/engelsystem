<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\ExtendsTokenParser;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use Twig\Environment as Twig;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

#[CoversMethod(ExtendsTokenParser::class, '__construct')]
#[CoversMethod(ExtendsTokenParser::class, 'getTag')]
#[CoversMethod(ExtendsTokenParser::class, 'parse')]
#[CoversMethod(ExtendsTokenParser::class, 'getNextParentFile')]
class ExtendsTokenParserTest extends TestCase
{
    protected ExtendsTokenParser $parser;
    protected Twig $twig;

    public function testGetTag(): void
    {
        $this->assertEquals('extends', $this->parser->getTag());
    }

    public function testParseNormalExtends(): void
    {
        $template = $this->twig->load('extends.twig');
        $output = $template->render();

        $this->assertStringContainsString('Extended template content - Extends Content', $output);
    }

    public function testParseWithInheritance(): void
    {
        $template = $this->twig->load('inheritance.twig');
        $output = $template->render();

        $this->assertStringContainsString('Base template content - B Content - A Content', $output);
    }

    public function testParseExtendsThrowsError(): void
    {
        $this->expectException(SyntaxError::class);

        $this->twig->load('extends-error.twig');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $fsLoader = $this->app->make(FilesystemLoader::class, ['paths' => [
            __DIR__ . '/Stub/Plugin/A/views',
            __DIR__ . '/Stub/Plugin/B/views',
            __DIR__ . '/Stub/views',
            __DIR__ . '/Stub/views/..',
        ]]);
        $this->app->instance(FilesystemLoader::class, $fsLoader);
        $this->app->instance(LoaderInterface::class, $fsLoader);
        $this->parser = $this->app->make(ExtendsTokenParser::class, ['basePath' => __DIR__ . '/Stub']);

        $this->twig = $this->app->make(Twig::class);
        $this->twig->addTokenParser($this->parser);
    }
}
