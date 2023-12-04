<?php

namespace Engelsystem;

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftSignupStatus;
use Engelsystem\Models\User\User;
use Illuminate\Support\Collection;

use function theme_type;

/**
 * Renders a single shift for the shift calendar
 */
class ShiftCalendarShiftRenderer
{
    /**
     * Renders a shift
     *
     * @param Shift                   $shift The shift to render
     * @param array[]                 $needed_angeltypes
     * @param ShiftEntry[]|Collection $shift_entries
     * @param User                    $user The user who is viewing the shift calendar
     * @return array
     */
    public function render(Shift $shift, $needed_angeltypes, $shift_entries, $user)
    {
        $info_text = '';
        if ($shift->title != '') {
            $info_text = icon('info-circle') . htmlspecialchars($shift->title) . '<br>';
        }
        list($shift_signup_state, $shifts_row) = $this->renderShiftNeededAngeltypes(
            $shift,
            $needed_angeltypes,
            $shift_entries,
            $user
        );

        $class = $this->classForSignupState($shift_signup_state);

        $blocks = ceil(($shift->end->timestamp - $shift->start->timestamp) / ShiftCalendarRenderer::SECONDS_PER_ROW);
        $blocks = max(1, $blocks);

        $room = $shift->room;

        return [
            $blocks,
            div(
                'shift-card" style="height: '
                . ($blocks * ShiftCalendarRenderer::BLOCK_HEIGHT - ShiftCalendarRenderer::MARGIN)
                . 'px;',
                div(
                    'shift card bg-' . $class,
                    [
                        $this->renderShiftHead($shift, $class, $shift_signup_state->getFreeEntries()),
                        div('card-body ' . $this->classBg(), [
                            $info_text,
                            Room_name_render($room),
                        ]),
                        $shifts_row,
                    ]
                )
            ),
        ];
    }

    /**
     * @param ShiftSignupState $shiftSignupState
     * @return string
     */
    private function classForSignupState(ShiftSignupState $shiftSignupState)
    {
        return match ($shiftSignupState->getState()) {
            ShiftSignupStatus::ADMIN, ShiftSignupStatus::OCCUPIED => 'success',
            ShiftSignupStatus::SIGNED_UP => 'primary',
            ShiftSignupStatus::NOT_ARRIVED, ShiftSignupStatus::NOT_YET, ShiftSignupStatus::SHIFT_ENDED => 'secondary',
            ShiftSignupStatus::ANGELTYPE, ShiftSignupStatus::COLLIDES => 'warning',
            ShiftSignupStatus::FREE => 'danger',
            default => 'light',
        };
    }

    /**
     * @param Shift                   $shift
     * @param array[]                 $needed_angeltypes
     * @param ShiftEntry[]|Collection $shift_entries
     * @param User                    $user
     * @return array
     */
    private function renderShiftNeededAngeltypes(Shift $shift, $needed_angeltypes, $shift_entries, $user)
    {
        $shift_entries_filtered = [];
        foreach ($needed_angeltypes as $needed_angeltype) {
            $shift_entries_filtered[$needed_angeltype['id']] = [];
        }
        foreach ($shift_entries as $shift_entry) {
            $shift_entries_filtered[$shift_entry->angel_type_id][] = $shift_entry;
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
            $shift_signup_state = new ShiftSignupState(ShiftSignupStatus::SHIFT_ENDED, 0);
        }

        if (auth()->can('user_shifts_admin')) {
            $html .= '<li class="list-group-item d-flex align-items-center ' . $this->classBg() . '">';
            $html .= button(
                shift_entry_create_link_admin($shift),
                icon('plus-lg') . __('Add more angels'),
                'btn-sm'
            );
            $html .= '</li>';
        }
        if ($html != '') {
            return [
                $shift_signup_state,
                '<ul class="list-group list-group-flush">' . $html . '</ul>',
            ];
        }

        return [
            $shift_signup_state,
            '',
        ];
    }

