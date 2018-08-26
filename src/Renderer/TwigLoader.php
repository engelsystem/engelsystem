<?php

namespace Engelsystem\Renderer;

use Twig_Error_Loader;
use Twig_Loader_Filesystem as FilesystemLoader;

class TwigLoader extends FilesystemLoader
{
    /**
     * @param string $name
     * @param bool   $throw
     * @return false|string
     * @throws Twig_Error_Loader
     */
    public function findTemplate($name, $throw = true)
    {
        $extension = '.twig';
        $extensionLength = strlen($extension);
        if (substr($name, -$extensionLength, $extensionLength) !== $extension) {
            $name .= $extension;
        }

        return parent::findTemplate($name, $throw);
    }
}
