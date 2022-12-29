<?php

use Engelsystem\Database\Db;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function myshifts_title()
{
    return __('My shifts');
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
            User_reset_api_key($user);
            success(__('Key changed.'));
            throw_redirect(page_link_to('users', ['action' => 'view', 'user_id' => $shifts_user->id]));
        }
        return page_with_title(__('Reset API key'), [
            error(
                __('If you reset the key, the url to your iCal- and JSON-export and your atom feed changes! You have to update it in every application using one of these exports.'),
                true
            ),
            button(page_link_to('user_myshifts', ['reset' => 'ack']), __('Continue'), 'btn-danger')
        ]);
    } elseif ($request->has('edit') && preg_match('/^\d+$/', $request->input('edit'))) {
        $shift_entry_id = $request->input('edit');
        $shift = Db::selectOne(
            '
                SELECT
                    `ShiftEntry`.`freeloaded`,
                    `ShiftEntry`.`freeload_comment`,
                    `ShiftEntry`.`Comment`,
                    `ShiftEntry`.`UID`,
                    `shift_types`.`name`,
                    `shifts`.*,
                    `angel_types`.`name` AS `angel_type`
                FROM `ShiftEntry`
                JOIN `angel_types` ON (`ShiftEntry`.`TID` = `angel_types`.`id`)
                JOIN `shifts` ON (`ShiftEntry`.`SID` = `shifts`.`id`)
                JOIN `shift_types` ON (`shift_types`.`id` = `shifts`.`shift_type_id`)
                WHERE `ShiftEntry`.`id`=?
                AND `UID`=?
                LIMIT 1
            ',
            [
                $shift_entry_id,
                $shifts_user->id,
            ]
        );
        if (!empty($shift)) {
            /** @var Shift $shift */
            $shift = (new Shift())->forceFill($shift);

            $freeloaded = $shift->freeloaded;
            $freeload_comment = $shift->freeloaded_comment;

            if ($request->hasPostData('submit')) {
                $valid = true;
                if (auth()->can('user_shifts_admin')) {
                    $freeloaded = $request->has('freeloaded');
                    $freeload_comment = strip_request_item_nl('freeload_comment');
                    if ($freeloaded && $freeload_comment == '') {
                        $valid = false;
                        error(__('Please enter a freeload comment!'));
                    }
                }

                $comment = $shift->Comment;
                $user_source = User::find($shift->UID);
                if (auth()->user()->id == $user_source->id) {
                    $comment = strip_request_item_nl('comment');
                }

                if ($valid) {
                    ShiftEntry_update([
                        'id'               => $shift_entry_id,
                        'Comment'          => $comment,
                        'freeloaded'       => $freeloaded,
                        'freeload_comment' => $freeload_comment
                    ]);

                    engelsystem_log(
                        'Updated ' . User_Nick_render($user_source, true) . '\'s shift '
                        . $shift->title . ' / ' . $shift->shiftType->name
                        . ' from ' . $shift->start->format('Y-m-d H:i')
                        . ' to ' . $shift->end->format('Y-m-d H:i')
                        . ' with comment ' . $comment
                        . '. Freeloaded: ' . ($freeloaded ? 'YES Comment: ' . $freeload_comment : 'NO')
                    );
                    success(__('Shift saved.'));
                    throw_redirect(page_link_to('users', ['action' => 'view', 'user_id' => $shifts_user->id]));
                }
            }

            return ShiftEntry_edit_view(
                $shifts_user,
                $shift->start->format('Y-m-d H:i') . ', ' . shift_length($shift),
                $shift->room->name,
                $shift->shiftType->name,
                $shift->angel_type,
                $shift->Comment,
                $shift->freeloaded,
                $shift->freeload_comment,
                auth()->can('user_shifts_admin')
            );
        } else {
            throw_redirect(page_link_to('user_myshifts'));
        }
    }

    throw_redirect(page_link_to('users', ['action' => 'view', 'user_id' => $shifts_user->id]));
    return '';
}
