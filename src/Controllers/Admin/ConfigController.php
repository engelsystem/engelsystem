<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Admin;

use Engelsystem\Config\Config;
use Engelsystem\Controllers\BaseController;
use Engelsystem\Controllers\HasUserNotifications;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Http\Exceptions\HttpNotFound;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGeneratorInterface;
use Engelsystem\Models\EventConfig;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class ConfigController extends BaseController
{
    use HasUserNotifications;

    protected array $permissions = [
        'config.edit',
    ];

    protected array $options = [
        /**
         *  '[name]' => [
         *      'title' => '[title], # Optional, default config.[name]
         *      'permission' => '[permission]' # Optional, string or array
         *      'icon' => '[icon]', # Optional, default gear-fill
         *      'validation' => callable, # Optional. callable to validate the request
         *      'config' => [
         *          '[name]' => [
         *              'name' => 'some.value', # Optional, default: config.[name]
         *              'type' => 'string', # string, text, datetime-local, ...
         *              'default' => '[value]', # Optional
         *              'required' => true, # Optional, default false
         *              # Optional config.[name].info for information messages
         *              # Optionally other options used by the correlating field
         *          ],
         *      ],
         *  ],
         */
        'event' => [
            'config' => [
                'name' => [
                    'type' => 'string',
                ],
                'welcome_msg' => [
                    'type' => 'text',
                    'rows' => 5,
                ],
                'buildup_start' => [
                    'type' => 'datetime-local',
                ],
                'event_start' => [
                    'type' => 'datetime-local',
                ],
                'event_end' => [
                    'type' => 'datetime-local',
                ],
                'teardown_end' => [
                    'type' => 'datetime-local',
                ],
            ],
        ],
    ];

    public function __construct(
        protected Response $response,
        protected Config $config,
        protected Redirector $redirect,
        protected UrlGeneratorInterface $url,
        protected LoggerInterface $log,
        array $options = [],
    ) {
        $this->options += $options;
        $this->parseOptions();
    }

    public function index(): Response
    {
        return $this->redirect->to('/admin/config/' . array_key_first($this->options));
    }

    public function edit(Request $request): Response
    {
        $page = $this->activePage($request);

        return $this->response->withView(
            'admin/config/index',
            [
                'page' => $page,
                'title' => $this->options[$page]['title'],
                'config' => $this->options[$page]['config'],
                'options' => $this->options,
            ]
        );
    }

    public function save(Request $request): Response
    {
        $page = $this->activePage($request);
        $data = $this->validation($page, $request);
        $settings = $this->options[$page]['config'];

        $changes = [];
        foreach ($settings as $key => $options) {
            $value = $data[$key] ?? $options['default'] ?? null;

            $value = match ($options['type']) {
                'datetime-local' => $value ? Carbon::createFromDatetime($value) : $value,
                default => $value,
            };

            if ($this->config->get($key) == $value) {
                continue;
            }

            $changes[] = sprintf('%s = "%s"', $key, $value);

            (new EventConfig())
                ->findOrNew($key)
                ->setAttribute('name', $key)
                ->setAttribute('value', $value)
                ->save();
        }

        $this->log->info(
            'Updated {page} configuration: {changes}',
            [
                'page' => $page,
                'changes' => implode(', ', $changes),
            ]
        );

        $this->addNotification('config.edit.success');

        return $this->redirect->back();
    }

    protected function validation(string $page, Request $request): array
    {
        $rules = [];
        $config = $this->options[$page];
        $settings = $config['config'];

        // Generate validation rules
        foreach ($settings as $key => $setting) {
            $validation = [];
            $validation[] = empty($setting['required']) ? 'optional' : 'required';

            match ($setting['type']) {
                'string', 'text' => null, // Anything is valid here when optional
                'datetime-local' => $validation[] = 'date_time',
                default => throw new InvalidArgumentException(
                    'Type ' . $setting['type'] . ' of ' . $key . ' not defined'
                ),
            };

            $rules[$key] = implode('|', $validation);
        }

        if (!empty($config['validation']) || method_exists($this, 'validate' . Str::ucfirst($page))) {
            $callback = $config['validation'] ?? null;
            if (!is_callable($callback)) {
                // Used until proper dynamic config loading is implemented
                $callback = [$this, 'validate' . Str::ucfirst($page)];
            }

            return $callback($request, $rules);
        }

        return $this->validate($request, $rules);
    }

    protected function parseOptions(): void
    {
        foreach ($this->options as $key => $value) {
            // Add page URLs
            $this->options[$key]['url'] = $this->url->to('/admin/config/' . $key);

            // Configure page translation names
            if (empty($this->options[$key]['title'])) {
                $this->options[$key]['title'] = 'config.' . $key;
            }

            // Iterate over settings
            foreach ($this->options[$key]['config'] as $name => $config) {
                // Set name for translation
                if (empty($this->options[$key]['config'][$name]['name'])) {
                    $this->options[$key]['config'][$name]['name'] = 'config.' . $name;
                }

                // Configure required icon
                if (!empty($this->options[$key]['config'][$name]['required'])) {
                    $this->options[$key]['config'][$name]['required_icon'] = true;
                }
            }
        }
    }

    protected function activePage(Request $request): string
    {
        $page = $request->getAttribute('page');

        if (empty($this->options[$page])) {
            throw new HttpNotFound();
        }

        return $page;
    }

    protected function validateEvent(Request $request, array $rules): array
    {
        // Run general validation
        $data = $this->validate($request, $rules);
        $addedRules = [];

        // Ensure event dates are after each other
        $dates = ['buildup_start', 'event_start', 'event_end', 'teardown_end'];
        foreach ($dates as $i => $dateField) {
            if (!$i || !$data[$dateField]) {
                continue;
            }

            foreach (array_slice($dates, 0, $i) as $previousDateField) {
                if (!$data[$previousDateField]) {
                    continue;
                }

                $addedRules[$dateField][] = ['after', $data[$previousDateField], 'true'];
            }
        }

        if (!empty($addedRules)) {
            $this->validate($request, $addedRules);
        }

        return $data;
    }
}
