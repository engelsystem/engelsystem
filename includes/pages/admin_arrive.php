<?php

use Engelsystem\Helpers\BarChart;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function admin_arrive_title()
{
    return __('Arrive angels');
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

    $action = $request->get('action');
    if (
        $action == 'reset'
        && preg_match('/^\d+$/', $request->input('user'))
        && $request->hasPostData('submit')
    ) {
        $user_id = $request->input('user');
        $user_source = User::find($user_id);
        if ($user_source) {
            $user_source->state->arrived = false;
            $user_source->state->arrival_date = null;
            $user_source->state->save();

            engelsystem_log('User set to not arrived: ' . User_Nick_render($user_source, true));
            success(__('Reset done. Angel has not arrived.'));
            throw_redirect(user_link($user_source->id));
        } else {
            $msg = error(__('Angel not found.'), true);
        }
    } elseif (
        $action == 'arrived'
        && preg_match('/^\d+$/', $request->input('user'))
        && $request->hasPostData('submit')
    ) {
        $user_id = $request->input('user');
        $user_source = User::find($user_id);
        if ($user_source) {
            $user_source->state->arrived = true;
            $user_source->state->arrival_date = new Carbon\Carbon();
            $user_source->state->save();

            engelsystem_log('User set has arrived: ' . User_Nick_render($user_source, true));
            success(__('Angel has been marked as arrived.'));
            throw_redirect(user_link($user_source->id));
        } else {
            $msg = error(__('Angel not found.'), true);
        }
    }

    /** @var User[] $users */
    $users = User::with('personalData')->orderBy('name')->get();
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
            $index = join(' ', $usr->attributesToArray());
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

        $usr->name = User_Nick_render($usr) . User_Pronoun_render($usr);
        $plannedDepartureDate = $usr->personalData->planned_departure_date;
        $arrivalDate = $usr->state->arrival_date;
        $plannedArrivalDate = $usr->personalData->planned_arrival_date;
        $usr['rendered_planned_departure_date'] = $plannedDepartureDate
            ? $plannedDepartureDate->format(__('Y-m-d'))
            : '-';
        $usr['rendered_planned_arrival_date'] = $plannedArrivalDate ? $plannedArrivalDate->format(__('Y-m-d')) : '-';
        $usr['rendered_arrival_date'] = $arrivalDate ? $arrivalDate->format(__('Y-m-d')) : '-';
        $usr['arrived'] = icon_bool($usr->state->arrived);
        $usr['actions'] = form([
            form_hidden('action', $usr->state->arrived ? 'reset' : 'arrived'),
            form_hidden('user', $usr->id),
            form_submit(
                'submit',
                $usr->state->arrived ? __('reset') : __('arrived'),
                'btn-sm',
                true,
                $usr->state->arrived ? 'secondary' : 'primary'
            ),
        ]);

        if ($usr->state->arrival_date) {
            $day = $usr->state->arrival_date->format('Y-m-d');
            if (!isset($arrival_count_at_day[$day])) {
                $arrival_count_at_day[$day] = 0;
            }
            $arrival_count_at_day[$day]++;
        }

        if ($usr->personalData->planned_arrival_date) {
            $day = $usr->personalData->planned_arrival_date->format('Y-m-d');
            if (!isset($planned_arrival_count_at_day[$day])) {
                $planned_arrival_count_at_day[$day] = 0;
            }
            $planned_arrival_count_at_day[$day]++;
        }

        if ($usr->personalData->planned_departure_date && $usr->state->arrived) {
            $day = $usr->personalData->planned_departure_date->format('Y-m-d');
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
            form_text('search', __('Search'), $search),
            form_submit('submit', __('Search'))
        ], page_link_to('admin_arrive')),
        table([
            'name'                            => __('Nickname'),
            'rendered_planned_arrival_date'   => __('Planned arrival'),
            'arrived'                         => __('Arrived?'),
            'rendered_arrival_date'           => __('Arrival date'),
            'rendered_planned_departure_date' => __('Planned departure'),
            'actions'                         => ''
        ], $users_matched),
        div('row', [
            div('col-md-4', [
                heading(__('Planned arrival statistics'), 3),
                BarChart::render([
                    'count' => __('arrived'),
                    'sum'   => __('arrived sum')
                ], [
                    'count' => '#090',
                    'sum'   => '#888'
                ], $planned_arrival_at_day),
                table([
                    'day'   => __('Date'),
                    'count' => __('Count'),
                    'sum'   => __('Sum')
                ], $planned_arrival_at_day)
            ]),
            div('col-md-4', [
                heading(__('Arrival statistics'), 3),
                BarChart::render([
                    'count' => __('arrived'),
                    'sum'   => __('arrived sum')
                ], [
                    'count' => '#090',
                    'sum'   => '#888'
                ], $arrival_at_day),
                table([
                    'day'   => __('Date'),
                    'count' => __('Count'),
                    'sum'   => __('Sum')
                ], $arrival_at_day)
            ]),
            div('col-md-4', [
                heading(__('Planned departure statistics'), 3),
                BarChart::render([
                    'count' => __('arrived'),
                    'sum'   => __('arrived sum')
                ], [
                    'count' => '#090',
                    'sum'   => '#888'
                ], $planned_departure_at_day),
                table([
                    'day'   => __('Date'),
                    'count' => __('Count'),
                    'sum'   => __('Sum')
                ], $planned_departure_at_day)
            ])
        ])
    ]);
}
