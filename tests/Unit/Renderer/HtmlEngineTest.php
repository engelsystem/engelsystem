<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\HtmlEngine;
use PHPUnit\Framework\TestCase;

class HtmlEngineTest extends TestCase
{
    /** @var string[] */
    protected array $tmpFileNames = [];

    /**
     * @covers \Engelsystem\Renderer\HtmlEngine::get
     */
    public function testGet(): void
    {
        $engine = new HtmlEngine();
        $engine->share('shared_data', 'tester');

        $file = $this->createTempFile('<div>%main_content% is a %shared_data%</div>');

        $data = $engine->get($file, ['main_content' => 'Lorem ipsum dolor sit']);
        $this->assertEquals('<div>Lorem ipsum dolor sit is a tester</div>', $data);
    }

    /**
     * @covers \Engelsystem\Renderer\HtmlEngine::canRender
     */
    public function testCanRender(): void
    {
        $engine = new HtmlEngine();

        $this->assertFalse($engine->canRender('/dev/null'));

        $file = $this->createTempFile();
        $this->assertTrue($engine->canRender($file));

        $htmFile = $this->createTempFile('', '.htm');
        $this->assertTrue($engine->canRender($htmFile));
    }

    protected function createTempFile(string $content = '', string $extension = '.html'): string
    {
        $tmpFileName = tempnam(sys_get_temp_dir(), 'EngelsystemUnitTest');

        $fileName = $tmpFileName . $extension;
        rename($tmpFileName, $fileName);

        file_put_contents($fileName, $content);

        $this->tmpFileNames[] = $fileName;

        return $fileName;
    }

    /**
     * Remove files
     */
    protected function tearDown(): void
    {
        foreach ($this->tmpFileNames as $fileName) {
            file_exists($fileName) && unlink($fileName);
        }
    }
}
