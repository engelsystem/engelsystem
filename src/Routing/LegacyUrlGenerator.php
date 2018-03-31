<?php

namespace Engelsystem\Routing;

class LegacyUrlGenerator extends UrlGenerator
{
    /**
     * @param string $path
     * @param array  $parameters
     * @return string
     */
    public function to($path, $parameters = [])
    {
        $page = ltrim($path, '/');
        if (!empty($page)) {
            $page = str_replace('-', '_', $page);
            $parameters = array_merge(['p' => $page], $parameters);
        }

        $uri = parent::to('index.php', $parameters);
        $uri = preg_replace('~(/index\.php)+~', '/index.php', $uri);

        return $uri;
    }
}
