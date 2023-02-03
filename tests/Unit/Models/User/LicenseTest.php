<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Models\User;

use Engelsystem\Models\User\License;
use Engelsystem\Test\Unit\Models\ModelTest;

class LicenseTest extends ModelTest
{
    /**
     * @covers \Engelsystem\Models\User\License::wantsToDrive
     */
    public function testWantsToDrive(): void
    {
        $license = new License();
        $this->assertFalse($license->wantsToDrive());

        $license->has_car = true;
        $this->assertFalse($license->wantsToDrive());

        $license->drive_car = true;
        $this->assertTrue($license->wantsToDrive());

        // True if a user wants to drive anything
        $license = new License(['drive_forklift' => true]);
        $this->assertTrue($license->wantsToDrive());

        $license = new License(['drive_car' => true]);
        $this->assertTrue($license->wantsToDrive());

        $license = new License(['drive_3_5t' => true]);
        $this->assertTrue($license->wantsToDrive());

        $license = new License(['drive_7_5t' => true]);
        $this->assertTrue($license->wantsToDrive());

        $license = new License(['drive_12t' => true]);
        $this->assertTrue($license->wantsToDrive());
    }
}
