<?php

namespace Engelsystem\Test\Unit\Renderer;

use Engelsystem\Renderer\HtmlEngine;
use PHPUnit\Framework\TestCase;

class HtmlEngineTest extends TestCase
{
    /** @var string[] */
    protected $tmpFileNames = [];

    /**
     * @covers \Engelsystem\Renderer\HtmlEngine::get
     */
    public function testGet()
    {
        $engine = new HtmlEngine();

        $file = $this->createTempFile('<div>%main_content%</div>');

        $data = $engine->get($file, ['main_content' => 'Lorem ipsum dolor sit']);
        $this->assertEquals('<div>Lorem ipsum dolor sit</div>', $data);
    }

    /**
     * @covers \Engelsystem\Renderer\HtmlEngine::canRender
     */
    public function testCanRender()
    {
        $engine = new HtmlEngine();

        $this->assertFalse($engine->canRender('/dev/null'));

        $file = $this->createTempFile();
        $this->assertTrue($engine->canRender($file));

        $htmFile = $this->createTempFile('', '.htm');
        $this->assertTrue($engine->canRender($htmFile));
    }

    /**
     * @param string $content
     * @param string $extension
     * @return string
     */
    protected function createTempFile($content = '', $extension = '.html')
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
            unlink($fileName);
        }
    }
}
