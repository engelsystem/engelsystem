<?php

use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function myshifts_title()
{
    return __('profile.my_shifts');
}

/**
 * Zeigt die Schichten an, die ein Benutzer belegt
 *
 * @return string
 */
function user_myshifts()
{
    $user = auth()->user();
    $request = request();
    $is_angeltype_supporter = false;
    if ($request->has('edit')) {
        $id = $request->input('edit');
        $shiftEntry = ShiftEntry::where('id', $id)
            ->where('user_id', User::find($request->input('id'))?->id)
            ->first();
        $is_angeltype_supporter = $shiftEntry && auth()->user()->isAngelTypeSupporter($shiftEntry->angelType);
    }

    if (
        $request->has('id')
        && (auth()->can('user_shifts_admin') || $is_angeltype_supporter)
        && preg_match('/^\d+$/', $request->input('id'))
        && User::find($request->input('id'))
    ) {
        $shift_entry_id = $request->input('id');
    } else {
        $shift_entry_id = $user->id;
    }

    $shifts_user = User::find($shift_entry_id);
    if ($request->has('edit') && preg_match('/^\d+$/', $request->input('edit'))) {
        $shift_entry_id = $request->input('edit');
        /** @var ShiftEntry $shiftEntry */
        $shiftEntry = ShiftEntry::where('id', $shift_entry_id)
            ->where('user_id', $shifts_user->id)
            ->with(['shift', 'shift.shiftType', 'shift.location', 'user'])
            ->first();
        if (!empty($shiftEntry)) {
            $shift = $shiftEntry->shift;
            $freeloaded_by = $shiftEntry->freeloaded_by;
            $freeloaded_comment = $shiftEntry->freeloaded_comment;

            if ($request->hasPostData('submit')) {
                $valid = true;
                if (
                    auth()->can('user_shifts_admin')
                    || $is_angeltype_supporter
                ) {
                    // set freeloaded by on new freeload or changed comment
                    $freeloaded_by = $request->has('freeloaded')
                        ? (strip_request_item_nl('freeloaded_comment') == $freeloaded_comment
                            ? ($shiftEntry->freeloaded_by ?? $user->id)
                            : $user->id)
                        : null;
                    // set freeloaded comment
                    $freeloaded_comment = strip_request_item_nl('freeloaded_comment');
                    if ($freeloaded_by && $freeloaded_comment == '') {
                        $valid = false;
                        error(__('Please enter a freeload comment!'));
                    }
                }

                $comment = $shiftEntry->user_comment;
                $user_source = $shiftEntry->user;
                if (auth()->user()->id == $user_source->id) {
                    $comment = strip_request_item_nl('comment');
                }

                if ($valid) {
                    $shiftEntry->user_comment = $comment;
                    $shiftEntry->freeloaded_by = $freeloaded_by;
                    $shiftEntry->freeloaded_comment = $freeloaded_comment;
                    $shiftEntry->save();

                    engelsystem_log(
                        'Updated ' . User_Nick_render($user_source, true) . '\'s shift '
                        . $shift->title . ' / ' . $shift->shiftType->name
                        . ' from ' . $shift->start->format('Y-m-d H:i')
                        . ' to ' . $shift->end->format('Y-m-d H:i')
                        . ' with comment ' . $comment
                        . '. Freeloaded' . ($freeloaded_by
                            ? ' by ' . User_Nick_render(User::findOrFail($freeloaded_by), true) . ' with Comment: ' . $freeloaded_comment
                            : ': NO')
                    );
                    success(__('Shift saved.'));
                    if ($is_angeltype_supporter) {
                        throw_redirect(url('/shifts', ['action' => 'view', 'shift_id' => $shiftEntry->shift_id]));
                    }
                    throw_redirect(url('/users', ['action' => 'view', 'user_id' => $shifts_user->id]));
                }
            }

            return ShiftEntry_edit_view(
                $shifts_user,
                $shift->start->format(__('general.datetime')) . ', ' . shift_length($shift),
                $shift->location->name,
                $shift->shiftType->name,
                $shiftEntry->angelType->name,
                $shiftEntry->user_comment,
                $shiftEntry->freeloaded_by,
                $shiftEntry->freeloaded_comment,
                auth()->can('user_shifts_admin'),
                $is_angeltype_supporter
            );
        } else {
            throw_redirect(url('/user-myshifts'));
        }
    }
    throw_redirect(url('/users', ['action' => 'view', 'user_id' => $shifts_user->id]));
}
