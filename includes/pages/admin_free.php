<?php

use Engelsystem\Database\DB;
use Engelsystem\Models\User\User;
use Illuminate\Database\Query\JoinClause;

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
    $request = request();

    $search = '';
    if ($request->has('search')) {
        $search = strip_request_item('search');
    }

    $angel_types_source = DB::select('SELECT `id`, `name` FROM `AngelTypes` ORDER BY `name`');
    $angel_types = [
        '' => 'alle Typen'
    ];
    foreach ($angel_types_source as $angel_type) {
        $angel_types[$angel_type['id']] = $angel_type['name'];
    }

    $angelType = $request->input('angeltype', '');
    $query = User::query()
        ->select('users.*')
        ->leftJoin('ShiftEntry', 'users.id', 'ShiftEntry.UID')
        ->leftJoin('users_state', 'users.id', 'users_state.user_id')
        ->leftJoin('Shifts', function ($join) {
            /** @var JoinClause $join */
            $join->on('ShiftEntry.SID', '=', 'Shifts.SID')
                ->where('Shifts.start', '<', time())
                ->where('Shifts.end', '>', time());
        })
        ->where('users_state.arrived', '=', 1)
        ->whereNull('Shifts.SID')
        ->groupBy('users.id');

    if (!empty($angelType)) {
        $query->join('UserAngelTypes', function ($join) use ($angelType, $request, $query) {
            /** @var JoinClause $join */
            $join->on('UserAngelTypes.user_id', '=', 'users.id')
                ->where('UserAngelTypes.angeltype_id', '=', $angelType);

            if ($request->has('confirmed_only')) {
                $join->whereNotNull('UserAngelTypes.confirm_user_id');
            }
        });
    }

    $users = $query->get();
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
                auth()->can('admin_user')
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
