<?php

namespace Engelsystem;

use Engelsystem\Models\User\User;

/**
 * Renders a single shift for the shift calendar
 */
class ShiftCalendarShiftRenderer
{
    /**
     * Renders a shift
     *
     * @param array $shift The shift to render
     * @param array $needed_angeltypes
     * @param array $shift_entries
     * @param User  $user  The user who is viewing the shift calendar
     * @return array
     */
    public function render($shift, $needed_angeltypes, $shift_entries, $user)
    {
        $info_text = '';
        if ($shift['title'] != '') {
            $info_text = glyph('info-sign') . $shift['title'] . '<br>';
        }
        list($shift_signup_state, $shifts_row) = $this->renderShiftNeededAngeltypes(
            $shift,
            $needed_angeltypes,
            $shift_entries,
            $user
        );

        $class = $this->classForSignupState($shift_signup_state);

        $blocks = ceil(($shift['end'] - $shift['start']) / ShiftCalendarRenderer::SECONDS_PER_ROW);
        $blocks = max(1, $blocks);

        return [
            $blocks,
            div(
                'shift panel panel-' . $class . '" '
                . 'style="height: '
                . ($blocks * ShiftCalendarRenderer::BLOCK_HEIGHT - ShiftCalendarRenderer::MARGIN)
                . 'px"',
                [
                    $this->renderShiftHead($shift, $class),
                    div('panel-body', [
                        $info_text,
                        Room_name_render([
                            'RID'  => $shift['RID'],
                            'Name' => $shift['room_name']
                        ])
                    ]),
                    $shifts_row,
                    div('shift-spacer')
                ]
            )
        ];
    }

    /**
     * @param ShiftSignupState $shiftSignupState
     * @return string
     */
    private function classForSignupState(ShiftSignupState $shiftSignupState)
    {
        switch ($shiftSignupState->getState()) {
            case ShiftSignupState::ADMIN:
            case ShiftSignupState::OCCUPIED:
                return 'success';

            case ShiftSignupState::SIGNED_UP:
                return 'primary';

            case ShiftSignupState::NOT_ARRIVED:
            case ShiftSignupState::SHIFT_ENDED:
                return 'default';

            case ShiftSignupState::ANGELTYPE:
            case ShiftSignupState::COLLIDES:
                return 'warning';

            case ShiftSignupState::FREE:
                return 'danger';
            default:
                return '';
        }
    }

    /**
     * @param array   $shift
     * @param array[] $needed_angeltypes
     * @param array[] $shift_entries
     * @param User    $user
     * @return array
     */
    private function renderShiftNeededAngeltypes($shift, $needed_angeltypes, $shift_entries, $user)
    {
        $shift_entries_filtered = [];
        foreach ($needed_angeltypes as $needed_angeltype) {
            $shift_entries_filtered[$needed_angeltype['id']] = [];
        }
        foreach ($shift_entries as $shift_entry) {
            $shift_entries_filtered[$shift_entry['TID']][] = $shift_entry;
        }

        $html = '';
        /** @var ShiftSignupState $shift_signup_state */
        $shift_signup_state = null;
        foreach ($needed_angeltypes as $angeltype) {
            if ($angeltype['count'] > 0 || count($shift_entries_filtered[$angeltype['id']]) > 0) {
                list($angeltype_signup_state, $angeltype_html) = $this->renderShiftNeededAngeltype(
                    $shift,
                    $shift_entries_filtered[$angeltype['id']],
                    $angeltype,
                    $user
                );
                if (is_null($shift_signup_state)) {
                    $shift_signup_state = $angeltype_signup_state;
                } else {
                    $shift_signup_state->combineWith($angeltype_signup_state);
                }
                $html .= $angeltype_html;
            }
        }
        if (is_null($shift_signup_state)) {
            $shift_signup_state = new ShiftSignupState(ShiftSignupState::SHIFT_ENDED, 0);
        }

        if (auth()->can('user_shifts_admin')) {
            $html .= '<li class="list-group-item">';
            $html .= button(shift_entry_create_link_admin($shift),
                glyph('plus') . __('Add more angels'),
                'btn-xs'
            );
            $html .= '</li>';
        }
        if ($html != '') {
            return [
                $shift_signup_state,
                '<ul class="list-group">' . $html . '</ul>'
            ];
        }

        return [
            $shift_signup_state,
            ''
        ];
    }

