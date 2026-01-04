<?php

declare(strict_types=1);

namespace Engelsystem\Plugins;

class DisabledPlugin extends Plugin
{
    public function __construct(array $pluginInfo)
    {
        $pluginInfo['providers'] = [];
        $pluginInfo['middleware'] = [];
        $pluginInfo['event_handlers'] = [];
        $pluginInfo['config_options'] = [];
        $pluginInfo['routes'] = [];

        parent::__construct($pluginInfo);
    }
}
