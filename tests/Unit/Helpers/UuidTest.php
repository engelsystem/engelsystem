<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use InvalidArgumentException;
use Engelsystem\Helpers\Uuid;
use Engelsystem\Test\Unit\TestCase;
use Illuminate\Support\Str;

class UuidTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Uuid::uuid
     */
    public function testUuid(): void
    {
        $uuid = new Uuid();
        $result = $uuid->uuid();

        $this->checkUuid4Format($result);
    }

    public function generateUuidBy(): array
    {
        return [
            [42, 'a1d0c6e8-3f02-4327-9846-1063f4ac58a6'],
            ['42', 'a1d0c6e8-3f02-4327-9846-1063f4ac58a6'],
            [1.23, '579c4c7f-58e4-45e8-8cfc-73e08903a08c'],
            ['1.23', '579c4c7f-58e4-45e8-8cfc-73e08903a08c'],
            ['test', '098f6bcd-4621-4373-8ade-4e832627b4f6'],
        ];
    }

    /**
     * @covers       \Engelsystem\Helpers\Uuid::uuidBy
     * @dataProvider generateUuidBy
     */
    public function testUuidBy(mixed $value, string $expected): void
    {
        $uuid = new Uuid();
        $result = $uuid->uuidBy($value);

        $this->checkUuid4Format($result);
        $this->assertEquals($expected, $result);
    }

    public function generateUuidByNumbers(): array
    {
        $numbers = [];
        foreach (range(0, 10) as $number) {
            $numbers[] = [$number];
        }

        return $numbers;
    }

    /**
     * @covers       \Engelsystem\Helpers\Uuid::uuidBy
     * @dataProvider generateUuidByNumbers
     */
    public function testUuidByNumbers(mixed $value): void
    {
        $uuid = new Uuid();
        $result = $uuid->uuidBy($value);

        $this->checkUuid4Format($result);
    }

    public function generateUuidByNamed(): array
    {
        return [
            ['42', 42, '42a1d0c6-e83f-4273-a7d8-461063f4ac58'],
            ['123', 1.23, '123579c4-c7f5-4e48-9e8c-cfc73e08903a'],
            ['7e57', 'test', '7e57098f-6bcd-4621-9373-cade4e832627'],
            ['5c4ed01eda7a1490751', 'schedule data', '5c4ed01e-da7a-4490-b514-33ffb998ffe8'],
            ['00001000010000100001', '20 characters', '00001000-0100-4010-8001-3e8c8aa31133'],
            ['ABC0DEF', 'lowercase', 'abc0deff-8241-4ecc-87fb-74bf40ccfe96'],
            ['0123456789000abc0def', 'change nothing', '01234567-8900-4abc-8def-4a28d7089c93'],
        ];
    }

    /**
     * @covers       \Engelsystem\Helpers\Uuid::uuidBy
     * @dataProvider generateUuidByNamed
     */
    public function testUuidByNamed(string $name, mixed $value, string $expected): void
    {
        $uuid = new Uuid();
        $result = $uuid->uuidBy($value, $name);

        $this->checkUuid4Format($result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers \Engelsystem\Helpers\Uuid::uuidBy
     */
    public function testUuidByNamedTooLong(): void
    {
        $uuid = new Uuid();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/\$name.+20.+/');
        $uuid->uuidBy('', '111111111111111111111');
    }

    /**
     * @covers \Engelsystem\Helpers\Uuid::uuidBy
     */
    public function testUuidByNamedNotHex(): void
    {
        $uuid = new Uuid();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/\$name.+hex.+/');
        $uuid->uuidBy('', 'not a hex name');
    }

    protected function checkUuid4Format(string $uuid): void
    {
        $version = Str::substr($uuid, 14, 1);
        $variant = Str::substr($uuid, 19, 1);

        $this->assertTrue(Str::isUuid($uuid), 'Is a UUID');
        $this->assertEquals(4, $version, 'Version');
        $this->assertStringStartsWith(
            '10',
            sprintf('%04b', hexdec($variant)),
            'Variant is 0x8-0xb (RFC 4122, DCE 1.1,  ISO/IEC 11578:1996)'
        );
    }
}
