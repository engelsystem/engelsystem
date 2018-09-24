<?php

namespace Engelsystem\Test\Unit\Models;

use Carbon\Carbon;
use Engelsystem\Models\EventConfig;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\TestCase;

class EventConfigTest extends TestCase
{
    use HasDatabase;

    /**
     * @covers \Engelsystem\Models\EventConfig::setValueAttribute
     */
    public function testSetValueAttribute()
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
            '"2000-01-01"',
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
    public function testGetValueAttribute()
    {
        $model = new EventConfig(['name', 'buildup_start', 'value' => '']);
        $this->assertEquals('', $model->value);

        (new EventConfig())
            ->setAttribute('name', 'buildup_start')
            ->setAttribute('value', new Carbon('2001-02-03 11:12'))
            ->save();
        $this->assertEquals(
            '2001-02-03 00:00',
            EventConfig::find('buildup_start')
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
    }

    /**
     * @covers \Engelsystem\Models\EventConfig::getValueCast
     */
    public function testGetValueCast()
    {
        $model = new EventConfig(['value' => 'bar']);
        $this->assertEquals('bar', $model->value);

        return;
    }

    /**
     * Init a new EventConfig class
     *
     * @return EventConfig
     */
    protected function getEventConfig()
    {
        return new class extends EventConfig
        {
            /**
             * @param string $value
             * @param string $type
             * @return EventConfig
             */
            public function setValueCast($value, $type)
            {
                $this->valueCasts[$value] = $type;

                return $this;
            }
        };
    }

    /**
     * Prepare test
     */
    protected function setUp()
    {
        $this->initDatabase();
    }
}
