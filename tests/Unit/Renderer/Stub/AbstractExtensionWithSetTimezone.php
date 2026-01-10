<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Stub;

use Twig\Extension\AbstractExtension;

abstract class AbstractExtensionWithSetTimezone extends AbstractExtension
{
    public function setTimezone(string $timezone): void
    {
    }
}
