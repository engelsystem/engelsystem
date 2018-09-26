<?php

namespace Engelsystem\Test\Unit\Mail\Transport\Stub;

use Engelsystem\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage as SimpleMessage;

class TransportImplementation extends Transport
{
    /**
     * {@inheritdoc}
     */
    public function send(SimpleMessage $message, &$failedRecipients = null)
    {
        return 0;
    }

    /**
     * @param SimpleMessage $message
     * @return array
     */
    public function getAllRecipients(SimpleMessage $message)
    {
        return $this->allRecipients($message);
    }

    /**
     * @param SimpleMessage $message
     * @return string
     */
    public function getGetTo(SimpleMessage $message)
    {
        return $this->getTo($message);
    }
}
