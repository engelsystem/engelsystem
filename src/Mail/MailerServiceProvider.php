<?php

namespace Engelsystem\Mail;

use Engelsystem\Config\Config;
use Engelsystem\Container\ServiceProvider;
use Engelsystem\Mail\Transport\LogTransport;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;

class MailerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var Config $config */
        $config = $this->app->get('config');
        $mailConfig = $config->get('email');

        $transport = $this->getTransport($mailConfig['driver'], $mailConfig);
        $this->app->instance(TransportInterface::class, $transport);
        $this->app->instance('mailer.transport', $transport);

        /** @var SymfonyMailer $symfonyMailer */
        $symfonyMailer = $this->app->make(SymfonyMailer::class);
        $this->app->instance(SymfonyMailer::class, $symfonyMailer);
        $this->app->instance(MailerInterface::class, $symfonyMailer);
        $this->app->instance('mailer.symfony', $symfonyMailer);

        /** @var EngelsystemMailer $mailer */
        $mailer = $this->app->make(EngelsystemMailer::class);
        $mailer->setFromAddress($mailConfig['from']['address']);
        $mailer->setSubjectPrefix($config->get('app_name'));
        if (!empty($mailConfig['from']['name'])) {
            $mailer->setFromName($mailConfig['from']['name']);
        }

        $this->app->instance(EngelsystemMailer::class, $mailer);
        $this->app->instance(Mailer::class, $mailer);
        $this->app->instance('mailer', $mailer);
    }

    protected function getTransport(?string $transport, array $config): TransportInterface
    {
        return match ($transport) {
            'log'              => $this->app->make(LogTransport::class),
            'mail', 'sendmail' => $this->app->make(
                SendmailTransport::class,
                ['command' => $config['sendmail'] ?? null]
            ),
            'smtp'             => $this->getSmtpTransport($config),
            default            => Transport::fromDsn($transport ?? ''),
        };
    }

    protected function getSmtpTransport(array $config): SmtpTransport
    {
        /** @var EsmtpTransport $transport */
        $transport = $this->app->make(EsmtpTransport::class, [
            'host' => $config['host'] ?? 'localhost',
            'port' => $config['port'] ?? 0,
            'tls'  => $config['tls'] ?? null,
            'logger' => null,
        ]);

        if (!empty($config['username'])) {
            $transport->setUsername($config['username']);
        }

        if (!empty($config['password'])) {
            $transport->setPassword($config['password']);
        }

        return $transport;
    }
}
