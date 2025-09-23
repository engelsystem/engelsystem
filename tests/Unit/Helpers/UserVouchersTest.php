<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\UserVouchers;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;
use Engelsystem\Models\Worklog;
use Engelsystem\Test\Unit\Controllers\ControllerTest;

class UserVouchersTest extends ControllerTest
{
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->config->set('enable_voucher', true);
        $this->config->set('voucher_settings', [
            'initial_vouchers'   => 0,
            'shifts_per_voucher' => 0,
            'hours_per_voucher'  => 2,
            'voucher_start'      => null,
        ]);

        $this->user = User::factory()->create();
        $user2 = User::factory()->create();

        // user
        // start more than 3 days ago and ended, 2 hours long
        $shift1 = Shift::factory()->create([
            'start' => Carbon::now()->subDays(3)->subHour(),
            'end' => Carbon::now()->subDays(3)->addHour(),
        ]);
        ShiftEntry::factory()->create([
            'shift_id' => $shift1->id,
            'user_id' => $this->user->id,
            'freeloaded_by' => null,
        ]);

        // started less than 1 day ago and ended, 2 hours long
        $shift2 = Shift::factory()->create([
            'start' => Carbon::now()->subHours(3),
            'end' => Carbon::now()->subHour(),
        ]);
        ShiftEntry::factory()->create([
            'shift_id' => $shift2->id,
            'user_id' => $this->user->id,
            'freeloaded_by' => null,
        ]);
        // entry freeloaded
        ShiftEntry::factory()->create([
            'shift_id' => $shift1->id,
            'user_id' => $this->user->id,
            'freeloaded_by' => $user2->id,
        ]);

        // started less than 1 day ago and ended, 4 hours long
        $shift3 = Shift::factory()->create([
            'start' => Carbon::now()->subHours(5),
            'end' => Carbon::now()->subHour(),
        ]);
        ShiftEntry::factory()->create([
            'shift_id' => $shift3->id,
            'user_id' => $this->user->id,
            'freeloaded_by' => null,
        ]);

        // shifts still running, 2 hours long
        $shift4 = Shift::factory()->create([
            'start' => Carbon::now()->subHour(),
            'end' => Carbon::now()->addHour(),
        ]);
        ShiftEntry::factory()->create([
            'shift_id' => $shift4->id,
            'user_id' => $this->user->id,
            'freeloaded_by' => null,
        ]);

        // worklog 3 days ago, 2 hours long
        Worklog::factory()->create([
            'user_id' => $this->user->id,
            'creator_id' => $user2->id,
            'hours' => 4,
            'worked_at' => Carbon::today()->subDays(3),
        ]);
        // worklog today, 4 hours long
        Worklog::factory()->create([
            'user_id' => $this->user->id,
            'creator_id' => $user2->id,
            'hours' => 4,
            'worked_at' => Carbon::today(),
        ]);
        // worklog tomorrow, 2 hours long
        Worklog::factory()->create([
            'user_id' => $this->user->id,
            'creator_id' => $user2->id,
            'hours' => 4,
            'worked_at' => Carbon::tomorrow(),
        ]);
    }

    /**
     * @return Array<string, array>
     */
    public function provideTestData(): array
    {
        return [
            // settings, userId, got_voucher, expected vouchers
            'initial_vouchers 2, user, got voucher 2' => [['initial_vouchers' => 2], 1, 2, 8],
            'shifts_per_voucher 1, hours_per_voucher 0, voucher_start 2 days ago, user, got voucher 0' => [[
                'shifts_per_voucher' => 1,
                'hours_per_voucher'  => 0,
                'voucher_start'      => Carbon::now()->subDays(2)->format('Y-m-d'),
            ], 1, 0, 3],
            'default settings, user2, got voucher 2' => [null, 2, 2, 0],
        ];
    }

    /**
     * @dataProvider provideTestData
     * @covers \Engelsystem\Helpers\UserVouchers::eligibleVoucherCount
     */
    public function testEligibleVoucherCount(
        array | null $voucherSettings,
        int $userId,
        int $gotVoucher,
        int $expected
    ): void {
        if ($voucherSettings) {
            $this->config->set(
                'voucher_settings',
                array_merge($this->config->get('voucher_settings'), $voucherSettings)
            );
        }

        $user = User::find($userId);
        $user->state->got_voucher = $gotVoucher;
        $user->state->save();

        $this->assertEquals($expected, UserVouchers::eligibleVoucherCount($user));
    }

    /**
     * @covers \Engelsystem\Helpers\UserVouchers::eligibleVoucherCount
     */
    public function testUserVouchersWithVouchersDisabled(): void
    {
        $this->config->set('enable_voucher', false);
        $this->assertEquals(0, UserVouchers::eligibleVoucherCount($this->user));
    }
}
