<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models;

use Carbon\Carbon;
use Engelsystem\Helpers\CarbonDay;
use Engelsystem\Models\EventConfig;

class EventConfigTest extends ModelTest
{
    /**
     * @covers       \Engelsystem\Models\EventConfig::casts
     */
    public function testCast(): void
    {
        $casts = (new EventConfig())->casts();
        $this->assertEquals(['value' => 'array'], $casts);
    }

    public function dataCasts(): array
    {
        return [
            ['bar', '"bar"'],
            [new Carbon('2000-01-01 10:20'), '"2000-01-01T10:20:00.000000Z"', '2000-01-01T10:20:00.000000Z'],
            [
                new Carbon('2042-01-01T13:00:00.000001+23:42'),
                '"2041-12-31T13:18:00.000001Z"',
                '2041-12-31T13:18:00.000001Z',
            ],
            [new CarbonDay('2000-01-01'), '"2000-01-01"', '2000-01-01'],
            [false, 'false'],
            [['test'], '["test"]'],
            [['some' => 'test'], '{"some":"test"}'],
        ];
    }

    /**
     * @covers       \Engelsystem\Models\EventConfig::casts
     * @dataProvider dataCasts
     */
    public function testCastCasting(mixed $setValue, mixed $expectedValueDb, mixed $expectedValueResult = null): void
    {
        (new EventConfig())
            ->setAttribute('name', 'config_name')
            ->setAttribute('value', $setValue)
            ->save();
        $this->assertEquals(
            $expectedValueDb,
            $this->database
                ->selectOne("SELECT `value` FROM event_config WHERE name='config_name'")
                ->value
        );
        $this->assertEquals($expectedValueResult ?? $setValue, EventConfig::find('config_name')->value);
    }
}
