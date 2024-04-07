<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Controllers\Admin;

use Engelsystem\Controllers\Admin\UserSettingsController;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Http\Validation\Validator;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\User;
use Engelsystem\Test\Unit\Controllers\ControllerTest;
use Engelsystem\Test\Unit\HasDatabase;
use PHPUnit\Framework\MockObject\MockObject;

class UserSettingsControllerTest extends ControllerTest
{
    use HasDatabase;

    protected Authenticator | MockObject $auth;

    protected User $user;

    protected User $userChanged;

    protected UserSettingsController $controller;

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::certificate
     */
    public function testCertificateDisabled(): void
    {
        config(['ifsg_enabled' => false]);

        $this->expectException(HttpNotFound::class);
        $this->controller->certificate($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::certificate
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::isIfsgSupporter
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::isDriverLicenseSupporter
     */
    public function testCertificateNotAllowed(): void
    {
        config(['ifsg_enabled' => true, 'driving_license_enabled' => true]);

        $this->expectException(HttpForbidden::class);
        $this->controller->certificate($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::__construct
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::certificate
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::checkPermission
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::getUser
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::view
     */
    public function testCertificateByPermission(): void
    {
        config(['ifsg_enabled' => true]);
        $this->setExpects($this->auth, 'canAny', [['user.ifsg.edit', 'user.drive.edit']], true, $this->atLeastOnce());

        $this->response->expects($this->once())
            ->method('withView')
            ->willReturnCallback(function (string $view, array $data): Response {
                $this->assertArrayHasKey('certificates', $data);
                $this->assertArrayHasKey('settings_menu', $data);
                $this->assertArrayHasKey('is_admin', $data);
                $this->assertTrue($data['is_admin']);
                $this->assertArrayHasKey('admin_user', $data);
                $this->assertEquals($this->userChanged->id, $data['admin_user']->id);
                return $this->response;
            });

        $this->controller->certificate($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::certificate
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::checkPermission
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::isIfsgSupporter
     */
    public function testCertificateByAngelTypeSupporter(): void
    {
        config(['ifsg_enabled' => true]);
        $this->setExpects($this->response, 'withView', null, $this->response);

        $angelType = AngelType::factory()->create(['requires_ifsg_certificate' => true]);
        $this->user->userAngelTypes()->attach($angelType, ['supporter' => true]);

        $this->controller->certificate($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::certificate
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::checkPermission
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::isDriverLicenseSupporter
     */
    public function testDriverLicenseByAngelTypeSupporter(): void
    {
        config(['driving_license_enabled' => true]);
        $this->setExpects($this->response, 'withView', null, $this->response);

        $angelType = AngelType::factory()->create(['requires_driver_license' => true]);
        $this->user->userAngelTypes()->attach($angelType, ['supporter' => true]);

        $this->controller->certificate($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::saveIfsgCertificate
     */
    public function testSaveIfsgCertificateDisabled(): void
    {
        config(['ifsg_enabled' => false]);

        $this->expectException(HttpNotFound::class);
        $this->controller->saveIfsgCertificate($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::saveIfsgCertificate
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::checkPermission
     */
    public function testSaveIfsgCertificateNotAllowed(): void
    {
        config(['ifsg_enabled' => true]);

        $this->expectException(HttpForbidden::class);
        $this->controller->saveIfsgCertificate($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::saveIfsgCertificate
     */
    public function testSaveIfsgCertificateConfirmed(): void
    {
        config(['ifsg_enabled' => true]);
        $this->setExpects($this->auth, 'can', ['user.ifsg.edit'], true, $this->atLeastOnce());

        $body = [
            'ifsg_certificate' => true,
            'ifsg_confirmed' => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/users/' . $this->userChanged->id . '/certificates')
            ->willReturn($this->response);

        $this->controller->saveIfsgCertificate($this->request);
        $this->assertTrue($this->log->hasInfoThatContains('Certificate'));

        $this->assertFalse($this->userChanged->license->ifsg_certificate_light);
        $this->assertTrue($this->userChanged->license->ifsg_certificate);
        $this->assertTrue($this->userChanged->license->ifsg_confirmed);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::saveIfsgCertificate
     */
    public function testSaveIfsgCertificate(): void
    {
        config(['ifsg_enabled' => true]);
        $this->setExpects($this->auth, 'can', ['user.ifsg.edit'], true, $this->atLeastOnce());

        $body = [
            'ifsg_certificate' => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/users/' . $this->userChanged->id . '/certificates')
            ->willReturn($this->response);

        $this->controller->saveIfsgCertificate($this->request);

        $this->assertFalse($this->userChanged->license->ifsg_certificate_light);
        $this->assertTrue($this->userChanged->license->ifsg_certificate);
        $this->assertFalse($this->userChanged->license->ifsg_confirmed);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::saveIfsgCertificate
     */
    public function testSaveIfsgCertificateLite(): void
    {
        config(['ifsg_enabled' => true, 'ifsg_light_enabled' => true]);
        $this->setExpects($this->auth, 'can', ['user.ifsg.edit'], true, $this->atLeastOnce());

        $body = [
            'ifsg_certificate_light' => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/users/' . $this->userChanged->id . '/certificates')
            ->willReturn($this->response);

        $this->controller->saveIfsgCertificate($this->request);

        $this->assertTrue($this->userChanged->license->ifsg_certificate_light);
        $this->assertFalse($this->userChanged->license->ifsg_certificate);
        $this->assertFalse($this->userChanged->license->ifsg_confirmed);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::saveDrivingLicense
     */
    public function testSaveDrivingLicenseDisabled(): void
    {
        config(['driving_license_enabled' => false]);

        $this->expectException(HttpNotFound::class);
        $this->controller->saveDrivingLicense($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::saveDrivingLicense
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::checkPermission
     */
    public function testSaveDrivingLicenseNotAllowed(): void
    {
        config(['driving_license_enabled' => true]);

        $this->expectException(HttpForbidden::class);
        $this->controller->saveDrivingLicense($this->request);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::saveDrivingLicense
     */
    public function testSaveDrivingLicenseConfirmed(): void
    {
        config(['driving_license_enabled' => true]);
        $this->setExpects($this->auth, 'can', ['user.drive.edit'], true, $this->atLeastOnce());

        $body = [
            'drive_car' => true,
            'drive_3_5t' => true,
            'drive_confirmed' => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/users/' . $this->userChanged->id . '/certificates')
            ->willReturn($this->response);

        $this->controller->saveDrivingLicense($this->request);
        $this->assertTrue($this->log->hasInfoThatContains('Certificate'));

        $this->assertFalse($this->userChanged->license->drive_forklift);
        $this->assertFalse($this->userChanged->license->drive_12t);
        $this->assertFalse($this->userChanged->license->drive_7_5t);
        $this->assertFalse($this->userChanged->license->has_car);
        $this->assertTrue($this->userChanged->license->drive_car);
        $this->assertTrue($this->userChanged->license->drive_3_5t);
        $this->assertTrue($this->userChanged->license->drive_confirmed);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::saveDrivingLicense
     */
    public function testSaveDrivingLicense(): void
    {
        config(['driving_license_enabled' => true]);
        $this->setExpects($this->auth, 'can', ['user.drive.edit'], true, $this->atLeastOnce());

        $body = [
            'drive_forklift' => true,
            'drive_12t' => true,
        ];
        $this->request = $this->request->withParsedBody($body);

        $this->response->expects($this->once())
            ->method('redirectTo')
            ->with('http://localhost/users/' . $this->userChanged->id . '/certificates')
            ->willReturn($this->response);

        $this->controller->saveDrivingLicense($this->request);

        $this->assertFalse($this->userChanged->license->drive_3_5t);
        $this->assertFalse($this->userChanged->license->drive_7_5t);
        $this->assertFalse($this->userChanged->license->drive_car);
        $this->assertFalse($this->userChanged->license->has_car);
        $this->assertTrue($this->userChanged->license->drive_forklift);
        $this->assertTrue($this->userChanged->license->drive_12t);
        $this->assertFalse($this->userChanged->license->drive_confirmed);
    }

    /**
     * @covers \Engelsystem\Controllers\Admin\UserSettingsController::settingsMenu
     */
    public function testSettingsMenu(): void
    {
        $menu = $this->controller->settingsMenu($this->userChanged);
        $this->assertArrayHasKey('http://localhost/users?action=view&user_id=' . $this->userChanged->id, $menu);

        config(['ifsg_enabled' => true]);
        $menu = $this->controller->settingsMenu($this->userChanged);
        $this->assertArrayHasKey('http://localhost/users/' . $this->userChanged->id . '/certificates', $menu);
    }

    /**
     * Setup environment
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->app->bind('http.urlGenerator', UrlGenerator::class);

        $this->user = User::factory()->create();

        $this->userChanged = User::factory()
            ->has(License::factory())
            ->create();

        $this->auth = $this->createMock(Authenticator::class);
        $this->app->instance(Authenticator::class, $this->auth);

        $this->setExpects($this->auth, 'user', null, $this->user, $this->any());

        $this->request = $this->request->withAttribute('user_id', $this->userChanged->id);

        $this->controller = $this->app->make(UserSettingsController::class);
        $this->controller->setValidator(new Validator());
    }
}
