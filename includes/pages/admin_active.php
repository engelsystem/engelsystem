<?php

use Engelsystem\Helpers\Carbon;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Engelsystem\Config\GoodieType;

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
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;

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
            if ($count < $forced_count && config('enable_force_active')) {
                error(sprintf(
                    __('At least %s angels are forced to be active. The number has to be greater.'),
                    $forced_count
                ));
                throw_redirect(url('/admin-active'));
            }
        } else {
            $msg .= error(__('Please enter a number of angels to be marked as active.'));
            throw_redirect(url('/admin-active'));
        }

        if ($request->hasPostData('ack')) {
            State::query()
                ->where('got_goodie', '=', false)
                ->update(['active' => false]);

            $query = User::query()
                ->selectRaw(
                    sprintf(
                        '
                            users.*,
                            COUNT(shift_entries.id) AS shift_count,
                                (%s + (
                                    SELECT COALESCE(SUM(`hours`) * 3600, 0)
                                    FROM `worklogs` WHERE `user_id`=`users`.`id`
                                    AND `worked_at` <= NOW()
                                )) AS `shift_length`
                        ',
                        $shift_sum_formula
                    )
                )
                ->leftJoin('shift_entries', 'users.id', '=', 'shift_entries.user_id')
                ->leftJoin('shifts', 'shift_entries.shift_id', '=', 'shifts.id')
                ->leftJoin('users_state', 'users.id', '=', 'users_state.user_id')
                ->where('users_state.arrived', '=', true)
                ->orWhere(function (EloquentBuilder $userinfo) {
                    $userinfo->where('users_state.arrived', '=', false)
                        ->whereNotNull('users_state.user_info')
                        ->whereNot('users_state.user_info', '');
                })
                ->groupBy('users.id');
            if (config('enable_force_active')) {
                $query->orderByDesc('force_active');
            }
            $query
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
                button(url('/admin-active', ['search' => $search]), '&laquo; ' . __('general.back')),
                form_submit('ack', '&raquo; ' . __('Apply')),
            ], url('/admin-active', ['search' => $search, 'count' => $count, 'set_active' => 1]));
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
                $user_source->state->got_goodie = true;
                $user_source->state->save();
                engelsystem_log('User ' . User_Nick_render($user_source, true) . ' has tshirt now.');
                $msg = success(($goodie_tshirt ? __('Angel has got a T-shirt.') : __('Angel has got a goodie.')), true);
            } else {
                $msg = error('Angel not found.', true);
            }
        } elseif ($request->has('not_tshirt') && preg_match('/^\d+$/', $request->input('not_tshirt'))) {
            $user_id = $request->input('not_tshirt');
            $user_source = User::find($user_id);
            if ($user_source) {
                $user_source->state->got_goodie = false;
                $user_source->state->save();
                engelsystem_log('User ' . User_Nick_render($user_source, true) . ' has NO tshirt.');
                $msg = success(($goodie_tshirt ? __('Angel has got no T-shirt.') : __('Angel has got no goodie.')), true);
            } else {
                $msg = error(__('Angel not found.'), true);
            }
        }
    }

    $query = User::with(['personalData', 'state', 'worklogs'])
        ->selectRaw(
            sprintf(
                '
                    users.*,
                    COUNT(shift_entries.id) AS shift_count,
                        (%s + (
                            SELECT COALESCE(SUM(`hours`) * 3600, 0)
                            FROM `worklogs` WHERE `user_id`=`users`.`id`
                            AND `worked_at` <= NOW()
                        )) AS `shift_length`
                ',
                $shift_sum_formula
            )
        )
        ->leftJoin('shift_entries', 'users.id', '=', 'shift_entries.user_id')
        ->leftJoin('shifts', function ($join) use ($show_all_shifts) {
            /** @var JoinClause $join */
            $join->on('shift_entries.shift_id', '=', 'shifts.id');
            if (!$show_all_shifts) {
                $join->where(function ($query) {
                    /** @var Builder $query */
                    $query->where('shifts.end', '<', Carbon::now())
                        ->orWhereNull('shifts.end');
                });
            }
        })
        ->leftJoin('users_state', 'users.id', '=', 'users_state.user_id')
        ->where('users_state.arrived', '=', true)
        ->orWhere(function (EloquentBuilder $userinfo) {
            $userinfo->where('users_state.arrived', '=', false)
                ->whereNotNull('users_state.user_info')
                ->whereNot('users_state.user_info', '');
        })
        ->groupBy('users.id');
    if (config('enable_force_active')) {
        $query->orderByDesc('force_active');
    }
    $query
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

        $timeSum = 0;
        /** @var ShiftEntry[] $shiftEntries */
        $shiftEntries = $usr->shiftEntries()
            ->with('shift')
            ->get();
        foreach ($shiftEntries as $entry) {
            if ($entry->freeloaded || $entry->shift->start > Carbon::now()) {
                continue;
            }
            $timeSum += ($entry->shift->end->timestamp - $entry->shift->start->timestamp);
        }
        foreach ($usr->worklogs as $worklog) {
            $timeSum += $worklog->hours * 3600;
        }

        $shirtSize = $usr->personalData->shirt_size;
        $userData = [];
        $userData['no'] = count($matched_users) + 1;
        $userData['nick'] = User_Nick_render($usr) . User_Pronoun_render($usr) . user_info_icon($usr);
        if ($goodie_tshirt) {
            $userData['shirt_size'] = (isset($tshirt_sizes[$shirtSize]) ? $tshirt_sizes[$shirtSize] : '');
        }
        $userData['work_time'] = sprintf('%.2f', round($timeSum / 3600, 2)) . '&nbsp;h';
        $userData['score'] = round($usr['shift_length'] / 60)
            . ' min (' . sprintf('%.2f', $usr['shift_length'] / 3600) . '&nbsp;h)';
        $userData['active'] = icon_bool($usr->state->active);
        $userData['force_active'] = icon_bool($usr->state->force_active);
        $userData['tshirt'] = icon_bool($usr->state->got_goodie);
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
                [form_submit('submit', icon('plus-lg') . __('set active'), 'btn-sm', false, 'secondary')],
                url('/admin-active', $parameters),
                false,
                true
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
                [form_submit('submit', icon('dash-lg') . __('Remove active'), 'btn-sm', false, 'secondary')],
                url('/admin-active', $parametersRemove),
                false,
                true
            );
        }
        if (!$usr->state->got_goodie) {
            $parametersShirt = [
                'tshirt' => $usr->id,
                'search' => $search,
            ];
            if ($show_all_shifts) {
                $parametersShirt['show_all_shifts'] = 1;
            }

            if ($goodie_enabled) {
                $actions[] = form(
                    [form_submit('submit', icon('person') . ($goodie_tshirt ? __('Got T-shirt') : __('Got goodie')), 'btn-sm', false, 'secondary')],
                    url('/admin-active', $parametersShirt),
                    false,
                    true
                );
            }
        }
        if ($usr->state->got_goodie) {
            $parameters = [
                'not_tshirt' => $usr->id,
                'search'     => $search,
            ];
            if ($show_all_shifts) {
                $parameters['show_all_shifts'] = 1;
            }

            if ($goodie_enabled) {
                $actions[] = form(
                    [form_submit('submit', icon('person') . ($goodie_tshirt ? __('Remove T-shirt') : __('Remove goodie')), 'btn-sm', false, 'secondary')],
                    url('/admin-active', $parameters),
                    false,
                    true
                );
            }
        }

        if ($goodie_tshirt) {
            $actions[] = button(url('/admin/user/' . $usr->id . '/goodie'), icon('pencil') . __('form.edit'), 'btn-secondary btn-sm');
        }

        $userData['actions'] = buttons($actions);

        $matched_users[] = $userData;
    }

    $goodie_statistics = [];
    if ($goodie_tshirt) {
        foreach (array_keys($tshirt_sizes) as $size) {
            $gc = State::query()
                ->leftJoin('users_settings', 'users_state.user_id', '=', 'users_settings.user_id')
                ->leftJoin('users_personal_data', 'users_state.user_id', '=', 'users_personal_data.user_id')
                ->where('users_state.got_goodie', '=', true)
                ->where('users_personal_data.shirt_size', '=', $size)
                ->count();
            $goodie_statistics[] = [
                'size'  => $size,
                'given' => $gc,
            ];
        }
    }

    $goodie_statistics[] = array_merge(
        ($goodie_tshirt ? ['size'  => '<b>' . __('Sum') . '</b>'] : []),
        ['given' => '<b>' . State::whereGotGoodie(true)->count() . '</b>']
    );

    return page_with_title(admin_active_title(), [
        form([
            form_text('search', __('Search angel:'), $search),
            form_checkbox('show_all_shifts', __('Show all shifts'), $show_all_shifts),
            form_submit('submit', icon('search') . __('form.search')),
        ], url('/admin-active')),
        $set_active == '' ? form([
            form_text('count', __('How many angels should be active?'), $count ?: $forced_count),
            form_submit('set_active', icon('eye') .  __('form.preview'), 'btn-info'),
        ]) : $set_active,
        $msg . msg(),
        table(
            array_merge(
                [
                    'no'           => __('No.'),
                    'nick'         => __('general.name'),
                ],
                ($goodie_tshirt ? ['shirt_size'   => __('Size')] : []),
                [
                    'shift_count'  => __('general.shifts'),
                    'work_time'    => __('Length'),
                ],
                ($goodie_enabled ? ['score'   => ($goodie_tshirt
                    ? __('T-shirt score')
                    : __('Goodie score')
                )] : []),
                [
                    'active'       => __('Active'),
                ],
                (config('enable_force_active') ? ['force_active' => __('Forced'),] : []),
                ($goodie_enabled ? ['tshirt' => ($goodie_tshirt ? __('T-shirt') : __('Goodie'))] : []),
                [
                    'actions'      => __('general.actions'),
                ]
            ),
            $matched_users
        ),
        $goodie_enabled ? '<h2>' . ($goodie_tshirt ? __('T-shirt statistic') : __('Goodie statistic')) . '</h2>' : '',
        $goodie_enabled ? table(array_merge(
            ($goodie_tshirt ? ['size'  => __('Size')] : []),
            ['given' => $goodie_tshirt ? __('Given T-shirts') : __('Given goodies') ]
        ), $goodie_statistics) : '',
    ]);
}
