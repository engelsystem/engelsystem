<?php

namespace Engelsystem\Renderer\Twig\Extensions;

use Carbon\Carbon;
use Twig_Extension as TwigExtension;
use Twig_Extension_GlobalsInterface as GlobalsInterface;

class Globals extends TwigExtension implements GlobalsInterface
{
    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        global $user;

        $eventConfig = $this->getEventConfig();
        if (empty($eventConfig)) {
            $eventConfig = [];
        }

        return [
            'user'         => isset($user) ? $user : [],
            'event_config' => $this->filterEventConfig($eventConfig),
        ];
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    protected function getEventConfig()
    {
        return EventConfig();
    }

    /**
     * @param $eventConfig
     * @return mixed
     */
    protected function filterEventConfig($eventConfig)
    {
        array_walk($eventConfig, function (&$value, $key) {
            if (is_null($value) || !in_array($key, [
                    'buildup_start_date',
                    'event_start_date',
                    'event_end_date',
                    'teardown_end_date',
                ])) {
                return;
            }

            $value = Carbon::createFromTimestamp($value);
        });

        return $eventConfig;
    }
}
