<?php

declare(strict_types=1);

namespace Engelsystem\Test\Feature\Controllers;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\RegistrationController;
use Engelsystem\Events\Listener\OAuth2;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Test\Feature\ApplicationFeatureTest;
use Engelsystem\Test\Utils\FormFieldAssert;
use Engelsystem\Test\Utils\SignUpConfig;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @group registration-controller-tests
 * @covers \Engelsystem\Controllers\RegistrationController
 */
final class RegistrationControllerTest extends ApplicationFeatureTest
{
    private Config $config;
    private SessionInterface $session;
    /**
     * @var OAuth2&MockObject
     */
    private OAuth2 $oauth;
    /**
     * @var Array<BaseModel>
     */
    private array $modelsToBeDeleted;
    private RegistrationController $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->modelsToBeDeleted = [];
        $app = app();
        $this->oauth = $this->getMockBuilder(OAuth2::class)
            ->disableOriginalConstructor()
            ->getMock();
        $app->instance(OAuth2::class, $this->oauth);
        $this->config = $app->get(Config::class);
        $this->session = $app->get(SessionInterface::class);
        $this->subject = $app->make(RegistrationController::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->deleteModels();
    }

    /**
     * Renders the registration page with a minimum fields config.
     * Asserts that the basic fields are there while the other fields are not there.
     *
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewMinimumConfig(): void
    {
        SignUpConfig::setMinimumConfig($this->config);
        $response = $this->subject->view();

        $this->assertSame(200, $response->getStatusCode());
        $responseHTML = $response->getBody()->__toString();

        // assert the expected fields are there
        FormFieldAssert::assertContainsInputField('username', $responseHTML);
        FormFieldAssert::assertContainsInputField('password', $responseHTML);
        FormFieldAssert::assertContainsInputField('password_confirmation', $responseHTML);
        FormFieldAssert::assertContainsInputField('email', $responseHTML);
        FormFieldAssert::assertContainsInputField('mobile', $responseHTML);

        // assert the disabled fields are not there
        FormFieldAssert::assertNotContainsInputField('pronoun', $responseHTML);
        FormFieldAssert::assertNotContainsInputField('firstname', $responseHTML);
        FormFieldAssert::assertNotContainsInputField('lastname', $responseHTML);
        FormFieldAssert::assertNotContainsInputField('email_goodie', $responseHTML);
        FormFieldAssert::assertNotContainsSelectField('tshirt_size', $responseHTML);
        FormFieldAssert::assertNotContainsInputField('planned_arrival_date', $responseHTML);
        FormFieldAssert::assertNotContainsInputField('mobile_show', $responseHTML);
        FormFieldAssert::assertNotContainsInputField('dect', $responseHTML);
    }

    /**
     * Renders the registration page with a maximum fields config.
     * Asserts that all fields are there.
     *
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewMaximumConfig(): void
    {
        SignUpConfig::setMaximumConfig($this->config);
        $response = $this->subject->view();

        $this->assertSame(200, $response->getStatusCode());
        $responseHTML = $response->getBody()->__toString();

        // assert the expected fields are there
        FormFieldAssert::assertContainsInputField('pronoun', $responseHTML);
        FormFieldAssert::assertContainsInputField('username', $responseHTML);
        FormFieldAssert::assertContainsInputField('email', $responseHTML);
        FormFieldAssert::assertContainsInputField('mobile', $responseHTML);
        FormFieldAssert::assertContainsInputField('password', $responseHTML);
        FormFieldAssert::assertContainsInputField('password_confirmation', $responseHTML);
        FormFieldAssert::assertContainsInputField('firstname', $responseHTML);
        FormFieldAssert::assertContainsInputField('lastname', $responseHTML);
        FormFieldAssert::assertContainsInputField('email_goodie', $responseHTML);
        FormFieldAssert::assertContainsSelectField('tshirt_size', $responseHTML);
        FormFieldAssert::assertContainsInputField('planned_arrival_date', $responseHTML);
        FormFieldAssert::assertContainsInputField('mobile_show', $responseHTML);
        FormFieldAssert::assertContainsInputField('dect', $responseHTML);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewAngelTypesOAuthPreselection(): void
    {
        SignUpConfig::setMinimumConfig($this->config);
        $angelTypes = $this->createAngelTypes();
        $this->session->set('oauth2_connect_provider', 'test_oauth_provider');
        $this->session->set('oauth2_groups', [$angelTypes[1]->name]);
        $this->oauth
            ->method('getSsoTeams')
            ->with('test_oauth_provider')
            ->willReturn(
                [
                    $angelTypes[1]->name => ['id' => $angelTypes[1]->id],
                    $angelTypes[2]->name => ['id' => $angelTypes[2]->id],
                ],
            );

        $response = $this->subject->view();

        $this->assertSame(200, $response->getStatusCode());
        $responseHTML = $response->getBody()->__toString();

        // assert that the unrestricted angel type is there and checked
        FormFieldAssert::assertContainsCheckedCheckbox('angel_types_' . $angelTypes[0]->id, $responseHTML);

        // assert that the first restricted angel type from oauth is there and checked
        FormFieldAssert::assertContainsCheckedCheckbox('angel_types_' . $angelTypes[1]->id, $responseHTML);

        // assert that the second restricted angel type not in oauth is there and not checked
        FormFieldAssert::assertContainsUncheckedCheckbox('angel_types_' . $angelTypes[2]->id, $responseHTML);

        // assert that the angel type with "hide_register" = true is not there
        FormFieldAssert::assertNotContainsInputField('angel_types_' . $angelTypes[3]->id, $responseHTML);
    }

    /**
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewAngelTypesPreselection(): void
    {
        $angelTypes = $this->createAngelTypes();

        SignUpConfig::setMinimumConfig($this->config);
        $response = $this->subject->view();

        $this->assertSame(200, $response->getStatusCode());
        $responseHTML = $response->getBody()->__toString();

        // assert that the unrestricted angel type is there and checked
        FormFieldAssert::assertContainsCheckedCheckbox('angel_types_' . $angelTypes[0]->id, $responseHTML);

        // assert that restricted angel type are there and not checked
        FormFieldAssert::assertContainsUncheckedCheckbox('angel_types_' . $angelTypes[1]->id, $responseHTML);
        FormFieldAssert::assertContainsUncheckedCheckbox('angel_types_' . $angelTypes[2]->id, $responseHTML);

        // assert that the angel type with "hide_register" = true is not there
        FormFieldAssert::assertNotContainsInputField('angel_types_' . $angelTypes[3]->id, $responseHTML);
    }

    /**
     * Asserts that values are prefilled after submit
     *
     * @covers \Engelsystem\Controllers\RegistrationController
     */
    public function testViewValuesAfterSubmit(): void
    {
        $angelTypes = $this->createAngelTypes();

        // fake submit and set form-data in session
        $this->session->set('form-data-register-submit', '1');
        $this->session->set('form-data-angel_types_' . $angelTypes[1]->id, '1');

        SignUpConfig::setMinimumConfig($this->config);
        $response = $this->subject->view();

        $this->assertSame(200, $response->getStatusCode());
        $responseHTML = $response->getBody()->__toString();

        // assert that the unrestricted angel type is not checked
        FormFieldAssert::assertContainsUncheckedCheckbox('angel_types_' . $angelTypes[0]->id, $responseHTML);

        // assert that the restricted angel type is checked
        FormFieldAssert::assertContainsCheckedCheckbox('angel_types_' . $angelTypes[1]->id, $responseHTML);
    }

    /**
     * Creates three angel types:
     * - unrestricted
     * - restricted
     * - unrestricted, hidden on registration
     *
     * @return Array<AngelType>
     */
    private function createAngelTypes(): array
    {
        $angelType1 = AngelType::create([
            'name' => 'Test angel type 1',
            'restricted' => false,
        ]);

        $angelType2 = AngelType::create([
            'name' => 'Test angel type 2',
            'restricted' => true,
        ]);

        $angelType3 = AngelType::create([
            'name' => 'Test angel type 3',
            'restricted' => true,
        ]);

        $angelType4 = AngelType::create([
            'name' => 'Test angel type 4',
            'hide_register' => true,
            'restricted' => false,
        ]);

        $this->modelsToBeDeleted[] = $angelType1;
        $this->modelsToBeDeleted[] = $angelType2;
        $this->modelsToBeDeleted[] = $angelType3;
        $this->modelsToBeDeleted[] = $angelType4;
        return [$angelType1, $angelType2, $angelType3, $angelType4];
    }

    private function deleteModels(): void
    {
        foreach ($this->modelsToBeDeleted as $modelToBeDeleted) {
            $modelToBeDeleted->delete();
        }
    }
}
