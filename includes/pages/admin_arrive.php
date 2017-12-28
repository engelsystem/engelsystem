<?php

use Engelsystem\Database\DB;

/**
 * @return string
 */
function admin_arrive_title()
{
    return _('Arrived angels');
}

/**
 * @return string
 */
function admin_arrive()
{
    $msg = '';
    $search = '';
    $request = request();

    if ($request->has('search')) {
        $search = strip_request_item('search');
        $search = trim($search);
    }

    if ($request->has('reset') && preg_match('/^\d+$/', $request->input('reset'))) {
        $user_id = $request->input('reset');
        $user_source = User($user_id);
        if ($user_source != null) {
            DB::update('
                UPDATE `User`
                SET `Gekommen`=0, `arrival_date` = NULL
                WHERE `UID`=?
                LIMIT 1
            ', [$user_id]);
            engelsystem_log('User set to not arrived: ' . User_Nick_render($user_source));
            success(_('Reset done. Angel has not arrived.'));
            redirect(user_link($user_source));
        } else {
            $msg = error(_('Angel not found.'), true);
        }
    } elseif ($request->has('arrived') && preg_match('/^\d+$/', $request->input('arrived'))) {
        $user_id = $request->input('arrived');
        $user_source = User($user_id);
        if ($user_source != null) {
            DB::update('
                UPDATE `User`
                SET `Gekommen`=1, `arrival_date`=?
                WHERE `UID`=?
                LIMIT 1
            ', [time(), $user_id]);
            engelsystem_log('User set has arrived: ' . User_Nick_render($user_source));
            success(_('Angel has been marked as arrived.'));
            redirect(user_link($user_source));
        } else {
            $msg = error(_('Angel not found.'), true);
        }
    }

    $users = DB::select('SELECT * FROM `User` ORDER BY `Nick`');
    $arrival_count_at_day = [];
    $planned_arrival_count_at_day = [];
    $planned_departure_count_at_day = [];
    $users_matched = [];
    if ($search == '') {
        $tokens = [];
    } else {
        $tokens = explode(' ', $search);
    }
    foreach ($users as $usr) {
        if (count($tokens) > 0) {
            $match = false;
            $index = join(' ', $usr);
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

        $usr['nick'] = User_Nick_render($usr);
        if ($usr['planned_departure_date'] != null) {
            $usr['rendered_planned_departure_date'] = date('Y-m-d', $usr['planned_departure_date']);
        } else {
            $usr['rendered_planned_departure_date'] = '-';
        }
        $usr['rendered_planned_arrival_date'] = date('Y-m-d', $usr['planned_arrival_date']);
        $usr['rendered_arrival_date'] = $usr['arrival_date'] > 0 ? date('Y-m-d', $usr['arrival_date']) : '-';
        $usr['arrived'] = $usr['Gekommen'] == 1 ? _('yes') : '';
        $usr['actions'] = $usr['Gekommen'] == 1
            ? '<a href="' . page_link_to(
                'admin_arrive',
                ['reset' => $usr['UID'], 'search' => $search]
            ) . '">' . _('reset') . '</a>'
            : '<a href="' . page_link_to(
                'admin_arrive',
                ['arrived' => $usr['UID'], 'search' => $search]
            ) . '">' . _('arrived') . '</a>';

        if ($usr['arrival_date'] > 0) {
            $day = date('Y-m-d', $usr['arrival_date']);
            if (!isset($arrival_count_at_day[$day])) {
                $arrival_count_at_day[$day] = 0;
            }
            $arrival_count_at_day[$day]++;
        }

        if ($usr['planned_arrival_date'] != null) {
            $day = date('Y-m-d', $usr['planned_arrival_date']);
            if (!isset($planned_arrival_count_at_day[$day])) {
                $planned_arrival_count_at_day[$day] = 0;
            }
            $planned_arrival_count_at_day[$day]++;
        }

        if ($usr['planned_departure_date'] != null && $usr['Gekommen'] == 1) {
            $day = date('Y-m-d', $usr['planned_departure_date']);
            if (!isset($planned_departure_count_at_day[$day])) {
                $planned_departure_count_at_day[$day] = 0;
            }
            $planned_departure_count_at_day[$day]++;
        }

        $users_matched[] = $usr;
    }

    ksort($arrival_count_at_day);
    ksort($planned_arrival_count_at_day);
    ksort($planned_departure_count_at_day);

    $arrival_at_day = [];
    $arrival_sum = 0;
    foreach ($arrival_count_at_day as $day => $count) {
        $arrival_sum += $count;
        $arrival_at_day[$day] = [
            'day'   => $day,
            'count' => $count,
            'sum'   => $arrival_sum
        ];
    }

    $planned_arrival_at_day = [];
    $planned_arrival_sum = 0;
    foreach ($planned_arrival_count_at_day as $day => $count) {
        $planned_arrival_sum += $count;
        $planned_arrival_at_day[$day] = [
            'day'   => $day,
            'count' => $count,
            'sum'   => $planned_arrival_sum
        ];
    }

    $planned_departure_at_day = [];
    $planned_departure_sum = 0;
    foreach ($planned_departure_count_at_day as $day => $count) {
        $planned_departure_sum += $count;
        $planned_departure_at_day[$day] = [
            'day'   => $day,
            'count' => $count,
            'sum'   => $planned_departure_sum
        ];
    }

    return page_with_title(admin_arrive_title(), [
        $msg . msg(),
        form([
            form_text('search', _('Search'), $search),
            form_submit('submit', _('Search'))
        ]),
        table([
            'nick'                            => _('Nickname'),
            'rendered_planned_arrival_date'   => _('Planned arrival'),
            'arrived'                         => _('Arrived?'),
            'rendered_arrival_date'           => _('Arrival date'),
            'rendered_planned_departure_date' => _('Planned departure'),
            'actions'                         => ''
        ], $users_matched),
        div('row', [
            div('col-md-4', [
                heading(_('Planned arrival statistics'), 2),
                bargraph('planned_arrives', 'day', [
                    'count' => _('arrived'),
                    'sum'   => _('arrived sum')
                ], [
                    'count' => '#090',
                    'sum'   => '#888'
                ], $planned_arrival_at_day),
                table([
                    'day'   => _('Date'),
                    'count' => _('Count'),
                    'sum'   => _('Sum')
                ], $planned_arrival_at_day)
            ]),
            div('col-md-4', [
                heading(_('Arrival statistics'), 2),
                bargraph('arrives', 'day', [
                    'count' => _('arrived'),
                    'sum'   => _('arrived sum')
                ], [
                    'count' => '#090',
                    'sum'   => '#888'
                ], $arrival_at_day),
                table([
                    'day'   => _('Date'),
                    'count' => _('Count'),
                    'sum'   => _('Sum')
                ], $arrival_at_day)
            ]),
            div('col-md-4', [
                heading(_('Planned departure statistics'), 2),
                bargraph('planned_departures', 'day', [
                    'count' => _('arrived'),
                    'sum'   => _('arrived sum')
                ], [
                    'count' => '#090',
                    'sum'   => '#888'
                ], $planned_departure_at_day),
                table([
                    'day'   => _('Date'),
                    'count' => _('Count'),
                    'sum'   => _('Sum')
                ], $planned_departure_at_day)
            ])
        ])
    ]);
}
