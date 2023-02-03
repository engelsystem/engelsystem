<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\NeededAngelType;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;

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
     * @covers \Engelsystem\Models\AngelType::userAngelTypes
     */
    public function testUserAngelTypes(): void
    {
        User::factory(2)->create();
        $user1 = User::factory()->create();
        User::factory(1)->create();
        $user2 = User::factory()->create();

        $angelType = AngelType::create(['name' => 'Test']);

        $angelType->userAngelTypes()->attach($user1);
        $angelType->userAngelTypes()->attach($user2);

        /** @var UserAngelType $userAngelType */
        $userAngelType = UserAngelType::find(1);
        $this->assertEquals($angelType->id, $userAngelType->angelType->id);

        $angeltypes = $angelType->userAngelTypes;
        $this->assertCount(2, $angeltypes);
    }

    /**
     * @covers \Engelsystem\Models\AngelType::shiftEntries
     */
    public function testShiftEntries(): void
    {
        $angelType = AngelType::create(['name' => 'test type']);

        ShiftEntry::factory(3)->create(['angel_type_id' => $angelType->id]);

        $angelType = AngelType::find(1);
        $this->assertCount(3, $angelType->shiftEntries);
    }

    /**
     * @covers \Engelsystem\Models\AngelType::neededBy
     */
    public function testNeededBy(): void
    {
        $angelType = AngelType::create(['name' => 'test type']);

        $this->assertCount(0, $angelType->neededBy);

        NeededAngelType::factory(4)->create(['angel_type_id' => $angelType->id]);

        $angelType = AngelType::find(1);
        $this->assertCount(4, $angelType->neededBy);
    }

    /**
     * @covers \Engelsystem\Models\AngelType::boot
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
