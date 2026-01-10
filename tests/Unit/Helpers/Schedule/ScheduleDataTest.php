<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\ScheduleData;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(ScheduleData::class, 'patch')]
class ScheduleDataTest extends TestCase
{
    public function testPatch(): void
    {
        $instance = new class ('value') extends ScheduleData {
            public function __construct(
                protected string $key
            ) {
            }

            public function getKey(): string
            {
                return $this->key;
            }
        };

        $this->assertEquals('value', $instance->getKey());

        $instance->patch('key', 'new');
        $this->assertEquals('new', $instance->getKey());
    }
}
