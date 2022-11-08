<?php

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\AngelType;

class AngelTypeTest extends ModelTest
{
    /**
     * @return array<array{boolean, string, string, string}>
     */
    public function hasContactInfoDataProvider(): array
    {
        return [
            [false, '', '', ''],
            [true, 'Foo', '', ''],
            [true, '', 'BAR', ''],
            [true, '', '', 'baz@localhost'],
            [true, 'Foo', 'BAR', 'baz@localhost'],
        ];
    }

    /**
     * @covers       \Engelsystem\Models\AngelType::hasContactInfo
     * @dataProvider hasContactInfoDataProvider
     */
    public function testHasContactInfo(bool $expected, ?string $name, ?string $dect, ?string $email): void
    {
        $model = new AngelType([
            'contact_name'  => $name,
            'contact_dect'  => $dect,
            'contact_email' => $email,
        ]);

        $this->assertEquals($expected, $model->hasContactInfo());
    }

    /**
     * @covers       \Engelsystem\Models\AngelType::boot
     */
    public function testBoot(): void
    {
        AngelType::factory()->create(['name' => 'foo']);
        AngelType::factory()->create(['name' => 'bar']);
        AngelType::factory()->create(['name' => 'baz']);
        AngelType::factory()->create(['name' => 'lorem']);
        AngelType::factory()->create(['name' => 'ipsum']);

        $this->assertEquals(
            ['bar', 'baz', 'foo', 'ipsum', 'lorem'],
            AngelType::all()->map(fn(AngelType $angelType) => $angelType->toArray())->pluck('name')->toArray()
        );
    }
}
