<?php

declare(strict_types=1);

namespace Engelsystem\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Uuid as UuidHelper;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

class Uuid extends TwigExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('uuid', [$this, 'getUuid']),
            new TwigFunction('uuidBy', [$this, 'getUuidBy']),
        ];
    }

    public function getUuid(): string
    {
        return UuidHelper::uuid();
    }

    public function getUuidBy(mixed $value, ?string $name = null): string
    {
        return UuidHelper::uuidBy($value, $name);
    }
}
