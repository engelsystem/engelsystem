<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers\Schedule;

use Engelsystem\Helpers\Schedule\ConferenceColor;
use Engelsystem\Test\Unit\TestCase;

class ConferenceColorTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Schedule\ConferenceColor::__construct
     * @covers \Engelsystem\Helpers\Schedule\ConferenceColor::getPrimary
     * @covers \Engelsystem\Helpers\Schedule\ConferenceColor::getBackground
     * @covers \Engelsystem\Helpers\Schedule\ConferenceColor::getOthers
     */
    public function testCreateDefaults(): void
    {
        $conferenceColor = new ConferenceColor();

        $this->assertNull($conferenceColor->getPrimary());
        $this->assertNull($conferenceColor->getBackground());
        $this->assertEmpty($conferenceColor->getOthers());
    }

    /**
     * @covers \Engelsystem\Helpers\Schedule\ConferenceColor::__construct
     * @covers \Engelsystem\Helpers\Schedule\ConferenceColor::getPrimary
     * @covers \Engelsystem\Helpers\Schedule\ConferenceColor::getBackground
     * @covers \Engelsystem\Helpers\Schedule\ConferenceColor::getOthers
     */
    public function testCreate(): void
    {
        $conferenceColor = new ConferenceColor(
            '#abcdef',
            '#aabbcc',
            [
                'tertiary' => '#133742',
            ]
        );

        $this->assertEquals('#abcdef', $conferenceColor->getPrimary());
        $this->assertEquals('#aabbcc', $conferenceColor->getBackground());
        $this->assertArrayHasKey('tertiary', $conferenceColor->getOthers());
        $this->assertEquals('#133742', $conferenceColor->getOthers()['tertiary']);
    }
}