    /**
     * Renders a list entry containing the needed angels for an angeltype
     *
     * @param Shift                   $shift The shift which is rendered
     * @param ShiftEntry[]|Collection $shift_entries
     * @param array                   $angeltype The angeltype, containing information about needed angeltypes
     *                           and already signed up angels
     * @param User                    $user The user who is viewing the shift calendar
     * @return array
     */
    private function renderShiftNeededAngeltype(Shift $shift, $shift_entries, $angeltype, $user)
    {
        $angeltype = (new AngelType())->forceFill($angeltype);
        $entry_list = [];
        foreach ($shift_entries as $entry) {
            $class = $entry->freeloaded ? 'text-decoration-line-through' : '';
            $entry_list[] = '<span class="text-nowrap ' . $class . '">' . User_Nick_render($entry->user) . '</span>';
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

        $entry = match ($shift_signup_state->getState()) {
            // When admin or free display a link + button for sign up
            ShiftSignupStatus::ADMIN, ShiftSignupStatus::FREE =>
                '<a class="me-1 text-nowrap" href="'
                . shift_entry_create_link($shift, $angeltype)
                . '">'
                . $inner_text
                . '</a> '
                . button(
                    shift_entry_create_link($shift, $angeltype),
                    __('Sign up'),
                    'btn-sm btn-primary text-nowrap d-print-none'
                ),
            // No link and add a text hint, when the shift ended
            ShiftSignupStatus::SHIFT_ENDED => $inner_text . ' (' . __('ended') . ')',
            // No link and add a text hint, when the shift ended
            ShiftSignupStatus::NOT_ARRIVED => $inner_text . ' (' . __('please arrive for signup') . ')',
            ShiftSignupStatus::NOT_YET => $inner_text . ' (' . __('not yet') . ')',
            ShiftSignupStatus::ANGELTYPE => $angeltype->restricted
                // User has to be confirmed on the angeltype first
                ? $inner_text . icon('mortarboard-fill')
                // Add link to join the angeltype first
                : $inner_text . '<br />'
                . button(
                    page_link_to('user_angeltypes', ['action' => 'add', 'angeltype_id' => $angeltype->id]),
                    sprintf(__('Become %s'), htmlspecialchars($angeltype->name)),
                    'btn-sm'
                ),
            // Shift collides or user is already signed up: No signup allowed
            ShiftSignupStatus::COLLIDES, ShiftSignupStatus::SIGNED_UP => $inner_text,
            // Shift is full
            ShiftSignupStatus::OCCUPIED => null,
            default => null,
        };
        if (!is_null($entry)) {
            $entry_list[] = $entry;
        }

        $shifts_row = '<li class="list-group-item d-flex flex-wrap align-items-center ' . $this->classBg() . '">';
        $shifts_row .= '<strong class="me-1">' . AngelType_name_render($angeltype) . ':</strong> ';
        $shifts_row .= join(', ', $entry_list);
        $shifts_row .= '</li>';
        return [
            $shift_signup_state,
            $shifts_row,
        ];
    }

    /**
     * Return the corresponding bg class
     *
     * @return string
     */
    private function classBg(): string
    {
        if (theme_type() === 'light') {
            return 'bg-white';
        }

        return 'bg-dark';
    }

    /**
     * Renders the shift header
     *
     * @param Shift  $shift The shift
     * @param string $class The shift state class
     * @return string
     */
    private function renderShiftHead(Shift $shift, $class, $needed_angeltypes_count)
    {
        $header_buttons = '';
        if (auth()->can('admin_shifts')) {
            $header_buttons = '<div class="ms-auto d-print-none">' . table_buttons([
                    button(
                        page_link_to('user_shifts', ['edit_shift' => $shift->id]),
                        icon('pencil'),
                        'btn-' . $class . ' btn-sm border-light text-white'
                    ),
                    button(
                        page_link_to('user_shifts', ['delete_shift' => $shift->id]),
                        icon('trash'),
                        'btn-' . $class . ' btn-sm border-light text-white'
                    ),
                ]) . '</div>';
        }
        $shift_heading = $shift->start->format('H:i') . ' &dash; '
            . $shift->end->format('H:i') . ' &mdash; '
            . htmlspecialchars($shift->shiftType->name);

        if ($needed_angeltypes_count > 0) {
            $shift_heading = '<span class="badge bg-light text-danger me-1">' . $needed_angeltypes_count . '</span> ' . $shift_heading;
        }

        return div('card-header d-flex align-items-center', [
            '<a class="d-flex align-items-center text-white" href="' . shift_link($shift) . '">' . $shift_heading . '</a>',
            $header_buttons,
        ]);
    }
}
