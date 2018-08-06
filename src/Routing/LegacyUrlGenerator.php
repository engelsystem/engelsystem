<?php

namespace Engelsystem\Routing;

/**
 * Provides urls when webserver rewriting is disabled.
 * 
 * The urls have the form <app url>/index.php?p=<path>&<parameters>
 */
class LegacyUrlGenerator extends UrlGenerator
{
    /**
     * @param string $path
     * @param array  $parameters
     * @return string urls in the form <app url>/index.php?p=<path>&<parameters>
     */
    public function link_to($path, $parameters = [])
    {
        $page = ltrim($path, '/');
        if (!empty($page)) {
            $page = str_replace('-', '_', $page);
            $parameters = array_merge(['p' => $page], $parameters);
        }

        $uri = parent::link_to('index.php', $parameters);
        $uri = preg_replace('~(/index\.php)+~', '/index.php', $uri);

        return $uri;
    }
}
