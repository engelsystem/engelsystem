<?php

use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function messages_title()
{
    return __('Messages');
}

/**
 * @return string
 */
function user_unread_messages()
{
    $user = auth()->user();

    if ($user) {
        $new_messages = $user->messagesReceived()
            ->where('read', false)
            ->count();

        if ($new_messages > 0) {
            return ' <span class="badge bg-danger">' . $new_messages . '</span>';
        }
    }
    return '';
}

/**
 * @return string
 */
function user_messages()
{
    $user = auth()->user();
    $request = request();

    if (!$request->has('action')) {
        /** @var User[] $users */
        $users = User::query()
            ->where('user_id', '!=', $user->id)
            ->leftJoin('users_personal_data', 'users.id', '=', 'users_personal_data.user_id')
            ->orderBy('name')
            ->get(['id', 'name', 'pronoun']);

        $to_select_data = [
            '' => __('Select recipient...')
        ];

        foreach ($users as $u) {
            $pronoun = ((config('enable_pronoun') && $u->pronoun) ? ' (' . htmlspecialchars($u->pronoun) . ')' : '');
            $to_select_data[$u->id] = $u->name . $pronoun;
        }

        $to_select = html_select_key('to', 'to', $to_select_data, '');

        $messages = $user->messages;

        $messages_table = [
            [
                'news'      => '',
                'timestamp' => date(__('Y-m-d H:i')),
                'from'      => User_Nick_render($user),
                'to'        => $to_select,
                'text'      => form_textarea('text', '', ''),
                'actions'   => form_submit('submit', __('Send'))
            ]
        ];

        foreach ($messages as $message) {
            $sender_user_source = $message->user;
            $receiver_user_source = $message->receiver;

            $messages_table_entry = [
                'new'       => !$message->read ? icon('envelope') : '',
                'timestamp' => $message->created_at->format(__('Y-m-d H:i')),
                'from'      => User_Nick_render($sender_user_source),
                'to'        => User_Nick_render($receiver_user_source),
                'text'      => nl2br(htmlspecialchars($message->text))
            ];

            if ($message->receiver_id == $user->id) {
                if (!$message->read) {
                    $messages_table_entry['actions'] = button(
                        page_link_to('user_messages', ['action' => 'read', 'id' => $message->id]),
                        __('mark as read'),
                        'btn-sm'
                    );
                }
            } else {
                $messages_table_entry['actions'] = button(
                    page_link_to('user_messages', ['action' => 'delete', 'id' => $message->id]),
                    __('delete message'),
                    'btn-sm'
                );
            }
            $messages_table[] = $messages_table_entry;
        }

        return page_with_title(messages_title(), [
            msg(),
            sprintf(__('Hello %s, here can you leave messages for other angels'), User_Nick_render($user)),
            form([
                table([
                    'new'       => __('New'),
                    'timestamp' => __('Date'),
                    'from'      => __('Transmitted'),
                    'to'        => __('Recipient'),
                    'text'      => __('Message'),
                    'actions'   => ''
                ], $messages_table)
            ], page_link_to('user_messages', ['action' => 'send']))
        ]);
    } else {
        switch ($request->input('action')) {
            case 'read':
                if ($request->has('id') && preg_match('/^\d{1,11}$/', $request->input('id'))) {
                    $message_id = $request->input('id');
                } else {
                    return error(__('Incomplete call, missing Message ID.'), true);
                }

                $message = Message::find($message_id);
                if ($message !== null && $message->receiver_id == $user->id) {
                    $message->read = true;
                    $message->save();
                    throw_redirect(page_link_to('user_messages'));
                } else {
                    return error(__('No Message found.'), true);
                }
                break;

            case 'delete':
                if ($request->has('id') && preg_match('/^\d{1,11}$/', $request->input('id'))) {
                    $message_id = $request->input('id');
                } else {
                    return error(__('Incomplete call, missing Message ID.'), true);
                }

                $message = Message::find($message_id);
                if ($message !== null && $message->user_id == $user->id) {
                    $message->delete();
                    throw_redirect(page_link_to('user_messages'));
                } else {
                    return error(__('No Message found.'), true);
                }
                break;

            case 'send':
                $receiver = User::find($request->input('to'));
                $text = $request->input('text');

                if ($receiver !== null && !empty($text)) {
                    Message::create([
                        'user_id'     => $user->id,
                        'receiver_id' => $request->input('to'),
                        'text'        => $request->input('text')
                    ]);
                    throw_redirect(page_link_to('user_messages'));
                } else {
                    return error(__('Transmitting was terminated with an Error.'), true);
                }
                break;

            default:
                return error(__('Wrong action.'), true);
        }
    }

    return '';
}
