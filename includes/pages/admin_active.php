<?php

use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;

/**
 * @return string
 */
function admin_active_title()
{
    return __('Active angels');
}

/**
 * @return string
 */
function admin_active()
{
    $tshirt_sizes = config('tshirt_sizes');
    $shift_sum_formula = User_get_shifts_sum_query();
    $request = request();

    $msg = '';
    $search = '';
    $forced_count = State::whereForceActive(true)->count();
    $count = null;
    $set_active = '';

    if ($request->has('search')) {
        $search = strip_request_item('search');
    }

    $show_all_shifts = $request->has('show_all_shifts');

    if ($request->has('set_active')) {
        if ($request->has('count') && preg_match('/^\d+$/', $request->input('count'))) {
            $count = strip_request_item('count');
            if ($count < $forced_count) {
                error(sprintf(
                    __('At least %s angels are forced to be active. The number has to be greater.'),
                    $forced_count
                ));
                throw_redirect(page_link_to('admin_active'));
            }
        } else {
            $msg .= error(__('Please enter a number of angels to be marked as active.'));
            throw_redirect(page_link_to('admin_active'));
        }

        if ($request->hasPostData('ack')) {
            State::query()
                ->where('got_shirt', '=', false)
                ->update(['active' => false]);

            $query = User::query()
                ->selectRaw(
                    sprintf(
                        '
                            users.*,
                            COUNT(ShiftEntry.id) AS shift_count,
                                (%s + (
                                    SELECT COALESCE(SUM(`hours`) * 3600, 0)
                                    FROM `worklogs` WHERE `user_id`=`users`.`id`
                                    AND `worked_at` <= NOW()
                                )) AS `shift_length`
                        ',
                        $shift_sum_formula
                    )
                )
                ->leftJoin('ShiftEntry', 'users.id', '=', 'ShiftEntry.UID')
                ->leftJoin('Shifts', 'ShiftEntry.SID', '=', 'Shifts.SID')
                ->leftJoin('users_state', 'users.id', '=', 'users_state.user_id')
                ->where('users_state.arrived', '=', true)
                ->groupBy('users.id')
                ->orderByDesc('force_active')
                ->orderByDesc('shift_length')
                ->orderByDesc('name')
                ->limit($count);

            $users = $query->get();
            $user_nicks = [];
            foreach ($users as $usr) {
                $usr->state->active = true;
                $usr->state->save();
                $user_nicks[] = User_Nick_render($usr, true);
            }

            engelsystem_log('These angels are active now: ' . join(', ', $user_nicks));

            $msg = success(__('Marked angels.'), true);
        } else {
            $set_active = form([
                button(page_link_to('admin_active', ['search' => $search]), '&laquo; ' . __('back')),
                form_submit('ack', '&raquo; ' . __('apply')),
            ], page_link_to('admin_active', ['search' => $search, 'count' => $count, 'set_active' => 1]));
        }
    }

    if ($request->hasPostData('submit')) {
        if ($request->has('active') && preg_match('/^\d+$/', $request->input('active'))) {
            $user_id = $request->input('active');
            $user_source = User::find($user_id);
            if ($user_source) {
                $user_source->state->active = true;
                $user_source->state->save();
                engelsystem_log('User ' . User_Nick_render($user_source, true) . ' is active now.');
                $msg = success(__('Angel has been marked as active.'), true);
            } else {
                $msg = error(__('Angel not found.'), true);
            }
        } elseif ($request->has('not_active') && preg_match('/^\d+$/', $request->input('not_active'))) {
            $user_id = $request->input('not_active');
            $user_source = User::find($user_id);
            if ($user_source) {
                $user_source->state->active = false;
                $user_source->state->save();
                engelsystem_log('User ' . User_Nick_render($user_source, true) . ' is NOT active now.');
                $msg = success(__('Angel has been marked as not active.'), true);
            } else {
                $msg = error(__('Angel not found.'), true);
            }
        } elseif ($request->has('tshirt') && preg_match('/^\d+$/', $request->input('tshirt'))) {
            $user_id = $request->input('tshirt');
            $user_source = User::find($user_id);
            if ($user_source) {
                $user_source->state->got_shirt = true;
                $user_source->state->save();
                engelsystem_log('User ' . User_Nick_render($user_source, true) . ' has tshirt now.');
                $msg = success(__('Angel has got a t-shirt.'), true);
            } else {
                $msg = error('Angel not found.', true);
            }
        } elseif ($request->has('not_tshirt') && preg_match('/^\d+$/', $request->input('not_tshirt'))) {
            $user_id = $request->input('not_tshirt');
            $user_source = User::find($user_id);
            if ($user_source) {
                $user_source->state->got_shirt = false;
                $user_source->state->save();
                engelsystem_log('User ' . User_Nick_render($user_source, true) . ' has NO tshirt.');
                $msg = success(__('Angel has got no t-shirt.'), true);
            } else {
                $msg = error(__('Angel not found.'), true);
            }
        }
    }

    $query = User::with('personalData')
        ->selectRaw(
            sprintf(
                '
                    users.*,
                    COUNT(ShiftEntry.id) AS shift_count,
                        (%s + (
                            SELECT COALESCE(SUM(`hours`) * 3600, 0)
                            FROM `worklogs` WHERE `user_id`=`users`.`id`
                            AND `worked_at` <= NOW()
                        )) AS `shift_length`
                ',
                $shift_sum_formula
            )
        )
        ->leftJoin('ShiftEntry', 'users.id', '=', 'ShiftEntry.UID')
        ->leftJoin('Shifts', function ($join) use ($show_all_shifts) {
            /** @var JoinClause $join */
            $join->on('ShiftEntry.SID', '=', 'Shifts.SID');
            if (!$show_all_shifts) {
                $join->where(function ($query) {
                    /** @var Builder $query */
                    $query->where('Shifts.end', '<', time())
                        ->orWhereNull('Shifts.end');
                });
            }
        })
        ->leftJoin('users_state', 'users.id', '=', 'users_state.user_id')
        ->where('users_state.arrived', '=', true)
        ->groupBy('users.id')
        ->orderByDesc('force_active')
        ->orderByDesc('shift_length')
        ->orderByDesc('name');

    if (!is_null($count)) {
        $query->limit($count);
    }

    /** @var User[] $users */
    $users = $query->get();
    $matched_users = [];
    if ($search == '') {
        $tokens = [];
    } else {
        $tokens = explode(' ', $search);
    }
    foreach ($users as $usr) {
        if (count($tokens) > 0) {
            $match = false;
            foreach ($tokens as $t) {
                $t = trim($t);
                if (!empty($t) && stristr($usr->name, $t)) {
                    $match = true;
                    break;
                }
            }
            if (!$match) {
                continue;
            }
        }

        $shirtSize = $usr->personalData->shirt_size;
        $userData = [];
        $userData['no'] = count($matched_users) + 1;
        $userData['nick'] = User_Nick_render($usr) . User_Pronoun_render($usr);
        $userData['shirt_size'] = (isset($tshirt_sizes[$shirtSize]) ? $tshirt_sizes[$shirtSize] : '');
        $userData['work_time'] = round($usr['shift_length'] / 60)
            . ' min (' . sprintf('%.2f', $usr['shift_length'] / 3600) . '&nbsp;h)';
        $userData['active'] = icon_bool($usr->state->active == 1);
        $userData['force_active'] = icon_bool($usr->state->force_active == 1);
        $userData['tshirt'] = icon_bool($usr->state->got_shirt == 1);
        $userData['shift_count'] = $usr['shift_count'];

        $actions = [];
        if (!$usr->state->active) {
            $parameters = [
                'active' => $usr->id,
                'search' => $search,
            ];
            if ($show_all_shifts) {
                $parameters['show_all_shifts'] = 1;
            }
            $actions[] = form(
                [form_submit('submit', __('set active'), 'btn-sm', false, 'secondary')],
                page_link_to('admin_active', $parameters), false, true
            );
        }
        if ($usr->state->active) {
            $parametersRemove = [
                'not_active' => $usr->id,
                'search'     => $search,
            ];
            if ($show_all_shifts) {
                $parametersRemove['show_all_shifts'] = 1;
            }
            $actions[] = form(
                [form_submit('submit', __('remove active'), 'btn-sm', false, 'secondary')],
                page_link_to('admin_active', $parametersRemove), false, true
            );
        }
        if (!$usr->state->got_shirt) {
            $parametersShirt = [
                'tshirt' => $usr->id,
                'search' => $search,
            ];
            if ($show_all_shifts) {
                $parametersShirt['show_all_shifts'] = 1;
            }
            $actions[] = form(
                [form_submit('submit', __('got t-shirt'), 'btn-sm', false, 'secondary')],
                page_link_to('admin_active', $parametersShirt), false, true
            );
        }
        if ($usr->state->got_shirt) {
            $parameters = [
                'not_tshirt' => $usr->id,
                'search'     => $search,
            ];
            if ($show_all_shifts) {
                $parameters['show_all_shifts'] = 1;
            }
            $actions[] = form(
                [form_submit('submit', __('remove t-shirt'), 'btn-sm', false, 'secondary')],
                page_link_to('admin_active', $parameters), false, true
            );
        }

        $actions[] = button(url('/admin/user/' . $usr->id . '/shirt'), __('form.edit'), 'btn-secondary btn-sm');

        $userData['actions'] = buttons($actions);

        $matched_users[] = $userData;
    }

    $shirt_statistics = [];
    foreach (array_keys($tshirt_sizes) as $size) {
        $gc = State::query()
            ->leftJoin('users_settings', 'users_state.user_id', '=', 'users_settings.user_id')
            ->leftJoin('users_personal_data', 'users_state.user_id', '=', 'users_personal_data.user_id')
            ->where('users_state.got_shirt', '=', true)
            ->where('users_personal_data.shirt_size', '=', $size)
            ->count();
        $shirt_statistics[] = [
            'size'  => $size,
            'given' => $gc
        ];
    }

    $shirt_statistics[] = [
        'size'  => '<b>' . __('Sum') . '</b>',
        'given' => '<b>' . State::whereGotShirt(true)->count() . '</b>'
    ];

    return page_with_title(admin_active_title(), [
        form([
            form_text('search', __('Search angel:'), $search),
            form_checkbox('show_all_shifts', __('Show all shifts'), $show_all_shifts),
            form_submit('submit', __('Search'))
        ], page_link_to('admin_active')),
        $set_active == '' ? form([
            form_text('count', __('How much angels should be active?'), $count ?: $forced_count),
            form_submit('set_active', __('Preview'))
        ]) : $set_active,
        $msg . msg(),
        table([
            'no'           => __('No.'),
            'nick'         => __('Nickname'),
            'shirt_size'   => __('Size'),
            'shift_count'  => __('Shifts'),
            'work_time'    => __('Length'),
            'active'       => __('Active?'),
            'force_active' => __('Forced'),
            'tshirt'       => __('T-shirt?'),
            'actions'      => ''
        ], $matched_users),
        '<h2>' . __('Shirt statistics') . '</h2>',
        table([
            'size'  => __('Size'),
            'given' => __('Given shirts')
        ], $shirt_statistics)
    ]);
}
