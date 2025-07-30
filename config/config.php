<?php

return [
    // Hide columns in backend user view. Possible values are any sortable parameters of the table.
    'disabled_user_view_columns' => ['freeloads', 'active', 'arrival_date', 'departure_date'],

    // Predefined headers
    // To disable a header in config.php, you can set its value to null
    'headers'                 => [
        'X-Content-Type-Options'  => 'nosniff',
        'X-Frame-Options'         => 'sameorigin',
        'Referrer-Policy'         => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' =>
            'default-src \'self\' https://www.openstreetmap.org; '
            . ' style-src \'self\' \'unsafe-inline\'; '
            . 'img-src \'self\' data:;',
        'X-XSS-Protection'        => '1; mode=block',
        'Feature-Policy'          => 'autoplay \'none\'',
        //'Strict-Transport-Security' => 'max-age=7776000',
        //'Expect-CT' => 'max-age=7776000,enforce,report-uri="[uri]"',
    ],
];
