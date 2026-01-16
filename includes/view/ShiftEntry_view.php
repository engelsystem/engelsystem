<?php

use Engelsystem\Config\GoodieType;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\Location;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\User\User;

/**
 * Sign off from a user from a shift with admin permissions, asking for ack.
 *
 * @param Shift $shift
 * @param AngelType $angeltype
 * @param User  $signoff_user
 *
 * @return string HTML
 */
function ShiftEntry_delete_view_admin(Shift $shift, AngelType $angeltype, User $signoff_user)
{
    return page_with_title(ShiftEntry_delete_title(), [
        info(sprintf(
            __('Do you want to sign off %s from shift %s from %s to %s as %s?'),
            $signoff_user->displayName,
            $shift->shiftType->name,
            $shift->start->format(__('general.datetime')),
            $shift->end->format(__('general.datetime')),
            $angeltype->name
        ), true),
        form([
            buttons([
                button(user_link($signoff_user->id), icon('x-lg') . __('form.cancel')),
                form_submit('delete', icon('trash'), 'btn-danger', false, 'primary', __('Sign off')),
            ]),
        ]),
    ]);
}

/**
 * Sign off from a shift, asking for ack.
 *
 * @param Shift $shift
 * @param AngelType $angeltype
 * @param User $signoff_user
 *
 * @return string HTML
 */
function ShiftEntry_delete_view(Shift $shift, AngelType $angeltype, User $signoff_user)
{
    return page_with_title(ShiftEntry_delete_title(), [
        info(sprintf(
            __('Do you want to sign off from your shift %s from %s to %s as %s?'),
            $shift->shiftType->name,
            $shift->start->format(__('general.datetime')),
            $shift->end->format(__('general.datetime')),
            $angeltype->name
        ), true),

        form([
            buttons([
                button(user_link($signoff_user->id), icon('x-lg') . __('form.cancel')),
                form_submit(
                    'delete',
                    icon('trash'),
                    'btn-danger',
                    false,
                    'danger',
                    __('Sign off')
                ),
            ]),
        ]),
    ]);
}

/**
 * Title for deleting a shift entry.
 */
function ShiftEntry_delete_title()
{
    return __('Shift sign off');
}

/**
 * Admin puts user into shift.
 *
 * @param Shift     $shift
 * @param Location  $location
 * @param AngelType $angeltype
 * @param array     $angeltypes_select
 * @param User      $signup_user
 * @param array     $users_select
 * @return string
 */
function ShiftEntry_create_view_admin(
    Shift $shift,
    Location $location,
    AngelType $angeltype,
    $angeltypes_select,
    $signup_user,
    $users_select
) {
    $start = $shift->start->format(__('general.datetime'));
    return page_with_title(
        ShiftEntry_create_title() . ': ' . htmlspecialchars($shift->shiftType->name)
        . ' <small title="' . $start . '" data-countdown-ts="' . $shift->start->timestamp . '">%c</small>',
        [
            Shift_view_header($shift, $location),
            info(__('Do you want to sign up the following user for this shift?'), true),
            form([
                form_select('angeltype_id', __('Angel type'), $angeltypes_select, $angeltype->id),
                form_select('user_id', __('general.user'), $users_select, $signup_user->id),
                form_submit('submit', icon('save') . __('form.save')),
            ]),
        ]
    );
}

/**
 * Supporter puts user into shift.
 *
 * @param Shift $shift
 * @param Location $location
 * @param AngelType $angeltype
 * @param User  $signup_user
 * @param array $users_select
 * @return string
 */
function ShiftEntry_create_view_supporter(
    Shift $shift,
    Location $location,
    AngelType $angeltype,
    $signup_user,
    $users_select
) {
    $start = $shift->start->format(__('general.datetime'));
    return page_with_title(
        ShiftEntry_create_title() . ': ' . htmlspecialchars($shift->shiftType->name)
        . ' <small title="' . $start . '" data-countdown-ts="' . $shift->start->timestamp . '">%c</small>',
        [
            Shift_view_header($shift, $location),
            info(sprintf(
                __('Do you want to sign up the following user for this shift as %s?'),
                $angeltype->name
            ), true),
            form([
                form_select('user_id', __('general.user'), $users_select, $signup_user->id),
                form_submit('submit', icon('save') . __('form.save')),
            ]),
        ]
    );
}

