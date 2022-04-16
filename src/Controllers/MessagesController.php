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

class MessagesController extends BaseController
{
    use CleanupModel;

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
        return $this->list_conversations();
    }

    /**
     * Returns a list of conversations of the current user, each containing the other participant,
     * the most recent message, and the number of unread messages.
     * @returns Response
     */
    public function list_conversations(): Response
    {
        $current_user = $this->auth->user();

        $latest_messages = $this->latest_message_per_conversation($current_user);
        $number_of_unread_messages = $this->number_of_unread_messages_per_conversation($current_user);

        $conversations = [];
        foreach ($latest_messages as $msg) {
            $other_user = $msg->user_id == $current_user->id ? $msg->receiver : $msg->sender;
            $unread_messages = $number_of_unread_messages[$other_user->id] ?? 0;
            array_push($conversations, [
                'other_user' => $other_user,
                'latest_message' => $msg,
                'unread_messages' => $unread_messages
            ]);
        }

        return $this->response->withView(
            'pages/messages/overview.twig',
            ['conversations' => $conversations]
        );
    }

    protected function number_of_unread_messages_per_conversation($current_user): Collection
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

    protected function latest_message_per_conversation($current_user): Collection
    {
        $latest_message_ids = $this->message
            ->select($this->raw('max(id) as last_id'))
            ->where('user_id', "=", $current_user->id)
            ->orWhere('receiver_id', "=", $current_user->id)
            ->groupBy($this->raw('IF (user_id = '.$current_user->id.', receiver_id, user_id)'));

        return $this->message
            ->joinSub($latest_message_ids, 'conversations', function($join) {
                $join->on('messages.id', '=' , 'conversations.last_id');
            })
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Returns a list of messages between the current user and a user with the given id. Unread messages will be marked
     * as read. Still, they will be shown as unread in the frontend to highlight them as new.
     * @param Request $request
     * @return Response
     */
    public function conversation(Request $request): Response
    {
        $current_user = $this->auth->user();
        $other_user = $this->user->findOrFail($request->getAttribute('id'));

        $messages = $this->message
            ->where(function($q) use ($current_user, $other_user) {
                $q->whereUserId($current_user->id)
                    ->whereReceiverId($other_user->id);
            })
            ->orWhere(function($q) use ($current_user, $other_user) {
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

    public function send(Request $request): Response
    {
        $data = $this->validate($request, [ 'text' => 'required' ]);

        $other_user = $this->user->findOrFail($request->getAttribute('id'));

        $new_message = new Message();
        $new_message->sender()->associate($this->auth->user());
        $new_message->receiver()->associate($other_user);
        $new_message->text = $data['text'];
        $new_message->read = false;
        $new_message->save();

        return $this->redirect->to('/messages/'. $other_user->id);
    }

    protected function raw($value): QueryExpression
    {
        return $this->db->getConnection()->raw($value);
    }
}
