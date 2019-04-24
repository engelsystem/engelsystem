<?php

namespace Engelsystem\Mail\Transport;

use Swift_Events_EventListener;
use Swift_Mime_SimpleMessage as SimpleMessage;
use Swift_Transport as SwiftTransport;

abstract class Transport implements SwiftTransport
{
    /**
     * Test if this Transport mechanism has started.
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return true;
    }

    /**
     * Start this Transport mechanism.
     */
    public function start()
    {
    }

    /**
     * Stop this Transport mechanism.
     */
    public function stop()
    {
    }

    /**
     * Check if this Transport mechanism is alive.
     *
     * If a Transport mechanism session is no longer functional, the method
     * returns FALSE. It is the responsibility of the developer to handle this
     * case and restart the Transport mechanism manually.
     *
     * @example
     *
     *   if (!$transport->ping()) {
     *      $transport->stop();
     *      $transport->start();
     *   }
     *
     * The Transport mechanism will be started, if it is not already.
     *
     * It is undefined if the Transport mechanism attempts to restart as long as
     * the return value reflects whether the mechanism is now functional.
     *
     * @return bool TRUE if the transport is alive
     */
    public function ping(): bool
    {
        return true;
    }

    /**
     * Register a plugin in the Transport.
     *
     * @param Swift_Events_EventListener $plugin
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
    }

    /**
     * Returns a unified list of all recipients
     *
     * @param SimpleMessage $message
     * @return array
     */
    protected function allRecipients(SimpleMessage $message): array
    {
        return array_merge(
            (array)$message->getTo(),
            (array)$message->getCc(),
            (array)$message->getBcc()
        );
    }

    /**
     * Returns a concatenated list of mail recipients
     *
     * @param  SimpleMessage $message
     * @return string
     */
    protected function getTo(SimpleMessage $message): string
    {
        return $this->formatTo($this->allRecipients($message));
    }

    /**
     * @param array $recipients
     * @return string
     */
    protected function formatTo(array $recipients)
    {
        $list = [];
        foreach ($recipients as $address => $name) {
            $list[] = $name ? sprintf('%s <%s>', $name, $address) : $address;
        }

        return implode(',', $list);
    }
}
