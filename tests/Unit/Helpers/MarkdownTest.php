<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Markdown;
use Engelsystem\Test\Unit\TestCase;

class MarkdownTest extends TestCase
{
    public function mapping(): array
    {
        return [
            // $text, $expected, $withAllowHtml
            ['# test', '<h1>test</h1>', '<h1>test</h1>'],
            ['<b>bold</b>', '<p>&lt;b&gt;bold&lt;/b&gt;</p>', '<p><b>bold</b></p>'],
            [
                '* test',
                '<ul>' . PHP_EOL . '<li>test</li>' . PHP_EOL . '</ul>',
                '<ul>' . PHP_EOL . '<li>test</li>' . PHP_EOL . '</ul>',
            ],
            ['<div></div>', '&lt;div&gt;&lt;/div&gt;', '<div></div>'],
            [
                '[Test](https://example.com)',
                '<p><a href="https://example.com">Test</a></p>',
                '<p><a href="https://example.com">Test</a></p>',
            ],
            ['[Test](javascript:alert("hi"))', '<p><a>Test</a></p>', '<p><a>Test</a></p>'],
            [
                '<script>alert("ho")</script>',
                '&lt;script&gt;alert("ho")&lt;/script&gt;',
                '&lt;script>alert("ho")&lt;/script>', // Looks kind of broken but thats fine
            ],
            [
                'https://example.com/link',
                '<p><a href="https://example.com/link">https://example.com/link</a></p>',
                '<p><a href="https://example.com/link">https://example.com/link</a></p>',
            ],
        ];
    }

    /**
     * @covers       \Engelsystem\Helpers\Markdown::render
     * @covers       \Engelsystem\Helpers\Markdown::getRenderer
     * @dataProvider mapping
     */
    public function testRender(string $text, string $expected): void
    {
        $uuid = new Markdown();
        $result = $uuid->render($text);

        $this->assertEquals($expected, $result);
    }

    /**
     * @covers       \Engelsystem\Helpers\Markdown::render
     * @covers       \Engelsystem\Helpers\Markdown::getRenderer
     * @dataProvider mapping
     */
    public function testRenderRaw(string $text, string $expected, string $withAllowHtml): void
    {
        $uuid = new Markdown();
        $result = $uuid->render($text, true);

        $this->assertEquals($withAllowHtml, $result);
    }

    /**
     * @covers \Engelsystem\Helpers\Markdown::render
     * @covers \Engelsystem\Helpers\Markdown::getRenderer
     */
    public function testRenderTable(): void
    {
        $text = '| test | value |' . PHP_EOL . '| --- | :--- |' . PHP_EOL . '| row | content |';

        $uuid = new Markdown();
        $result = $uuid->render($text, true);

        $this->assertStringNotContainsString('|', $result);
        $this->assertStringNotContainsString('---', $result);
        $this->assertStringContainsString('</table>', $result);
        $this->assertStringContainsString('table-striped', $result);
        $this->assertStringContainsString('<th>test</th>', $result);
        $this->assertStringContainsString('text-start', $result);
        $this->assertStringContainsString('content', $result);
    }
}
