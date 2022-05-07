<?php

namespace Engelsystem\Controllers;

use Engelsystem\Database\Database;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;
use Illuminate\Database\Query\Expression as QueryExpression;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Collection;
use Engelsystem\Http\Exceptions\HttpForbidden;

class MessagesController extends BaseController
{
    /** @var Authenticator */
    protected $auth;

    /** @var LoggerInterface */
    protected $log;

    /** @var Redirector */
    protected $redirect;

    /** @var Response */
    protected $response;

    /** @var Response */
    protected $request;

    /** @var Database */
    protected $db;

    /** @var Message */
    protected $message;

    /** @var User */
    protected $user;

    /** @var string[] */
    protected $permissions = [
        'user_messages',
    ];

    /**
     * @param Authenticator   $auth
     * @param LoggerInterface $log
     * @param Redirector      $redirect
     * @param Response        $response
     * @param Request         $request
     * @param Database        $db
     * @param Message         $message
     * @param User            $user
     */
    public function __construct(
        Authenticator $auth,
        LoggerInterface $log,
        Redirector $redirect,
        Response $response,
        Request $request,
        Database $db,
        Message $message,
        User $user
    ) {
        $this->auth = $auth;
        $this->log = $log;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->request = $request;
        $this->db = $db;
        $this->message = $message;
        $this->user = $user;
    }

    public function index(): Response
    {
        return $this->listConversations();
    }

    /**
     * Returns a list of conversations of the current user, each containing the other participant,
     * the most recent message, and the number of unread messages.
     */
    public function listConversations(): Response
    {
        $current_user = $this->auth->user();

        $latest_messages = $this->latestMessagePerConversation($current_user);
        $numberOfUnreadMessages = $this->numberOfUnreadMessagesPerConversation($current_user);

        $conversations = [];
        foreach ($latest_messages as $msg) {
            $other_user = $msg->user_id == $current_user->id ? $msg->receiver : $msg->sender;
            $unread_messages = $numberOfUnreadMessages[$other_user->id] ?? 0;
            array_push($conversations, [
                'other_user' => $other_user,
                'latest_message' => $msg,
                'unread_messages' => $unread_messages
            ]);
        }

        $users = $this->user->orderBy('name')->get()
            ->except($current_user->id)
            ->mapWithKeys(function ($u) {
                return [ $u->id => $u->nameWithPronoun() ];
            });

        return $this->response->withView(
            'pages/messages/overview.twig',
            [
                'conversations' => $conversations,
                'users' => $users
                ]
        );
    }

    /**
     * Forwards to the conversation with the user of the given id.
     */
    public function toConversation(Request $request): Response
    {
        $data = $this->validate($request, [ 'user_id' => 'required|int' ]);
        return $this->redirect->to('/messages/' . $data['user_id']);
    }

    /**
     * Returns a list of messages between the current user and a user with the given id. The ids shall not be the same.
     * Unread messages will be marked as read during this call. Still, they will be shown as unread in the frontend to
     * highlight them to the user as new.
     */
    public function conversation(Request $request): Response
    {
        $current_user = $this->auth->user();
        $other_user = $this->user->findOrFail($request->getAttribute('user_id'));

        if ($current_user->id == $other_user->id) {
            throw new HttpForbidden('You can not start a conversation with yourself.');
        }

        $messages = $this->message
            ->where(function ($q) use ($current_user, $other_user) {
                $q->whereUserId($current_user->id)
                    ->whereReceiverId($other_user->id);
            })
            ->orWhere(function ($q) use ($current_user, $other_user) {
                $q->whereUserId($other_user->id)
                    ->whereReceiverId($current_user->id);
            })
            ->orderBy('created_at')
            ->get();

        $unread_messages = $messages->filter(function ($m) use ($other_user) {
            return $m->user_id == $other_user->id && !$m->read;
        });

        foreach ($unread_messages as $msg) {
            $msg->read = true;
            $msg->save();
            $msg->read = false; // change back to true to display it to the frontend one more time.
        }

        return $this->response->withView(
            'pages/messages/conversation.twig',
            ['messages' => $messages, 'other_user' => $other_user]
        );
    }

    /**
     * Sends a message to another user.
     */
    public function send(Request $request): Response
    {
        $current_user = $this->auth->user();

        $data = $this->validate($request, [ 'text' => 'required' ]);

        $other_user = $this->user->findOrFail($request->getAttribute('user_id'));

        if ($other_user->id == $current_user->id) {
            throw new HttpForbidden('You can not send a message to yourself.');
        }

        $new_message = new Message();
        $new_message->sender()->associate($current_user);
        $new_message->receiver()->associate($other_user);
        $new_message->text = $data['text'];
        $new_message->read = false;
        $new_message->save();

        $this->log->info(
            'User {from} has written a message to user {to}',
            [
                'from' => $current_user->id,
                'to' => $other_user->id
            ]
        );
        return $this->redirect->to('/messages/' . $other_user->id);
    }

    /**
     * Deletes a message from a given id, as long as this message was send by the current user. The given user_id
     * The given user_id is used to redirect back to the conversation with that user.
     */
    public function delete(Request $request): Response
    {
        $current_user = $this->auth->user();
        $other_user_id = $request->getAttribute('user_id');
        $msg_id = $request->getAttribute('msg_id');
        $msg = $this->message->findOrFail($msg_id);

        if ($msg->user_id == $current_user->id) {
            $msg->delete();

            $this->log->info(
                'User {from} deleted message {msg} in a conversation with user {to}',
                [
                    'from' => $current_user->id,
                    'to' => $other_user_id,
                    'msg' => $msg_id
                ]
            );
        } else {
            $this->log->warning(
                'User {from} tried to delete message {msg} which was not written by them, ' .
                'in a conversation with user {to}',
                [
                    'from' => $current_user->id,
                    'to' => $other_user_id,
                    'msg' => $msg_id
                ]
            );

            throw new HttpForbidden('You can not delete a message you haven\'t send');
        }

        return $this->redirect->to('/messages/' . $other_user_id);
    }

    public function numberOfUnreadMessages(): int
    {
        return $this->auth->user()
            ->messagesReceived()
            ->where('read', false)
            ->count();
    }

    protected function numberOfUnreadMessagesPerConversation($current_user): Collection
    {
        return $current_user->messagesReceived()
            ->select('user_id', $this->raw('count(*) as amount'))
            ->where('read', false)
            ->groupBy('user_id')
            ->get(['user_id', 'amount'])
            ->mapWithKeys(function ($unread) {
                return [ $unread->user_id => $unread->amount ];
            });
    }

    protected function latestMessagePerConversation($current_user): Collection
    {
        $latest_message_ids = $this->message
            ->select($this->raw('max(id) as last_id'))
            ->where('user_id', "=", $current_user->id)
            ->orWhere('receiver_id', "=", $current_user->id)
            ->groupBy($this->raw(
                '(CASE WHEN user_id = ' . $current_user->id .
                ' THEN receiver_id ELSE user_id END)'
            ));

        return $this->message
            ->joinSub($latest_message_ids, 'conversations', function ($join) {
                $join->on('messages.id', '=', 'conversations.last_id');
            })
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    protected function raw($value): QueryExpression
    {
        return $this->db->getConnection()->raw($value);
    }
}
