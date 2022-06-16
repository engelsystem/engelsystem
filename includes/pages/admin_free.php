<?php

use Engelsystem\Database\Db;
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

    $angel_types_source = Db::select('SELECT `id`, `name` FROM `AngelTypes` ORDER BY `name`');
    $angel_types = [
        '' => __('All')
    ];
    foreach ($angel_types_source as $angel_type) {
        $angel_types[$angel_type['id']] = $angel_type['name'];
    }

    $angelType = $request->input('angeltype', '');

    $users = [];
    if ($request->has('submit')) {
        $query = User::with('personalData')
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

            $query->join('AngelTypes', function ($join) {
                /** @var JoinClause $join */
                $join->on('UserAngelTypes.angeltype_id', '=', 'AngelTypes.id')
                    ->whereNotNull('UserAngelTypes.confirm_user_id')
                    ->orWhere('AngelTypes.restricted', '=', '0');
            });
        }

        $users = $query->get();
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
            $index = join('', $usr->attributesToArray());
            foreach ($tokens as $token) {
                $token = trim($token);
                if (!empty($token) && stristr($index, $token)) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                continue;
            }
        }

        $email = $usr->contact->email ?: $usr->email;
        $free_users_table[] = [
            'name'        => User_Nick_render($usr) . User_Pronoun_render($usr),
            'shift_state' => User_shift_state_render($usr),
            'last_shift'  => User_last_shift_render($usr),
            'dect'        => sprintf('<a href="tel:%s">%1$s</a>', $usr->contact->dect),
            'email'       => $usr->settings->email_human
                ? sprintf('<a href="email:%s">%1$s</a>', $email)
                : icon('eye-slash'),
            'actions'     =>
                auth()->can('admin_user')
                    ? button(page_link_to('admin_user', ['id' => $usr->id]), __('edit'), 'btn-sm')
                    : ''
        ];
    }
    return page_with_title(admin_free_title(), [
        form([
            div('row', [
                div('col-md-12 form-inline', [
                    div('row', [
                        form_text('search', __('Search'), $search, null, null, null, 'col'),
                        form_select('angeltype', __('Angeltype'), $angel_types, $angelType, '', 'col'),
                        form_submit('submit', __('Search'))
                    ]),
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