    /**
     * Renders a list entry containing the needed angels for an angeltype
     *
     * @param array   $shift     The shift which is rendered
     * @param array[] $shift_entries
     * @param array[] $angeltype The angeltype, containing information about needed angeltypes
     *                           and already signed up angels
     * @param User    $user      The user who is viewing the shift calendar
     * @return array
     */
    private function renderShiftNeededAngeltype($shift, $shift_entries, $angeltype, $user)
    {
        $entry_list = [];
        foreach ($shift_entries as $entry) {
            $style = $entry['freeloaded'] ? ' text-decoration: line-through;' : '';
            $entry_list[] = '<span style="' . $style . '">' . User_Nick_render($entry) . '</span>';
        }
        $shift_signup_state = Shift_signup_allowed(
            $user,
            $shift,
            $angeltype,
            null,
            null,
            $angeltype,
            $shift_entries
        );
        $freeEntriesCount = $shift_signup_state->getFreeEntries();
        $inner_text = _e('%d helper needed', '%d helpers needed', $freeEntriesCount, [$freeEntriesCount]);

        switch ($shift_signup_state->getState()) {
            case ShiftSignupState::ADMIN:
            case ShiftSignupState::FREE:
                // When admin or free display a link + button for sign up
                $entry_list[] = '<a href="'
                    . shift_entry_create_link($shift, $angeltype)
                    . '">'
                    . $inner_text
                    . '</a> '
                    . button(
                        shift_entry_create_link($shift, $angeltype),
                        __('Sign up'), 'btn-xs btn-primary'
                    );
                break;

            case ShiftSignupState::SHIFT_ENDED:
                // No link and add a text hint, when the shift ended
                $entry_list[] = $inner_text . ' (' . __('ended') . ')';
                break;

            case ShiftSignupState::NOT_ARRIVED:
                // No link and add a text hint, when the shift ended
                $entry_list[] = $inner_text . ' (' . __('please arrive for signup') . ')';
                break;

            case ShiftSignupState::ANGELTYPE:
                if ($angeltype['restricted'] == 1) {
                    // User has to be confirmed on the angeltype first
                    $entry_list[] = $inner_text . glyph('lock');
                } else {
                    // Add link to join the angeltype first
                    $entry_list[] = $inner_text . '<br />'
                        . button(
                            page_link_to(
                                'user_angeltypes',
                                ['action' => 'add', 'angeltype_id' => $angeltype['id']]
                            ),
                            sprintf(__('Become %s'), $angeltype['name']),
                            'btn-xs'
                        );
                }
                break;

            case ShiftSignupState::COLLIDES:
            case ShiftSignupState::SIGNED_UP:
                // Shift collides or user is already signed up: No signup allowed
                $entry_list[] = $inner_text;
                break;

            case ShiftSignupState::OCCUPIED:
                // Shift is full
                break;
        }

        $shifts_row = '<li class="list-group-item">';
        $shifts_row .= '<strong>' . AngelType_name_render($angeltype) . ':</strong> ';
        $shifts_row .= join(', ', $entry_list);
        $shifts_row .= '</li>';
        return [
            $shift_signup_state,
            $shifts_row
        ];
    }

    /**
     * Renders the shift header
     *
     * @param array $shift The shift
     * @param string $class The shift state class
     * @return string
     */
    private function renderShiftHead($shift, $class)
    {
        $header_buttons = '';
        if (auth()->can('admin_shifts')) {
            $header_buttons = '<div class="pull-right">' . table_buttons([
                    button(
                        page_link_to('user_shifts', ['edit_shift' => $shift['SID']]),
                        glyph('edit'),
                        "btn-$class btn-xs"
                    ),
                    button(
                        page_link_to('user_shifts', ['delete_shift' => $shift['SID']]),
                        glyph('trash'),
                        "btn-$class btn-xs"
                    )
                ]) . '</div>';
        }
        $shift_heading = date('H:i', $shift['start']) . ' &dash; '
            . date('H:i', $shift['end']) . ' &mdash; '
            . $shift['name'];
        return div('panel-heading', [
            '<a href="' . shift_link($shift) . '">' . $shift_heading . '</a>',
            $header_buttons
        ]);
    }
}
