<?php

namespace Engelsystem\Test\Unit\Models;

use Carbon\Carbon;
use Engelsystem\Models\EventConfig;

class EventConfigTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\EventConfig::setValueAttribute
     */
    public function testSetValueAttribute(): void
    {
        (new EventConfig())
            ->setAttribute('name', 'foo')
            ->setAttribute('value', 'bar')
            ->save();
        $this->assertEquals(
            '"bar"',
            $this->database
                ->selectOne("SELECT `value` FROM event_config WHERE name='foo'")
                ->value
        );

        (new EventConfig())
            ->setAttribute('name', 'buildup_start')
            ->setAttribute('value', new Carbon('2000-01-01 10:20'))
            ->save();
        $this->assertEquals(
            '"2000-01-01 10:20"',
            $this->database
                ->selectOne("SELECT `value` FROM event_config WHERE name='buildup_start'")
                ->value
        );

        ($this->getEventConfig())
            ->setAttribute('name', 'event_start')
            ->setValueCast('event_start', 'datetime')
            ->setAttribute('value', new Carbon('2010-11-11 20:22'))
            ->save();
        $this->assertEquals(
            '"' . (new Carbon('2010-11-11 20:22'))->format(Carbon::ATOM) . '"',
            $this->database
                ->selectOne("SELECT `value` FROM event_config WHERE name='event_start'")
                ->value
        );
    }

    /**
     * @covers \Engelsystem\Models\EventConfig::getValueAttribute
     */
    public function testGetValueAttribute(): void
    {
        $model = new EventConfig(['name', 'buildup_start', 'value' => '']);
        $this->assertEquals('', $model->value);

        (new EventConfig())
            ->setAttribute('name', 'buildup_start')
            ->setAttribute('value', new Carbon('2001-02-03 11:12'))
            ->save();
        $this->assertEquals(
            '2001-02-03 11:12',
            (new EventConfig())->find('buildup_start')
                ->value
                ->format('Y-m-d H:i')
        );

        ($this->getEventConfig())
            ->setAttribute('name', 'event_start')
            ->setValueCast('event_start', 'datetime')
            ->setAttribute('value', new Carbon('2010-11-11 20:22'))
            ->save();
        $this->assertEquals(
            '2010-11-11 20:22',
            ($this->getEventConfig())->find('event_start')
                ->setValueCast('event_start', 'datetime')
                ->value
                ->format('Y-m-d H:i')
        );
        $this->assertEquals(
            null,
            ($this->getEventConfig())->getValueAttribute(null)
        );
    }

    /**
     * @covers \Engelsystem\Models\EventConfig::getValueCast
     */
    public function testGetValueCast(): void
    {
        $model = new EventConfig(['name' => 'foo', 'value' => 'bar']);
        $this->assertEquals('bar', $model->value);

        return;
    }

    /**
     * Init a new EventConfig class
     */
    protected function getEventConfig(): EventConfig
    {
        return new class extends EventConfig
        {
            public function setValueCast(string $value, string $type): EventConfig
            {
                $this->valueCasts[$value] = $type;

                return $this;
            }
        };
    }
}
