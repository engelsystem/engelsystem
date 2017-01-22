<?php

// To change settings create a config.php

return [
    // MySQL-Connection Settings
    'database'         => [
        'host' => 'localhost',
        'user' => 'root',
        'pw'   => '',
        'db'   => 'engelsystem',
    ],

    // For accessing stats
    'api_key'          => '',

    // Enable maintenance mode (show a static page)
    'maintenance'      => false,

    // Set to development to enable debugging messages
    'environment'      => 'production',

    // URL to the angel faq and job description
    'faq_url'          => 'https://events.ccc.de/congress/2013/wiki/Static:Volunteers',

    // Contact email address, linked on every page
    'contact_email'    => 'mailto:ticket@c3heaven.de',

    // Default theme of the start page, 1=style1.css
    'default_theme'    => 1,

    // Number of News shown on one site
    'display_news'     => 6,

    // Anzahl Stunden bis zum Austragen eigener Schichten
    'last_unsubscribe' => 3,

    // Setzt den zu verwendenden Crypto-Algorismus (entsprechend der Dokumentation von crypt()).
    // Falls ein Benutzerpasswort in einem anderen Format gespeichert ist,
    // wird es bei der ersten Benutzung des Klartext-Passworts in das neue Format
    // konvertiert.
    //  MD5         '$1'
    //  Blowfish    '$2y$13'
    //  SHA-256     '$5$rounds=5000'
    //  SHA-512     '$6$rounds=5000'
    'crypt_alg'        => '$6$rounds=5000', // SHA-512

    'min_password_length'     => 8,

    // Wenn Engel beim Registrieren oder in ihrem Profil eine T-Shirt Größe angeben sollen, auf true setzen:
    'enable_tshirt_size'      => true,

    // Number of shifts to freeload until angel is locked for shift signup.
    'max_freeloadable_shifts' => 2,

    // local timezone
    'timezone'                => 'Europe/Berlin',

    // multiply 'night shifts' and freeloaded shifts (start or end between 2 and 6 exclusive) by 2
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
    // weigh every shift the same
    //'shift_sum_formula'       => 'SUM(`end` - `start`)',

    // voucher calculation
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
