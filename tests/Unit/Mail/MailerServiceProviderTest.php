<?php

namespace Engelsystem\Test\Unit\Mail;

use Engelsystem\Application;
use Engelsystem\Config\Config;
use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Mail\Mailer;
use Engelsystem\Mail\MailerServiceProvider;
use Engelsystem\Mail\Transport\LogTransport;
use Engelsystem\Test\Unit\ServiceProviderTest;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

class MailerServiceProviderTest extends ServiceProviderTest
{
    /** @var array */
    protected $defaultConfig = [
        'app_name' => 'Engelsystem App',
        'email'    => [
            'driver'   => 'mail',
            'from'     => [
                'name'    => 'Engelsystem',
                'address' => 'foo@bar.batz',
            ],
            'sendmail' => '/opt/bin/sendmail -bs',
        ],
    ];

    /** @var array */
    protected $smtpConfig = [
        'email' => [
            'driver'     => 'smtp',
            'host'       => 'mail.foo.bar',
            'port'       => 587,
            'tls'        => true,
            'username'   => 'foobar',
            'password'   => 'LoremIpsum123',
        ],
    ];

    /**
     * @covers \Engelsystem\Mail\MailerServiceProvider::register
     */
    public function testRegister()
    {
        $app = $this->getApplication();

        $serviceProvider = new MailerServiceProvider($app);
        $serviceProvider->register();

        $this->assertExistsInContainer(['mailer.transport', TransportInterface::class], $app);
        $this->assertExistsInContainer(['mailer.symfony', SymfonyMailer::class], $app);
        $this->assertExistsInContainer(['mailer', EngelsystemMailer::class, Mailer::class], $app);

        /** @var EngelsystemMailer $mailer */
        $mailer = $app->get('mailer');
        $this->assertEquals('Engelsystem App', $mailer->getSubjectPrefix());
        $this->assertEquals('Engelsystem', $mailer->getFromName());
        $this->assertEquals('foo@bar.batz', $mailer->getFromAddress());

        /** @var SendmailTransport $transport */
        $transport = $app->get('mailer.transport');
        $this->assertInstanceOf(SendmailTransport::class, $transport);
    }

    /**
     * @return array
     */
    public function provideTransports()
    {
        return [
            [LogTransport::class, ['email' => ['driver' => 'log']]],
            [SendmailTransport::class, ['email' => ['driver' => 'mail']]],
            [SendmailTransport::class, ['email' => ['driver' => 'sendmail']]],
            [
                EsmtpTransport::class,
                $this->smtpConfig,
            ],
        ];
    }

    /**
     * @covers       \Engelsystem\Mail\MailerServiceProvider::getTransport
     * @param string $class
     * @param array  $emailConfig
     * @dataProvider provideTransports
     */
    public function testGetTransport($class, $emailConfig = [])
    {
        $app = $this->getApplication($emailConfig);

        $serviceProvider = new MailerServiceProvider($app);
        $serviceProvider->register();

        $transport = $app->get('mailer.transport');
        $this->assertInstanceOf($class, $transport);
    }

    /**
     * @covers \Engelsystem\Mail\MailerServiceProvider::getTransport
     */
    public function testGetTransportNotFound()
    {
        $app = $this->getApplication(['email' => ['driver' => 'foo-bar-batz']]);
        $this->expectException(InvalidArgumentException::class);

        $serviceProvider = new MailerServiceProvider($app);
        $serviceProvider->register();
    }

    /**
     * @covers \Engelsystem\Mail\MailerServiceProvider::getSmtpTransport
     */
    public function testGetSmtpTransport()
    {
        $app = $this->getApplication($this->smtpConfig);

        $serviceProvider = new MailerServiceProvider($app);
        $serviceProvider->register();

        /** @var EsmtpTransport $transport */
        $transport = $app->get('mailer.transport');

        $this->assertEquals($this->smtpConfig['email']['username'], $transport->getUsername());
        $this->assertEquals($this->smtpConfig['email']['password'], $transport->getPassword());
    }

    /**
     * @param array $configuration
     * @return Application
     */
    protected function getApplication($configuration = []): Application
    {
        $app = new Application();

        $configuration = new Config(array_replace_recursive($this->defaultConfig, $configuration));
        $app->instance('config', $configuration);

        $logger = $this->getMockForAbstractClass(LoggerInterface::class);
        $app->instance(LoggerInterface::class, $logger);

        return $app;
    }

    /**
     * @param string[]    $abstracts
     * @param Application $container
     */
    protected function assertExistsInContainer($abstracts, $container)
    {
        $first = array_shift($abstracts);
        $this->assertContainerHas($first, $container);

        foreach ($abstracts as $abstract) {
            $this->assertContainerHas($abstract, $container);
            $this->assertEquals($container->get($first), $container->get($abstract));
        }
    }

    /**
     * @param string      $abstract
     * @param Application $container
     */
    protected function assertContainerHas($abstract, $container)
    {
        $this->assertTrue(
            $container->has($abstract) || $container->hasMethodBinding($abstract),
            sprintf('Container does not contain abstract %s', $abstract)
        );
    }
}
