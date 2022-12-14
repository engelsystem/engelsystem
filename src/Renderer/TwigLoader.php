<?php

namespace Engelsystem\Renderer;

use Twig\Error\LoaderError as ErrorLoader;
use Twig\Loader\FilesystemLoader as FilesystemLoader;

class TwigLoader extends FilesystemLoader
{
    /**
     * @throws ErrorLoader
     */
    public function findTemplate(string $name, bool $throw = true): ?string
    {
        $extension = '.twig';
        $extensionLength = mb_strlen($extension);
        if (mb_substr($name, -$extensionLength, $extensionLength) !== $extension) {
            $name .= $extension;
        }

        return parent::findTemplate($name, $throw);
    }
}
