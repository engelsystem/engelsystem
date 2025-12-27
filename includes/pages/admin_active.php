<?php

use Carbon\CarbonInterval;
use Engelsystem\Config\GoodieType;
use Engelsystem\Database\Db;
use Engelsystem\Helpers\Carbon;
use Engelsystem\Helpers\Goodie;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\State;
use Engelsystem\Models\User\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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
    $shift_sum_formula = Goodie::shiftScoreQuery()->getValue(Db::connection()->getQueryGrammar());
    $worklog_sum_formula = Goodie::worklogScoreQuery()->getValue(Db::connection()->getQueryGrammar());
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
                                    SELECT %s * 3600
                                    FROM `worklogs` WHERE `user_id`=`users`.`id`
                                    AND `worked_at` <= NOW()
                                )) AS `shift_length`
                        ',
                        $shift_sum_formula,
                        $worklog_sum_formula
                    )
                )
                ->leftJoin('shift_entries', 'users.id', '=', 'shift_entries.user_id')
                ->leftJoin('shifts', 'shift_entries.shift_id', '=', 'shifts.id')
                ->leftJoin('users_state', 'users.id', '=', 'users_state.user_id')
                ->whereNotNull('users_state.arrival_date')
                ->orWhere(function (EloquentBuilder $userinfo) {
                    $userinfo->whereNull('users_state.arrival_date')
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
            foreach ($users as $user) {
                $user->state->active = true;
                $user->state->save();
                $user_nicks[] = User_Nick_render($user, true);
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
                if (
                    $goodie_tshirt
                    && (!$user_source->personalData->shirt_size
                        || !isset($tshirt_sizes[$user_source->personalData->shirt_size])
                    )
                ) {
                    $msg = error(__('Angel has no valid T-shirt size. T-shirt was not set.'), true);
                } else {
                    $user_source->state->got_goodie = true;
                    $user_source->state->save();
                    engelsystem_log('User ' . User_Nick_render($user_source, true) . ' has goodie now.');
                    $msg = success(
                        __('Angel got a goodie.'),
                        true
                    );
                }
            } else {
                $msg = error('Angel not found.', true);
            }
        } elseif ($request->has('not_tshirt') && preg_match('/^\d+$/', $request->input('not_tshirt'))) {
            $user_id = $request->input('not_tshirt');
            $user_source = User::find($user_id);
            if ($user_source) {
                $user_source->state->got_goodie = false;
                $user_source->state->save();
                engelsystem_log('User ' . User_Nick_render($user_source, true) . ' has NO goodie.');
                $msg = success(
                    __('Angel got no goodie.'),
                    true
                );
            } else {
                $msg = error(__('Angel not found.'), true);
            }
        }
    }

    $query = User::with(['personalData', 'state', 'worklogs', 'shiftEntries'])
        ->selectRaw(
            sprintf(
                '
                    users.*,
                    COUNT(shift_entries.id) AS shift_count,
                        (%s + (
                            SELECT %s * 3600
                            FROM `worklogs` WHERE `user_id`=`users`.`id`
                            AND `worked_at` <= NOW()
                        )) AS `shift_length`
                ',
                $shift_sum_formula,
                $worklog_sum_formula
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
        ->whereNotNull('users_state.arrival_date')
        ->orWhere(function (EloquentBuilder $userinfo) {
            $userinfo->whereNull('users_state.arrival_date')
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
    foreach ($users as $user) {
        if (count($tokens) > 0) {
            $match = false;
            foreach ($tokens as $t) {
                $t = trim($t);
                if (!empty($t) && stristr($user->name, $t)) {
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
        $shiftEntries = $user->shiftEntries
            ->load('shift');
        foreach ($shiftEntries as $entry) {
            if ($entry->freeloaded_by || $entry->shift->start > Carbon::now()) {
                continue;
            }
            $timeSum += ($entry->shift->end->timestamp - $entry->shift->start->timestamp);
        }
        foreach ($user->worklogs as $worklog) {
            $timeSum += $worklog->hours * 3600;
        }

        $shirtSize = $user->personalData->shirt_size;
        $userData = [];
        $userData['no'] = count($matched_users) + 1;
        $userData['nick'] = User_Nick_render($user) . User_Pronoun_render($user) . user_info_icon($user);
        if ($goodie_tshirt) {
            $userData['shirt_size'] = (isset($tshirt_sizes[$shirtSize]) ? $tshirt_sizes[$shirtSize]
                : '<small><span class="bi bi-info-circle-fill text-info" data-bs-toggle="tooltip" title="'
                . __("This Angel has no valid T-shirt size, therefore it's not possible to hand out a T-shirt.")
                . '"></span></small>'
            );
        }
        $duration_format = __('general.duration');
        $userData['work_time'] = Carbon::formatDuration(CarbonInterval::seconds($timeSum), $duration_format);
        $userData['score'] =  Carbon::formatDuration(
            CarbonInterval::seconds((int) $user['shift_length']),
            $duration_format
        );
        $userData['active'] = icon_bool($user->state->active);
        $userData['force_active'] = icon_bool($user->state->force_active);
        $userData['force_food'] = icon_bool($user->state->force_food);
        $userData['tshirt'] = icon_bool($user->state->got_goodie);
        $userData['shift_count'] = $user['shift_count'];

        $actions = [];
        if (!$user->state->active) {
            $parameters = [
                'active' => $user->id,
                'search' => $search,
            ];
            if ($show_all_shifts) {
                $parameters['show_all_shifts'] = 1;
            }
            $actions[] = form(
                [form_submit(
                    'submit',
                    icon('plus-lg') . __('Set active'),
                    'btn-sm',
                    false,
                    'secondary'
                )],
                url('/admin-active', $parameters),
                false,
                true
            );
        }
        if ($user->state->active) {
            $parametersRemove = [
                'not_active' => $user->id,
                'search' => $search,
            ];
            if ($show_all_shifts) {
                $parametersRemove['show_all_shifts'] = 1;
            }
            $actions[] = form(
                [form_submit(
                    'submit',
                    icon('dash-lg') . __('Remove active'),
                    'btn-sm',
                    false,
                    'secondary'
                )],
                url('/admin-active', $parametersRemove),
                false,
                true
            );
        }
        if (!$user->state->got_goodie) {
            $parametersShirt = [
                'tshirt' => $user->id,
                'search' => $search,
            ];
            if ($show_all_shifts) {
                $parametersShirt['show_all_shifts'] = 1;
            }
            $got_tshirt_disabled = false;
            if (
                $goodie_tshirt
                && (!$user->personalData->shirt_size
                    || !isset($tshirt_sizes[$user->personalData->shirt_size])
                )
            ) {
                $got_tshirt_disabled = true;
            }

            if ($goodie_enabled) {
                $actions[] = !$got_tshirt_disabled ? form(
                    [form_submit(
                        'submit',
                        icon('gift')
                        . __('user.got_goodie'),
                        'btn-sm',
                        false,
                        'secondary'
                    )],
                    url('/admin-active', $parametersShirt),
                    false,
                    true
                ) : '';
            }
        }
        if ($user->state->got_goodie) {
            $parameters = [
                'not_tshirt' => $user->id,
                'search' => $search,
            ];
            if ($show_all_shifts) {
                $parameters['show_all_shifts'] = 1;
            }

            if ($goodie_enabled) {
                $actions[] = form(
                    [form_submit(
                        'submit',
                        icon('gift')
                        . __('Remove goodie'),
                        'btn-sm',
                        false,
                        'secondary'
                    )],
                    url('/admin-active', $parameters),
                    false,
                    true
                );
            }
        }

        if ($goodie_tshirt) {
            $actions[] = button(
                url('/admin/user/' . $user->id . '/goodie'),
                icon('pencil') . __('form.edit'),
                'btn-secondary btn-sm'
            );
        }

        $userData['actions'] = buttons($actions);

        $matched_users[] = $userData;
    }

    $goodie_statistics = [];
    $total = 0;
    if ($goodie_tshirt) {
        foreach (array_keys($tshirt_sizes) as $size) {
            $query = State::query()
                ->leftJoin('users_settings', 'users_state.user_id', '=', 'users_settings.user_id')
                ->leftJoin('users_personal_data', 'users_state.user_id', '=', 'users_personal_data.user_id')
                ->where('users_personal_data.shirt_size', '=', $size)
            ;
            $given = $query->clone()->where('users_state.got_goodie', true)->count();
            $notGiven = $query->clone()->where('users_state.got_goodie', false)->count();

            $totalSum = $given + $notGiven;
            $total += $totalSum;
            $goodie_statistics[] = [
                'size' => $size,
                'given' => $given,
                'total' => $totalSum,
            ];
        }
    }

    $goodie_statistics[] = array_merge(
        ($goodie_tshirt ? ['size' => '<b>' . __('Sum') . '</b>'] : []),
        ['given' => '<b>' . State::whereGotGoodie(true)->count() . '</b>'],
        ['total' => '<b>' . $total . '</b>'],
    );

    return page_with_title(admin_active_title(), [
        form([
            form_text('search', __('Search angel:'), $search),
            form_checkbox('show_all_shifts', __('Show all shifts'), $show_all_shifts),
            form_submit('submit', icon('search') . __('form.search')),
        ], url('/admin-active')),
        $set_active == '' ? form([
            form_text('count', __('How many angels should be active?'), $count ?: $forced_count),
            form_submit('set_active', icon('eye') . __('form.preview'), 'btn-info'),
        ]) : $set_active,
        $msg . msg(),
        table(
            array_merge(
                [
                    'no' => __('No.'),
                    'nick' => __('general.name'),
                ],
                ($goodie_tshirt ? ['shirt_size' => __('Size')] : []),
                [
                    'shift_count' => __('general.shifts'),
                    'work_time' => __('Length'),
                ],
                ($goodie_enabled ? ['score' => __('Goodie score')] : []),
                [
                    'active' => __('Active'),
                ],
                (config('enable_force_active') ? ['force_active' => __('Forced'),] : []),
                ($goodie_enabled ? ['tshirt' => __('Goodie')] : []),
                [
                    'actions' => __('general.actions'),
                ]
            ),
            $matched_users
        ),
        $goodie_enabled ? '<h2>' . __('Goodie statistic') . '</h2>' : '',
        $goodie_enabled ? table(array_merge(
            ($goodie_tshirt ? ['size' => __('Size')] : []),
            ['given' => __('Given goodies')],
            $goodie_tshirt ? ['total' => __('Configured T-shirts')] : [],
        ), $goodie_statistics) : '',
    ]);
}
