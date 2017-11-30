<?php

// To change settings create a config.php

return [
    // MySQL-Connection Settings
    'database'                => [
        'host' => env('MYSQL_HOST', (env('CI', false) ? 'mariadb' : 'localhost')),
        'user' => env('MYSQL_USER', 'root'),
        'pw'   => env('MYSQL_PASSWORD', ''),
        'db'   => env('MYSQL_DATABASE', 'engelsystem'),
    ],

    // For accessing stats
    'api_key'                 => '',

    // Enable maintenance mode (show a static page)
    'maintenance'             => false,

    // Set to development to enable debugging messages
    'environment'             => 'production',

    // URL to the angel faq and job description
    'faq_url'                 => 'https://events.ccc.de/congress/2013/wiki/Static:Volunteers',

    // Contact email address, linked on every page
    'contact_email'           => 'mailto:ticket@c3heaven.de',

    // From address of all emails
    'no_reply_email'          => 'noreply@engelsystem.de',

    // Default theme, 1=style1.css
    'theme'                   => 1,

    // Available themes
    'available_themes'        => [
        '6' => 'Engelsystem 34c3 dark (2017)',
        '5' => 'Engelsystem 34c3 light (2017)',
        '4' => 'Engelsystem 33c3 (2016)',
        '3' => 'Engelsystem 32c3 (2015)',
        '2' => 'Engelsystem cccamp15',
        '0' => 'Engelsystem light',
        '1' => 'Engelsystem dark'
    ],

    // Number of News shown on one site
    'display_news'            => 6,

    // Users are able to sign up
    'registration_enabled'    => true,

    // Only arrived angels can sign up for shifts
    'signup_requires_arrival' => false,

    // Anzahl Stunden bis zum Austragen eigener Schichten
    'last_unsubscribe'        => 3,

    // Setzt den zu verwendenden Crypto-Algorithmus (entsprechend der Dokumentation von crypt()).
    // Falls ein Benutzerpasswort in einem anderen Format gespeichert ist,
    // wird es bei der ersten Benutzung des Klartext-Passworts in das neue Format
    // konvertiert.
    //  MD5         '$1'
    //  Blowfish    '$2y$13'
    //  SHA-256     '$5$rounds=5000'
    //  SHA-512     '$6$rounds=5000'
    'crypt_alg'               => '$6$rounds=5000',

    'min_password_length'     => 8,

    // Wenn Engel beim Registrieren oder in ihrem Profil eine T-Shirt Größe angeben sollen, auf true setzen:
    'enable_tshirt_size'      => true,

    // Number of shifts to freeload until angel is locked for shift signup.
    'max_freeloadable_shifts' => 2,

    // local timezone
    'timezone'                => 'Europe/Berlin',

    // weigh every shift the same
    //'shift_sum_formula'       => 'SUM(`end` - `start`)',

    // Multiply 'night shifts' and freeloaded shifts (start or end between 2 and 6 exclusive) by 2
    'shift_sum_formula'       => '
        SUM(
            (1 +
                (
                  (HOUR(FROM_UNIXTIME(`Shifts`.`end`)) > 2 AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) < 6)
                  OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) > 2 AND HOUR(FROM_UNIXTIME(`Shifts`.`start`)) < 6)
                  OR (HOUR(FROM_UNIXTIME(`Shifts`.`start`)) <= 2 AND HOUR(FROM_UNIXTIME(`Shifts`.`end`)) >= 6)
                )
            )
            * (`Shifts`.`end` - `Shifts`.`start`)
            * (1 - 3 * `ShiftEntry`.`freeloaded`)
        )
    ',

    // Voucher calculation
    'voucher_settings'        => [
        'initial_vouchers'   => 2,
        'shifts_per_voucher' => 1,
    ],

    // Available locales in /locale/
    'locales'                 => [
        'de_DE.UTF-8' => 'Deutsch',
        'en_US.UTF-8' => 'English',
    ],

    'default_locale' => 'en_US.UTF-8',

    // Available T-Shirt sizes, set value to null if not available
    'tshirt_sizes'   => [
        ''     => _('Please select...'),
        'S'    => 'S',
        'M'    => 'M',
        'L'    => 'L',
        'XL'   => 'XL',
        '2XL'  => '2XL',
        '3XL'  => '3XL',
        '4XL'  => '4XL',
        '5XL'  => '5XL',
        'S-G'  => 'S Girl',
        'M-G'  => 'M Girl',
        'L-G'  => 'L Girl',
        'XL-G' => 'XL Girl',
    ],
];
