<?php

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
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
            $shift->start->format(__('Y-m-d H:i')),
            $shift->end->format(__('Y-m-d H:i')),
            $angeltype->name
        ), true),
        form([
            buttons([
                button(user_link($signoff_user->id), icon('x-lg') . __('cancel')),
                form_submit('delete', icon('trash') . __('sign off'), 'btn-danger', false),
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
            $shift->start->format(__('Y-m-d H:i')),
            $shift->end->format(__('Y-m-d H:i')),
            $angeltype->name
        ), true),

        form([
            buttons([
                button(user_link($signoff_user->id), icon('x-lg') . __('cancel')),
                form_submit('delete', icon('trash') . __('delete'), 'btn-danger', false),
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
 * @param Room      $room
 * @param AngelType $angeltype
 * @param array     $angeltypes_select
 * @param User      $signup_user
 * @param array     $users_select
 * @return string
 */
function ShiftEntry_create_view_admin(
    Shift $shift,
    Room $room,
    AngelType $angeltype,
    $angeltypes_select,
    $signup_user,
    $users_select
) {
    $start = $shift->start->format(__('Y-m-d H:i'));
    return page_with_title(
        ShiftEntry_create_title() . ': ' . htmlspecialchars($shift->shiftType->name)
        . ' <small title="' . $start . '" data-countdown-ts="' . $shift->start->timestamp . '">%c</small>',
        [
            Shift_view_header($shift, $room),
            info(__('Do you want to sign up the following user for this shift?'), true),
            form([
                form_select('angeltype_id', __('Angeltype'), $angeltypes_select, $angeltype->id),
                form_select('user_id', __('User'), $users_select, $signup_user->id),
                form_submit('submit', icon('check-lg') . __('Save')),
            ]),
        ]
    );
}

/**
 * Supporter puts user into shift.
 *
 * @param Shift $shift
 * @param Room  $room
 * @param AngelType $angeltype
 * @param User  $signup_user
 * @param array $users_select
 * @return string
 */
function ShiftEntry_create_view_supporter(Shift $shift, Room $room, AngelType $angeltype, $signup_user, $users_select)
{
    $start = $shift->start->format(__('Y-m-d H:i'));
    return page_with_title(
        ShiftEntry_create_title() . ': ' . htmlspecialchars($shift->shiftType->name)
        . ' <small title="' . $start . '" data-countdown-ts="' . $shift->start->timestamp . '">%c</small>',
        [
            Shift_view_header($shift, $room),
            info(sprintf(
                __('Do you want to sign up the following user for this shift as %s?'),
                $angeltype->name
            ), true),
            form([
                form_select('user_id', __('User'), $users_select, $signup_user->id),
                form_submit('submit', icon('check-lg') . __('Save')),
            ]),
        ]
    );
}

/**
 * User joining a shift.
 *
 * @param Shift  $shift
 * @param Room   $room
 * @param AngelType  $angeltype
 * @param string $comment
 * @return string
 */
function ShiftEntry_create_view_user(Shift $shift, Room $room, AngelType $angeltype, $comment)
{
    $start = $shift->start->format(__('Y-m-d H:i'));
    return page_with_title(
        ShiftEntry_create_title() . ': ' . htmlspecialchars($shift->shiftType->name)
        . ' <small title="' . $start . '" data-countdown-ts="' . $shift->start->timestamp . '">%c</small>',
        [
            Shift_view_header($shift, $room),
            info(sprintf(__('Do you want to sign up for this shift as %s?'), $angeltype->name), true),
            form([
                form_textarea('comment', __('Comment (for your eyes only):'), $comment),
                form_submit('submit', icon('check-lg') . __('Save')),
            ]),
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
 * @param bool   $freeloaded
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
    $freeloaded,
    $freeloaded_comment,
    $user_admin_shifts = false
) {
    $freeload_form = [];
    if ($user_admin_shifts) {
        $freeload_form = [
            form_checkbox('freeloaded', __('Freeloaded'), $freeloaded),
            form_textarea(
                'freeloaded_comment',
                __('Freeload comment (Only for shift coordination):'),
                $freeloaded_comment
            ),
        ];
    }

    if ($angel->id == auth()->user()->id) {
        $comment = form_textarea('comment', __('Comment (for your eyes only):'), $comment);
    } else {
        $comment = '';
    }

    return page_with_title(__('Edit shift entry'), [
        msg(),
        form([
            form_info(__('Angel:'), User_Nick_render($angel)),
            form_info(__('Date, Duration:'), $date),
            form_info(__('Location:'), htmlspecialchars($location)),
            form_info(__('Title:'), htmlspecialchars($title)),
            form_info(__('Type:'), htmlspecialchars($type)),
            $comment,
            join('', $freeload_form),
            form_submit('submit', __('Save')),
        ]),
    ]);
}
