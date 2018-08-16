<?php

namespace Engelsystem\Test\Unit\Http\Stub;

use Engelsystem\Http\MessageTrait;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\HttpFoundation\Request;

class MessageTraitRequestImplementation extends Request implements MessageInterface
{
    use MessageTrait;
}
