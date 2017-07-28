<?php

use Engelsystem\Database\DB;

/**
 * @return string
 */
function messages_title()
{
    return _('Messages');
}

/**
 * @return string
 */
function user_unread_messages()
{
    global $user;

    if (isset($user)) {
        $new_messages = count(DB::select(
            'SELECT `id` FROM `Messages` WHERE isRead=\'N\' AND `RUID`=?',
            [$user['UID']]
        ));
        if ($new_messages > 0) {
            return ' <span class="badge danger">' . $new_messages . '</span>';
        }
    }
    return '';
}

/**
 * @return string
 */
function user_messages()
{
    global $user;
    $request = request();

    if (!$request->has('action')) {
        $users = DB::select(
            'SELECT `UID`, `Nick` FROM `User` WHERE NOT `UID`=? ORDER BY `Nick`',
            [$user['UID']]
        );

        $to_select_data = [
            '' => _('Select recipient...')
        ];

        foreach ($users as $u) {
            $to_select_data[$u['UID']] = $u['Nick'];
        }

        $to_select = html_select_key('to', 'to', $to_select_data, '');

        $messages = DB::select('
            SELECT *
            FROM `Messages`
            WHERE `SUID`=?
            OR `RUID`=?
            ORDER BY `isRead`,`Datum` DESC
        ',
            [
                $user['UID'],
                $user['UID'],
            ]
        );

        $messages_table = [
            [
                'news'      => '',
                'timestamp' => date('Y-m-d H:i'),
                'from'      => User_Nick_render($user),
                'to'        => $to_select,
                'text'      => form_textarea('text', '', ''),
                'actions'   => form_submit('submit', _('Save'))
            ]
        ];

        foreach ($messages as $message) {
            $sender_user_source = User($message['SUID']);
            $receiver_user_source = User($message['RUID']);

            $messages_table_entry = [
                'new'       => $message['isRead'] == 'N' ? '<span class="glyphicon glyphicon-envelope"></span>' : '',
                'timestamp' => date('Y-m-d H:i', $message['Datum']),
                'from'      => User_Nick_render($sender_user_source),
                'to'        => User_Nick_render($receiver_user_source),
                'text'      => str_replace("\n", '<br />', $message['Text'])
            ];

            if ($message['RUID'] == $user['UID']) {
                if ($message['isRead'] == 'N') {
                    $messages_table_entry['actions'] = button(
                        page_link_to('user_messages') . '&action=read&id=' . $message['id'],
                        _('mark as read'),
                        'btn-xs'
                    );
                }
            } else {
                $messages_table_entry['actions'] = button(
                    page_link_to('user_messages') . '&action=delete&id=' . $message['id'],
                    _('delete message'),
                    'btn-xs'
                );
            }
            $messages_table[] = $messages_table_entry;
        }

        return page_with_title(messages_title(), [
            msg(),
            sprintf(_('Hello %s, here can you leave messages for other angels'), User_Nick_render($user)),
            form([
                table([
                    'new'       => _('New'),
                    'timestamp' => _('Date'),
                    'from'      => _('Transmitted'),
                    'to'        => _('Recipient'),
                    'text'      => _('Message'),
                    'actions'   => ''
                ], $messages_table)
            ], page_link_to('user_messages') . '&action=send')
        ]);
    } else {
        switch ($request->input('action')) {
            case 'read':
                if ($request->has('id') && preg_match('/^\d{1,11}$/', $request->input('id'))) {
                    $message_id = $request->input('id');
                } else {
                    return error(_('Incomplete call, missing Message ID.'), true);
                }

                $message = DB::selectOne(
                    'SELECT `RUID` FROM `Messages` WHERE `id`=? LIMIT 1',
                    [$message_id]
                );
                if (!empty($message) && $message['RUID'] == $user['UID']) {
                    DB::update(
                        'UPDATE `Messages` SET `isRead`=\'Y\' WHERE `id`=? LIMIT 1',
                        [$message_id]
                    );
                    redirect(page_link_to('user_messages'));
                } else {
                    return error(_('No Message found.'), true);
                }
                break;

            case 'delete':
                if ($request->has('id') && preg_match('/^\d{1,11}$/', $request->input('id'))) {
                    $message_id = $request->input('id');
                } else {
                    return error(_('Incomplete call, missing Message ID.'), true);
                }

                $message = DB::selectOne(
                    'SELECT `SUID` FROM `Messages` WHERE `id`=? LIMIT 1',
                    [$message_id]
                );
                if (!empty($message) && $message['SUID'] == $user['UID']) {
                    DB::delete('DELETE FROM `Messages` WHERE `id`=? LIMIT 1', [$message_id]);
                    redirect(page_link_to('user_messages'));
                } else {
                    return error(_('No Message found.'), true);
                }
                break;

            case 'send':
                // @TODO: Validation?
                if (Message_send($request->input('to'), $request->input('text'))) {
                    redirect(page_link_to('user_messages'));
                } else {
                    return error(_('Transmitting was terminated with an Error.'), true);
                }
                break;

            default:
                return error(_('Wrong action.'), true);
        }
    }

    return '';
}
