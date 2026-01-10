<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\ShirtSize;
use Engelsystem\Test\Unit\ServiceProviderTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversMethod(ShirtSize::class, '__construct')]
class ShirtSizeTest extends ServiceProviderTestCase
{
    private ShirtSize $subject;

    public function setUp(): void
    {
        $app = $this->createAndSetUpAppWithConfig([]);
        $app->get('config')->set('tshirt_sizes', [
            'S' => 'Small Straight-Cut',
            'M' => 'Medium Straight-Cut',
        ]);
        $this->subject = new ShirtSize();
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function provideTestValidateData(): array
    {
        return [
            'empty string' => ['', false],
            'null' => [null, false],
            '0' => [0, false],
            '"S" (known value)' => ['S', true],
            '"M" (known value)' => ['M', true],
            '"L" (unknown value)' => ['L', false],
        ];
    }

    #[DataProvider('provideTestValidateData')]
    public function testValidate(mixed $value, bool $expectedValid): void
    {
        self::assertSame($expectedValid, $this->subject->validate($value));
    }
}
