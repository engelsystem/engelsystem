<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Illuminate\Support\Str;

abstract class BaseConfigController extends BaseController
{
    use HasUserNotifications;

    protected array $options = [];

    protected array $permissions = [
        'config.edit',
    ];

    public function __construct()
    {
        $this->options = config('config_options', []);
        // Sort by order ascending, ignore missing
        uasort(
            $this->options,
            fn($a, $b) => ($a['order'] ?? $b['order'] ?? 0) <=> ($b['order'] ?? $a['order'] ?? 0)
        );
    }

    protected function getPageData(string $page): array
    {
        return [
            'page' => $page,
            'title' => $this->options[$page]['title'],
            'config' => $this->options[$page]['config'],
            'options' => $this->options,
        ];
    }

    protected function parseOptions(bool $localConfigWritable = false, bool $withAll = false): void
    {
        $fromEnv = array_filter(config('env_config', []), fn($a) => !is_null($a));

        foreach ($this->options as $key => $value) {
            // Add page URL
            if (empty($this->options[$key]['url'])) {
                $this->options[$key]['url'] = url('/admin/config/' . $key);
                $this->options[$key]['url_added'] = true;
            }

            // Configure page translation names
            if (empty($this->options[$key]['title'])) {
                $this->options[$key]['title'] = 'config.' . $key;
            }

            // Define internal validation action
            $internalValidation = 'validate' . Str::ucfirst($key);
            if (method_exists($this, $internalValidation)) {
                // Used until proper dynamic config loading is implemented
                $this->options[$key]['validation'] = [$this, $internalValidation];
            }

            // Iterate over settings
            foreach ($this->options[$key]['config'] as $name => $config) {
                // Ignore hidden options
                if (!empty($config['hidden']) && !$withAll) {
                    unset($this->options[$key]['config'][$name]);
                    continue;
                }

                // Set name for translation
                if (empty($this->options[$key]['config'][$name]['name'])) {
                    $this->options[$key]['config'][$name]['name'] = 'config.' . $name;
                }

                // Configure required icon
                if (!empty($this->options[$key]['config'][$name]['required'])) {
                    $this->options[$key]['config'][$name]['required_icon'] = true;
                }

                // Set ENV name
                if (empty($config['env'])) {
                    $config['env'] = Str::upper($name);
                    $this->options[$key]['config'][$name]['env'] = $config['env'];
                }

                // Configure select values
                if ($config['type'] == 'select' || $config['type'] == 'select_multi') {
                    $data = [];
                    foreach ($config['data'] ?? [] as $dataKey => $dataValue) {
                        if (is_int($dataKey) && !($config['preserve_key'] ?? false)) {
                            $dataKey = $dataValue;
                            $dataValue = 'config.' . $name . '.select.' . $dataKey;
                        }
                        $data[$dataKey] = $dataValue;
                    }
                    $this->options[$key]['config'][$name]['data'] = $data;
                }

                // Set if set from ENV
                if (isset($fromEnv[$config['env']])) {
                    $this->options[$key]['config'][$name]['in_env'] = true;
                }

                // Set if local config can be written to
                if (!$localConfigWritable && ($config['write_back'] ?? false)) {
                    $this->options[$key]['config'][$name]['writable'] = false;
                }
            }
        }
    }
}
