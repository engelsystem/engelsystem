<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Api;

use Engelsystem\Controllers\Api\Admin\UserVoucherController;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\State;

class UserVoucherControllerTest extends ApiBaseControllerTest {
    public function setUp(): void {
        parent::setUp();
        config(['enable_voucher' => true]);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Admin\UserVoucherController::update
     */
    public function testUpdateUserNotFound(): void {
        $request = new Request(
            request:    ['got_voucher' => 5],
            attributes: ['user_id' => 42]
        );

        $controller = new UserVoucherController(new Response());
        $controller->setValidator(new Validator());
        $response = $controller->update($request);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User not found', $data['message']);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Admin\UserVoucherController::update
     * @covers \Engelsystem\Controllers\Api\Admin\UserVoucherController::checkActive
     */
    public function testUpdateSuccess(): void {
        $user = User::factory()->create();
        State::factory()->create([
                                     'user_id'     => $user->id,
                                     'got_voucher' => 0,
                                 ]);

        $request = new Request(
            request:    ['got_voucher' => 5],
            attributes: ['user_id' => $user->id]
        );

        $controller = new UserVoucherController(new Response());
        $controller->setValidator(new Validator());

        $response = $controller->update($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User state updated successfully', $data['message']);

        $state = State::where('user_id', $user->id)->first();
        $this->assertEquals(5, $state->got_voucher);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Admin\UserVoucherController::increment
     */
    public function testIncrementUserNotFound(): void {
        $request = new Request(
            request:    ['amount' => 1],
            attributes: ['user_id' => 42]
        );

        $controller = new UserVoucherController(new Response());

        $response = $controller->increment($request);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User not found', $data['message']);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Admin\UserVoucherController::increment
     */
    public function testIncrementNegative(): void {
        $user = User::factory()->create();
        State::factory()->create([
                                     'user_id'     => $user->id,
                                     'got_voucher' => 1,
                                 ]);

        $request = new Request(
            request:    ['amount' => -2],
            attributes: ['user_id' => $user->id]
        );

        $controller = new UserVoucherController(new Response());
        $controller->setValidator(new Validator());
        $response = $controller->increment($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User voucher count cannot be negative', $data['message']);
    }

    /**
     * @covers \Engelsystem\Controllers\Api\Admin\UserVoucherController::increment
     * @covers \Engelsystem\Controllers\Api\Admin\UserVoucherController::checkActive
     */
    public function testIncrementSuccess(): void {
        $user = User::factory()->create();
        State::factory()->create([
                                     'user_id'     => $user->id,
                                     'got_voucher' => 2,
                                 ]);

        $request = new Request(
            request:    ['amount' => 3],
            attributes: ['user_id' => $user->id]
        );

        $controller = new UserVoucherController(new Response());
        $controller->setValidator(new Validator());
        $response = $controller->increment($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
        $this->assertJson($response->getContent());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('User voucher count updated', $data['message']);
        $this->assertEquals(5, $data['got_voucher']);
    }
}
