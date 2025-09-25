<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\UserVoucherController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Helpers\UserVouchers;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Exceptions\ValidationException;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;

class UserVoucherControllerTest extends ControllerTest
{
    protected Authenticator|MockObject $auth;

    protected Redirector|MockObject $redirect;

    protected User $user;

    protected UserVoucherController $controller;

    /**
     * @covers \Engelsystem\Controllers\Admin\UserVoucherController::editVoucher
     * @covers \Engelsystem\Controllers\Admin\UserVoucherController::checkActive
     */
    public function testVoucherEnabled(): void
    {
        $this->config->set('enable_voucher', false);
        $request = $this->request;
        $this->expectException(HttpNotFound::class);
        $this->controller->editVoucher($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserVoucherController::editVoucher
     */
    public function testShowEditVoucherWithUnknownUserIdThrows(): void
    {
        $request = $this->request->withAttribute('user_id', 1234);
        $this->expectException(ModelNotFoundException::class);
        $this->controller->editVoucher($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserVoucherController::editVoucher
     * @covers \Engelsystem\Controllers\Admin\UserVoucherController::__construct
     *
     * @uses \Engelsystem\Helpers\UserVouchers::eligibleVoucherCount
     */
    public function testShowEditVoucher(): void
    {
        $request = $this->request->withAttribute('user_id', $this->user->id);
        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data) {
                $this->assertEquals('admin/user/edit-voucher.twig', $view);
                $this->assertEquals($this->user->id, $data['userdata']->id);
                $this->assertEquals($this->user->state->got_voucher, $data['gotVoucher']);
                $this->assertEquals(
                    $this->user->state->force_active && config('enable_force_active'),
                    $data['forceActive']
                );
                $this->assertEquals(
                    $this->user->state->force_food && config('enable_force_food'),
                    $data['forceFood']
                );
                $this->assertEquals(UserVouchers::eligibleVoucherCount($this->user), $data['eligibleVoucherCount']);
                return $this->response;
            });
        $this->controller->editVoucher($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserVoucherController::saveVoucher
     */
    public function testSaveVoucherWithUnknownUserIdThrows(): void
    {
        $request = $this->request->withAttribute('user_id', 1234)->withParsedBody([]);
        $this->expectException(ModelNotFoundException::class);
        $this->controller->saveVoucher($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserVoucherController::saveVoucher
     *
     * @dataProvider invalidSaveVoucherParams
     */
    public function testSaveVoucherWithInvalidParamsThrows(array $body): void
    {
        $request = $this->request->withAttribute('user_id', $this->user->id)->withParsedBody($body);
        $this->expectException(ValidationException::class);
        $this->controller->saveVoucher($request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserVoucherController::saveVoucher
     */
    public function testSaveVoucher(): void
    {
        $got_voucher = 4;
        $body = ['got_voucher' => $got_voucher];
        $request = $this->request->withAttribute('user_id', $this->user->id)->withParsedBody($body);
        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());
        $this->redirect->expects($this->once())
            ->method('to')
            ->with('/users?action=view&user_id=' . $this->user->id)
            ->willReturn($this->response);

        $this->controller->saveVoucher($request);

        $this->assertHasNotification('voucher.save.success');
        $this->assertTrue($this->log->hasInfoThatContains('vouchers.'));

        $this->assertEquals(4, $this->user->state->got_voucher);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserVoucherController::saveVoucher
     */
    public function testSaveVoucherJsonResponce(): void
    {
        $got_voucher = 4;
        $body = ['got_voucher' => $got_voucher];
        $response_body = [
            'issued' => $got_voucher,
            'eligible' => $got_voucher + UserVouchers::eligibleVoucherCount($this->user),
            'total' => 4,
        ];
        $request = $this->request
            ->withAttribute('user_id', $this->user->id)
            ->withParsedBody($body)
            ->withHeader('accept', 'application/json');

        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());
        $this->setExpects($this->response, 'withHeader', ['content-type', 'application/json'], $this->response);
        $this->setExpects($this->response, 'withContent', [json_encode($response_body)], $this->response);

        $this->controller->saveVoucher($request);
    }

    /**
     * @return array[]
     */
    public function invalidSaveVoucherParams(): array
    {
        return [
            // missing got_voucher
            [[]],
            // got_voucher not int
            [['got_voucher' => 3.14]],
        ];
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->config->set('enable_voucher', true);
        $this->config->set('voucher_settings', [
        'initial_vouchers'   => 0,
        'shifts_per_voucher' => 0,
        'hours_per_voucher'  => 2,
        // 'Y-m-d' formatted
        'voucher_start'      => null,
        ]);

        $this->app->bind('http.urlGenerator', UrlGenerator::class);

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->redirect = $this->createMock(Redirector::class);
        $this->app->instance(Redirector::class, $this->redirect);

        $this->user = User::factory()->create();
        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());

        $this->controller = $this->app->make(UserVoucherController::class);
        $this->controller->setValidator(new Validator());
    }
}
