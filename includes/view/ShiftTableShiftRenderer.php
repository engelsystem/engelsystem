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
 * Renders a single shift as a table row for the shift table view
 */
class ShiftTableShiftRenderer extends ShiftCalendarShiftRenderer
{
    /**
     * Renders a shift
     *
     * @param Shift                  $shift The shift to render
     * @param AngelType[]|Collection $needed_angeltypes
     * @param ShiftEntry[]|Collection $shift_entries
     * @param User                   $user The user who is viewing the shift table
     * @return array
     */
    public function render(Shift $shift, $needed_angeltypes, $shift_entries, $user)
    {
        [$shiftState, $neededAngels] = $this->renderShiftNeededAngeltypes($shift, $needed_angeltypes, $shift_entries, $user);

        return [
            'timeslot'        =>
                icon('clock') . ' '
                . $shift->start->format('Y-m-d H:i')
                . ' - '
                . $shift->end->format('H:i')
                . '<br />'
                . location_name_render($shift->location),
            'title'           =>
                htmlspecialchars($shift->shiftType->name)
                . ($shift->title ? '<br />' . htmlspecialchars($shift->title) : ''),
            'needed_angels'   => $neededAngels,
            'selected_angels' => $this->renderRegisteredAngels($shift, $needed_angeltypes, $shift_entries, $user),
            'state'           => $this->getState($shift, $shiftState),
        ];
    }

    private function getState(Shift $shift, $shiftState)
    {
        $class = $this->classForSignupState($shiftState);
        $stateText = $this->getStateText($class);

        return "<span id='shift_row_{$shift->id}'>{$stateText}"
            . "<script>setStateShiftTableRow({$shift->id},'{$class}')</script></span>";
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

    private function getStateText(string $cssClass)
    {
        return match ($cssClass) {
            'primary' => __('Your shift'),
            'danger' => __('Help needed'),
            'warning' => __('Other angel type needed / collides with my shifts'),
            'success' => __('Shift is full'),
            'secondary' => __('Shift is running/has ended, you have not arrived or signup is blocked otherwise'),
            default => '',
        };
    }

    /**
     * Renders the "needed angels" column entries, i.e. the outstanding need per angeltype
     *
     * @param Shift                   $shift
     * @param AngelType[]|Collection  $needed_angeltypes
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
                [$angeltype_signup_state, $angeltype_html] = $this->renderShiftNeededAngeltype(
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
     * Renders the "selected angels" column, i.e. the angels who already signed up
     *
     * @param Shift                   $shift
     * @param AngelType[]|Collection  $needed_angeltypes
     * @param ShiftEntry[]|Collection $shift_entries
     * @param User                    $user
     * @return string
     */
    protected function renderRegisteredAngels(Shift $shift, $needed_angeltypes, $shift_entries, $user)
    {
        $shift_entries_filtered = [];
        foreach ($needed_angeltypes as $needed_angeltype) {
            $shift_entries_filtered[$needed_angeltype['id']] = [];
        }
        foreach ($shift_entries as $shift_entry) {
            $shift_entries_filtered[$shift_entry->angel_type_id][] = $shift_entry;
        }

        $html = '';
        foreach ($needed_angeltypes as $angeltype) {
            if ($angeltype['count'] > 0 || count($shift_entries_filtered[$angeltype['id']]) > 0) {
                $html .= $this->renderShiftRegisteredAngeltype(
                    $shift_entries_filtered[$angeltype['id']],
                    $angeltype
                );
            }
        }

        if ($html != '') {
            return '<ul class="list-group list-group-flush">' . $html . '</ul>';
        }

        return '';
    }

    /**
     * @param ShiftEntry[]|Collection $shift_entries
     * @param array                   $angeltype
     * @return string
     */
    protected function renderShiftRegisteredAngeltype($shift_entries, $angeltype)
    {
        $entry_list = [];
        foreach ($shift_entries as $entry) {
            $class = $entry->freeloaded_by ? 'text-decoration-line-through' : '';
            $entry_list[] = '<span class="text-nowrap ' . $class . '">' . User_Nick_render($entry->user) . '</span>';
        }

        if (empty($entry_list)) {
            return '';
        }

        $angeltype = (new AngelType())->forceFill($angeltype);

        return '<li class="list-group-item d-flex flex-wrap align-items-center ' . $this->classBg() . '">'
            . '<strong class="me-1">' . AngelType_name_render($angeltype) . ':</strong> '
            . join(', ', $entry_list)
            . '</li>';
    }

    /**
     * Renders a list entry containing the needed angels for an angeltype
     *
     * @param Shift                   $shift The shift which is rendered
     * @param ShiftEntry[]|Collection $shift_entries
     * @param array                   $angeltype The angeltype, containing information about needed angeltypes
     *                                            and already signed up angels
     * @param User                    $user The user who is viewing the shift table
     * @return array
     */
    private function renderShiftNeededAngeltype(Shift $shift, $shift_entries, $angeltype, $user)
    {
        $angeltype = (new AngelType())->forceFill($angeltype);

        $shift_signup_state = Shift_signup_allowed(
            $user,
            $shift,
            $angeltype,
            null,
            null,
            $angeltype,
            $shift_entries
        );
        $shift_can_signup = Shift_signup_allowed_angel(
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
            ShiftSignupStatus::SHIFT_ENDED => $inner_text . ' (' . __('ended') . ')',
            ShiftSignupStatus::NOT_ARRIVED => $inner_text . ' (' . __('please arrive for signup') . ')',
            ShiftSignupStatus::NOT_YET => $inner_text . ' (' . __('not yet possible') . ')',
            ShiftSignupStatus::ANGELTYPE => $angeltype->restricted || !$angeltype->shift_self_signup
                ? $inner_text . icon('mortarboard-fill')
                : $inner_text . '<br />'
                . button(
                    url('/user-angeltypes', ['action' => 'add', 'angeltype_id' => $angeltype->id]),
                    sprintf(__('Join %s'), htmlspecialchars($angeltype->name)),
                    'btn-sm'
                ),
            ShiftSignupStatus::COLLIDES, ShiftSignupStatus::SIGNED_UP => $inner_text,
            ShiftSignupStatus::OCCUPIED => '',
            default => '',
        };

        $shifts_row = '<li class="list-group-item d-flex flex-wrap align-items-center ' . $this->classBg() . '">'
            . '<strong class="me-1">' . AngelType_name_render($angeltype) . ':</strong> '
            . $entry
            . '</li>';

        return [
            $shift_can_signup,
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
}
