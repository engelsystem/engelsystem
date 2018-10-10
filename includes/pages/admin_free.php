<?php

use Engelsystem\Database\DB;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function admin_free_title()
{
    return __('Free angels');
}

/**
 * @return string
 */
function admin_free()
{
    global $privileges;
    $request = request();

    $search = '';
    if ($request->has('search')) {
        $search = strip_request_item('search');
    }

    $angelTypeSearch = '';
    $angelType = $request->input('angeltype', '');
    if (!empty($angelType)) {
        $angelTypeSearch = ' INNER JOIN `UserAngelTypes` ON (`UserAngelTypes`.`angeltype_id` = '
            . DB::getPdo()->quote($angelType)
            . ' AND `UserAngelTypes`.`user_id` = `users`.`id`';
        if ($request->has('confirmed_only')) {
            $angelTypeSearch .= ' AND `UserAngelTypes`.`confirm_user_id`';
        }
        $angelTypeSearch .= ') ';
    }

    $angel_types_source = DB::select('SELECT `id`, `name` FROM `AngelTypes` ORDER BY `name`');
    $angel_types = [
        '' => 'alle Typen'
    ];
    foreach ($angel_types_source as $angel_type) {
        $angel_types[$angel_type['id']] = $angel_type['name'];
    }

    /** @var User[] $users */
    $users = User::query()->raw(sprintf('
          SELECT `users`.*
          FROM `users`
          %s
          LEFT JOIN `ShiftEntry` ON `users`.`id` = `ShiftEntry`.`UID`
          LEFT JOIN `users_state` ON `users`.`id` = `users_state`.`user_id`
          LEFT JOIN `Shifts`
              ON (
                  `ShiftEntry`.`SID` = `Shifts`.`SID`
                  AND `Shifts`.`start` < %u
                  AND `Shifts`.`end` > %u
              )
          WHERE `users_state`.`arrived` = 1
          AND `Shifts`.`SID` IS NULL
          GROUP BY `users`.`id`
          ORDER BY `users`
        ', $angelTypeSearch, time(), time()
        )
    );

    $free_users_table = [];
    if ($search == '') {
        $tokens = [];
    } else {
        $tokens = explode(' ', $search);
    }
    foreach ($users as $usr) {
        if (count($tokens) > 0) {
            $match = false;
            $index = join('', $usr->toArray());
            foreach ($tokens as $t) {
                if (stristr($index, trim($t))) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                continue;
            }
        }

        $free_users_table[] = [
            'name'        => User_Nick_render($usr),
            'shift_state' => User_shift_state_render($usr),
            'dect'        => $usr->contact->dect,
            'email'       => $usr->settings->email_human ? ($usr->contact->email ? $usr->contact->email : $usr->email) : glyph('eye-close'),
            'actions'     =>
                in_array('admin_user', $privileges)
                    ? button(page_link_to('admin_user', ['id' => $usr->id]), __('edit'), 'btn-xs')
                    : ''
        ];
    }
    return page_with_title(admin_free_title(), [
        form([
            div('row', [
                div('col-md-4', [
                    form_text('search', __('Search'), $search)
                ]),
                div('col-md-4', [
                    form_select('angeltype', __('Angeltype'), $angel_types, $angelType)
                ]),
                div('col-md-2', [
                    form_checkbox('confirmed_only', __('Only confirmed'), $request->has('confirmed_only'))
                ]),
                div('col-md-2', [
                    form_submit('submit', __('Search'))
                ])
            ])
        ]),
        table([
            'name'        => __('Nick'),
            'shift_state' => '',
            'dect'        => __('DECT'),
            'email'       => __('E-Mail'),
            'actions'     => ''
        ], $free_users_table)
    ]);
}
