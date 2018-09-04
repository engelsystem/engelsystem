<?php

namespace Engelsystem\Test\Unit\Http\Stub;

use Engelsystem\Http\MessageTrait;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\HttpFoundation\Response;

class MessageTraitResponseImplementation extends Response implements MessageInterface
{
    use MessageTrait;
}
