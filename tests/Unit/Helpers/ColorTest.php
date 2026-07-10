<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Color;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversMethod(Color::class, '__construct')]
#[CoversMethod(Color::class, '__toString')]
#[CoversMethod(Color::class, 'isLight')]
#[CoversMethod(Color::class, 'brightness')]
#[CoversMethod(Color::class, 'random')]
class ColorTest extends TestCase
{
    public static function brightnessParams(): array
    {
        return [
            ['#ffffff', 1],
            ['#000000', 0],

            ['#ff0000', .55],
            ['#00ff00', .77],
            ['#0000ff', .34],

            ['#ffff00', .94],
            ['#00ffff', .83],
            ['#ff00ff', .64],

            ['#437aca', .48],
            ['#424242', .26],
            ['#00c960', .62],
            ['#e93a44', .54],
            ['#f6b345', .76],
            ['#3dbddf', .65],
            ['#f8f9fa', .98],
            ['#212529', .14],
            ['#d81389', .50],
            ['#798183', .49],
        ];
    }

    #[DataProvider('brightnessParams')]
    public function testBrightness(string $color, float $expectedBrightness): void
    {
        $c = new Color($color);
        $this->assertEqualsWithDelta($expectedBrightness, $c->brightness(), .01);
    }

    public function testIsLight(): void
    {
        $this->assertFalse((new Color('000'))->isLight());
        $this->assertTrue((new Color('fff'))->isLight());
        $this->assertTrue((new Color('888'))->isLight());
        $this->assertFalse((new Color('424242'))->isLight());
        $this->assertTrue((new Color('d81389'))->isLight());
        $this->assertFalse((new Color('798183'))->isLight());
    }

    public function testToString(): void
    {
        $this->assertEquals('#000000', (string) (new Color('#000')));
        $this->assertEquals('#ffffff', (string) (new Color('#fff')));
        $this->assertEquals('#ffffff', (string) (new Color('fff')));
        $this->assertEquals('#000000', (string) (new Color('')));
        $this->assertEquals('#000000', (string) (new Color('42')));
        $this->assertEquals('#112233', (string) (new Color('#123')));
        $this->assertEquals('#123456', (string) (new Color('#123456')));
        $this->assertEquals('#424242', (string) (new Color('#424242')));
        $this->assertEquals('#424242', (string) (new Color('424242')));
    }

    public function testRandom(): void
    {
        $this->assertInstanceOf(Color::class, Color::random());
    }
}
