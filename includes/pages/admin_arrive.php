<?php

use Engelsystem\Helpers\BarChart;
use Engelsystem\Models\User\User;
use Illuminate\Support\Str;

/**
 * @return string
 */
function admin_arrive_title()
{
    return auth()->can('admin_arrive') ? __('Arrive angels') : __('Angels');
}

/**
 * @return string
 */
function admin_arrive()
{
    $msg = '';
    $search = '';
    $request = request();
    $admin_arrive = auth()->can('admin_arrive');

    $exactSearch = $request->has('exact');
    if ($request->has('search')) {
        $search = strip_request_item('search');
        $search = trim($search);
    }

    if ($admin_arrive) {
        $action = $request->get('action');
        if (
            $action == 'reset'
            && preg_match('/^\d+$/', $request->input('user'))
            && $request->hasPostData('submit')
        ) {
            $user_id = $request->input('user');
            $user_source = User::find($user_id);
            if ($user_source) {
                $user_source->state->arrival_date = null;
                $user_source->state->save();

                engelsystem_log('User set to not arrived: ' . User_Nick_render($user_source, true));
                success(__('Reset done. Angel has not arrived.'));

                throw_redirect(back()->getHeaderLine('location'));
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
                $user_source->state->arrival_date = new Carbon\Carbon();
                $user_source->state->save();

                engelsystem_log('User set as arrived: ' . User_Nick_render($user_source, true));
                success(__('Angel has been marked as arrived.'));

                throw_redirect(back()->getHeaderLine('location'));
            } else {
                $msg = error(__('Angel not found.'), true);
            }
        }
    }

    /** @var User[] $users */
    $users = User::with(['personalData', 'state'])->orderBy('name')->get();
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
            if ($exactSearch && Str::lower($usr->name) != Str::lower(implode(' ', $tokens))) {
                continue;
            }

            if (!$exactSearch) {
                $match = false;
                $data = collect($usr->toArray())->flatten()->filter(function ($value) {
                    // Remove empty values
                    return !empty($value) &&
                        // Skip datetime
                        !preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d{6}Z$/', (string) $value);
                });
                $index = join(' ', $data->toArray());
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
        }

        $usr->name = User_Nick_render($usr)
            . User_Pronoun_render($usr)
            . user_info_icon($usr);
        $plannedDepartureDate = $usr->personalData->planned_departure_date;
        $arrivalDate = $usr->state->arrival_date;
        $plannedArrivalDate = $usr->personalData->planned_arrival_date;
        $usr['rendered_planned_departure_date'] = $plannedDepartureDate
            ? $plannedDepartureDate->format(__('general.date'))
            : '-';
        $usr['rendered_planned_arrival_date'] = $plannedArrivalDate ? $plannedArrivalDate->format(__('general.date')) : '-';
        $usr['rendered_arrival_date'] = $arrivalDate ? $arrivalDate->format(__('general.date')) : '-';
        $usr['arrived'] = icon_bool($usr->state->arrived);

        $arrive_template = <<<EOT
                <button
                    class="btn btn-sm {type} ms-2 js-only {modal}"
                    data-arrive-action="{action}"
                    data-arrive-user-id="{user}"
                    title="{title}"
                    data-confirm_submit_title="{confirm_submit_title}"
                    data-confirm_button_text="{confirm_button_text}"
                    data-modal-show="{modal}"
                    data-arrive-title="{arrive_title}"
                    data-reset-title="{reset_title}"
                >
                    {icon}
                </button>
        EOT;
        $arrive_attributes = ['{action}' => 'arrive',
            '{user}' => $usr->id,
            '{icon}' =>  icon('house', 'pointer-events: none'),
            '{type}' =>  'btn-primary',
            '{title}' => __('user.arrive'),
            '{confirm_submit_title}' => htmlspecialchars(__('Reset arrival state for %s?', [$usr->name])),
            '{confirm_button_text}' => __('Reset'),
            '{modal}' => false,
            '{arrive_title}' => __('user.arrive'),
            '{reset_title}' => __('Reset'),
            ];
        $reset_attributes = ['{action}' => '',
            '{user}' => $usr->id,
            '{icon}' =>  icon('arrow-counterclockwise', 'pointer-events: none'),
            '{type}' =>  'btn-danger',
            '{title}' => __('Reset'),
            '{confirm_submit_title}' => htmlspecialchars(__('Reset arrival state for %s?', [$usr->name])),
            '{confirm_button_text}' => __('Reset'),
            '{modal}' => true,
            '{arrive_title}' => __('user.arrive'),
            '{reset_title}' => __('Reset'),
            ];

        $arrive_button = strtr($arrive_template, $usr->state->arrived ? $reset_attributes : $arrive_attributes);
        $usr['actions'] = $arrive_button;

        if ($usr->state->arrival_date) {
            $day = $usr->state->arrival_date->format('Y-m-d');
            if (!isset($arrival_count_at_day[$day])) {
                $arrival_count_at_day[$day] = [
                    'day'   => $usr->state->arrival_date,
                    'count' => 0,
                ];
            }
            $arrival_count_at_day[$day]['count']++;
        }

        if ($usr->personalData->planned_arrival_date) {
            $day = $usr->personalData->planned_arrival_date->format('Y-m-d');
            if (!isset($planned_arrival_count_at_day[$day])) {
                $planned_arrival_count_at_day[$day] = [
                    'day'   => $usr->personalData->planned_arrival_date,
                    'count' => 0,
                ];
            }
            $planned_arrival_count_at_day[$day]['count']++;
        }

        if ($usr->personalData->planned_departure_date && $usr->state->arrived) {
            $day = $usr->personalData->planned_departure_date->format('Y-m-d');
            if (!isset($planned_departure_count_at_day[$day])) {
                $planned_departure_count_at_day[$day] = [
                    'day'   => $usr->personalData->planned_departure_date,
                    'count' => 0,
                ];
            }
            $planned_departure_count_at_day[$day]['count']++;
        }

        $users_matched[] = $usr;
    }

    ksort($arrival_count_at_day);
    ksort($planned_arrival_count_at_day);
    ksort($planned_departure_count_at_day);

    $arrival_at_day = [];
    $arrival_sum = 0;
    foreach ($arrival_count_at_day as $day => $entry) {
        $arrival_sum += $entry['count'];
        $arrival_at_day[$day] = [
            'day'   => $entry['day']->format(__('general.date')),
            'count' => $entry['count'],
            'sum'   => $arrival_sum,
        ];
    }

    $planned_arrival_at_day = [];
    $planned_arrival_sum = 0;
    foreach ($planned_arrival_count_at_day as $day => $entry) {
        $planned_arrival_sum += $entry['count'];
        $planned_arrival_at_day[$day] = [
            'day'   => $entry['day']->format(__('general.date')),
            'count' => $entry['count'],
            'sum'   => $planned_arrival_sum,
        ];
    }

    $planned_departure_at_day = [];
    $planned_departure_sum = 0;
    foreach ($planned_departure_count_at_day as $day => $entry) {
        $planned_departure_sum += $entry['count'];
        $planned_departure_at_day[$day] = [
            'day'   => $entry['day']->format(__('general.date')),
            'count' => $entry['count'],
            'sum'   => $planned_departure_sum,
        ];
    }

    return page_with_title(admin_arrive_title(), [
        $msg . msg(),
        form([
            form_text('search', __('form.search'), $search),
            div('row mb-3 align-items-center', [
                div('col-sm-auto', [form_submit('submit', icon('search') . __('form.search'), '', false)]),
                div('col', [form_checkbox('exact', __('form.exact_match'), $exactSearch)]),
            ]),
        ], url('/admin-arrive')),
        table(array_merge(
            ['name' => __('general.name'),],
            ($admin_arrive ? ['rendered_planned_arrival_date' => __('Planned arrival')] : []),
            ['arrived' => __('Arrived')],
            ($admin_arrive ? [
                'rendered_arrival_date' => __('Arrival date'),
                'rendered_planned_departure_date' => __('Planned departure'),
                'actions' => __('general.actions'),
            ] : [])
        ), $users_matched),
        div('row', $admin_arrive ? [
            div('col-md-4', [
                heading(__('Planned arrival statistics'), 3),
                BarChart::render([
                    'count' => __('user.arrived'),
                    'sum'   => __('arrived sum'),
                ], [
                    'count' => '#090',
                    'sum'   => '#888',
                ], $planned_arrival_at_day),
                table([
                    'day'   => __('title.date'),
                    'count' => __('general.count'),
                    'sum'   => __('Sum'),
                ], $planned_arrival_at_day),
            ]),
            div('col-md-4', [
                heading(__('Arrival statistics'), 3),
                BarChart::render([
                    'count' => __('user.arrived'),
                    'sum'   => __('arrived sum'),
                ], [
                    'count' => '#090',
                    'sum'   => '#888',
                ], $arrival_at_day),
                table([
                    'day'   => __('title.date'),
                    'count' => __('general.count'),
                    'sum'   => __('Sum'),
                ], $arrival_at_day),
            ]),
            div('col-md-4', [
                heading(__('Planned departure statistics'), 3),
                BarChart::render([
                    'count' => __('user.arrived'),
                    'sum'   => __('arrived sum'),
                ], [
                    'count' => '#090',
                    'sum'   => '#888',
                ], $planned_departure_at_day),
                table([
                    'day'   => __('title.date'),
                    'count' => __('general.count'),
                    'sum'   => __('Sum'),
                ], $planned_departure_at_day),
            ]),
        ] : []),
    ]);
}
