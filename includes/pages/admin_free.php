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
        '' => __('Alle')
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
        ->orderBy('users.name')
        ->groupBy('users.id');

    if (!empty($angelType)) {
        $query->join('UserAngelTypes', function ($join) use ($angelType) {
            /** @var JoinClause $join */
            $join->on('UserAngelTypes.user_id', '=', 'users.id')
                ->where('UserAngelTypes.angeltype_id', '=', $angelType);
        });
        $query->join('AngelTypes', 'UserAngelTypes.angeltype_id', 'AngelTypes.id')
            ->whereNotNull('UserAngelTypes.confirm_user_id')
            ->orWhere('AngelTypes.restricted', '=', '0');
    }

    if ($request->has('submit')) {
        $users = $query->get();
    } else {
        $users = [];
    }
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
                $t = trim($t);
                if (!empty($t) && stristr($index, $t)) {
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
            'last_shift'  => User_last_shift_render($usr),
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
                div('col-md-12 form-inline', [
                    form_text('search', __('Search'), $search),
                    form_select('angeltype', __('Angeltype'), $angel_types, $angelType),
                    form_submit('submit', __('Search'))
                ]),
            ]),
        ]),
        table([
            'name'        => __('Nick'),
            'shift_state' => __('Next shift'),
            'last_shift'  => __('Last shift'),
            'dect'        => __('DECT'),
            'email'       => __('E-Mail'),
            'actions'     => ''
        ], $free_users_table),
    ]);
}
