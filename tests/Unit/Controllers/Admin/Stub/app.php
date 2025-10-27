<?php

declare(strict_types=1);

return [
    'config_options' => [
        'test' => [
            'config' => [
                'write_to_file' => [
                    'type' => 'string',
                    'write_back' => true,
                ],
                'another_write' => [
                    'type' => 'string',
                    'write_back' => true,
                ],
                'normal_config' => [
                    'type' => 'string',
                ],
                'element.key' => [
                    'type' => 'string',
                    'write_back' => true,
                ],
            ],
        ],
    ],
];
