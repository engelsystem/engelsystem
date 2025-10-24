<?php

declare(strict_types=1);

return [
    'file' => 'app.php',
    'app' => 'loaded',

    'config_options' => [
        'test' => [
            'config' => [
                'from_env' => [
                    'type' => 'string',
                    'env' => 'VALUE_FROM_ENV',
                ],
                'some_foo' => [
                    'type' => 'string',
                ],
                'another_bar' => [
                    'type' => 'string',
                ],
                'not_set' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
                'date_time' => [
                    'type' => 'datetime-local',
                    'default' => true,
                ],
                'bool' => [
                    'type' => 'boolean',
                ],
                'multi_val' => [
                    'type' => 'select_multi',
                    'data' => ['test'],
                ],
            ],
        ],
        'system' => [
            'config' => [
                'timezone' => [
                    'data' => [],
                    'default' => 'Test/Testing',
                ],
            ],
        ],
    ],
];