/**
 * User joining a shift.
 *
 * @param Shift     $shift
 * @param Location  $location
 * @param AngelType $angeltype
 * @param string    $comment
 * @param array     $supervisors_select Optional array of available supervisors (user_id => display_name)
 * @param int|null  $selected_supervisor_id Currently selected supervisor ID
 * @return string
 */
function ShiftEntry_create_view_user(
    Shift $shift,
    Location $location,
    AngelType $angeltype,
    $comment,
    array $supervisors_select = [],
    ?int $selected_supervisor_id = null
) {
    $start = $shift->start->format(__('general.datetime'));
    $formFields = [];

    // Add supervisor selection if supervisors are available (user is a minor requiring supervision)
    if (!empty($supervisors_select)) {
        $formFields[] = form_select(
            'supervisor_id',
            __('shift.supervisor.select'),
            $supervisors_select,
            $selected_supervisor_id ?? array_key_first($supervisors_select)
        );
        $formFields[] = info(__('shift.supervisor.select.info'), true, true);
    }

    $formFields[] = form_textarea('comment', __('Comment (for your eyes only):'), $comment);
    $formFields[] = form_submit('submit', icon('save') . __('form.save'));

    return page_with_title(
        ShiftEntry_create_title() . ': ' . htmlspecialchars($shift->shiftType->name)
        . ' <small title="' . $start . '" data-countdown-ts="' . $shift->start->timestamp . '">%c</small>',
        [
            Shift_view_header($shift, $location),
            info(sprintf(__('Do you want to sign up for this shift as %s?'), $angeltype->name), true),
            form($formFields),
        ]
    );
}

/**
 * Title for creating a shift entry.
 */
function ShiftEntry_create_title()
{
    return __('Shift signup');
}

/**
 * Display form for adding/editing a shift entry.
 *
 * @param User   $angel
 * @param string $date
 * @param string $location
 * @param string $title
 * @param string $type
 * @param string $comment
 * @param int   $freeloaded_by
 * @param string $freeloaded_comment
 * @param bool   $user_admin_shifts
 * @return string
 */
function ShiftEntry_edit_view(
    $angel,
    $date,
    $location,
    $title,
    $type,
    $comment,
    $freeloaded_by,
    $freeloaded_comment,
    $user_admin_shifts = false,
    $angeltype_supporter = false
) {
    $freeload_form = [];
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;

    if ($user_admin_shifts || $angeltype_supporter) {
        if (!$goodie_enabled) {
            $freeload_info = __('freeload.freeloaded.info', [config('max_freeloadable_shifts')]);
        } else {
            $freeload_info = __('freeload.freeloaded.info.goodie', [__('Goodie score'),
                config('max_freeloadable_shifts')]);
        }
        $freeload_form = [
            form_checkbox('freeloaded', (!$freeloaded_by
                    ? __('Freeloaded')
                    : __('Freeloaded by %s', [User_Nick_render(User::find($freeloaded_by))]))
                . ' <span class="bi bi-info-circle-fill text-info" data-bs-toggle="tooltip" title="'
                . $freeload_info . '"></span>', $freeloaded_by),
            form_textarea(
                'freeloaded_comment',
                __('Freeload comment (Only for shift coordination and supporters):'),
                $freeloaded_comment
            ),
        ];
    }

    if ($angel->id == auth()->user()->id) {
        $comment = form_textarea('comment', __('Comment (for your eyes only):'), $comment);
    } else {
        $comment = '';
    }

    $link = button(
        url('/users', ['action' => 'view', 'user_id' => $angel->id]),
        icon('chevron-left'),
        'btn-sm',
        '',
        __('general.back'),
    );

    return page_with_title(
        $link . ' ' . __('Edit shift entry'),
        [
            msg(),
            form([
                form_info(__('Angel:'), User_Nick_render($angel)),
                form_info(__('Date, Duration:'), $date),
                form_info(__('Location:'), htmlspecialchars($location)),
                form_info(__('Title:'), htmlspecialchars($title)),
                form_info(__('Type:'), htmlspecialchars($type)),
                $comment,
                join('', $freeload_form),
                form_submit('submit', icon('save') . __('form.save')),
            ]),
        ]
    );
}
