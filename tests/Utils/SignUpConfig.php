<?php

declare(strict_types=1);

namespace Engelsystem\Test\Utils;

use Engelsystem\Config\Config;
use Engelsystem\Config\GoodieType;

final class SignUpConfig
{
    public static function setMaximumConfig(Config $config): void
    {
        $requiredFields = $config->get('signup_required_fields');
        $requiredFields['pronoun'] = false;
        $requiredFields['firstname'] = false;
        $requiredFields['lastname'] = false;
        $requiredFields['planned_arrival_date'] = true;
        $requiredFields['tshirt_size'] = true;
        $requiredFields['mobile'] = false;
        $requiredFields['dect'] = false;
        $config->set('registration_enabled', true);
        $config->set('enable_password', true);
        $config->set('enable_pronoun', true);
        $config->set('goodie_type', GoodieType::Tshirt->value);
        $config->set('tshirt_sizes', [
            'S'    => 'Small Straight-Cut',
            'M'    => 'Medium Straight-Cut',
        ]);
        // disallow numeric values in username for tests
        $config->set('username_regex', '/\d+/');
        $config->set('min_password_length', 3);
        $config->set('theme', 0);
        $config->set('enable_planned_arrival', true);
        $config->set('enable_user_name', true);
        $config->set('enable_mobile_show', true);
        $config->set('enable_dect', true);
        $config->set('signup_required_fields', $requiredFields);
    }

    public static function setMinimumConfig(Config $config): void
    {
        $requiredFields = $config->get('signup_required_fields');
        $requiredFields['pronoun'] = false;
        $requiredFields['firstname'] = false;
        $requiredFields['lastname'] = false;
        $requiredFields['planned_arrival_date'] = true;
        $requiredFields['tshirt_size'] = true;
        $requiredFields['mobile'] = false;
        $requiredFields['dect'] = false;
        $config->set('registration_enabled', true);
        $config->set('enable_password', true);
        $config->set('enable_pronoun', false);
        $config->set('goodie_type', GoodieType::None->value);
        // disallow numeric values in username for tests
        $config->set('username_regex', '/\d+/');
        $config->set('min_password_length', 3);
        $config->set('theme', 0);
        $config->set('enable_planned_arrival', false);
        $config->set('enable_user_name', false);
        $config->set('enable_mobile_show', false);
        $config->set('enable_dect', false);
        $config->set('signup_required_fields', $requiredFields);
    }
}
