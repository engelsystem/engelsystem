<?php

use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function myshifts_title()
{
    return __('profile.my-shifts');
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

    if (
        $request->has('id')
        && auth()->can('user_shifts_admin')
        && preg_match('/^\d+$/', $request->input('id'))
        && User::find($request->input('id'))
    ) {
        $shift_entry_id = $request->input('id');
    } else {
        $shift_entry_id = $user->id;
    }

    $shifts_user = User::find($shift_entry_id);
    if ($request->has('reset')) {
        if ($request->input('reset') == 'ack') {
            auth()->resetApiKey($user);
            engelsystem_log(sprintf('API key resetted (%s).', User_Nick_render($user, true)));
            success(__('Key changed.'));
            throw_redirect(url('/users', ['action' => 'view', 'user_id' => $shifts_user->id]));
        }
        return page_with_title(__('Reset API key'), [
            error(
                __('If you reset the key, the url to your iCal- and JSON-export and your atom/rss feed changes! You have to update it in every application using one of these exports.'),
                true
            ),
            button(url('/user-myshifts', ['reset' => 'ack']), __('Continue'), 'btn-danger'),
        ]);
    } elseif ($request->has('edit') && preg_match('/^\d+$/', $request->input('edit'))) {
        $shift_entry_id = $request->input('edit');
        /** @var ShiftEntry $shiftEntry */
        $shiftEntry = ShiftEntry::where('id', $shift_entry_id)
            ->where('user_id', $shifts_user->id)
            ->with(['shift', 'shift.shiftType', 'shift.location', 'user'])
            ->first();
        if (!empty($shiftEntry)) {
            $shift = $shiftEntry->shift;
            $freeloaded = $shiftEntry->freeloaded;
            $freeloaded_comment = $shiftEntry->freeloaded_comment;

            if ($request->hasPostData('submit')) {
                $valid = true;
                if (auth()->can('user_shifts_admin')) {
                    $freeloaded = $request->has('freeloaded');
                    $freeloaded_comment = strip_request_item_nl('freeloaded_comment');
                    if ($freeloaded && $freeloaded_comment == '') {
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
                    $shiftEntry->freeloaded = $freeloaded;
                    $shiftEntry->freeloaded_comment = $freeloaded_comment;
                    $shiftEntry->save();

                    engelsystem_log(
                        'Updated ' . User_Nick_render($user_source, true) . '\'s shift '
                        . $shift->title . ' / ' . $shift->shiftType->name
                        . ' from ' . $shift->start->format('Y-m-d H:i')
                        . ' to ' . $shift->end->format('Y-m-d H:i')
                        . ' with comment ' . $comment
                        . '. Freeloaded: ' . ($freeloaded ? 'YES Comment: ' . $freeloaded_comment : 'NO')
                    );
                    success(__('Shift saved.'));
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
                $shiftEntry->freeloaded,
                $shiftEntry->freeloaded_comment,
                auth()->can('user_shifts_admin')
            );
        } else {
            throw_redirect(url('/user-myshifts'));
        }
    }

    throw_redirect(url('/users', ['action' => 'view', 'user_id' => $shifts_user->id]));
    return '';
}
