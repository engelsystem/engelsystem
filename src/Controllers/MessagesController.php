<?php

namespace Engelsystem\Controllers;

use Engelsystem\Database\Database;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Response;
use Engelsystem\Models\Message;
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

    /** @var Database */
    protected $db;

    /** @var Message */
    protected $message;

    /**
     * @param Authenticator   $auth
     * @param LoggerInterface $log
     * @param Redirector      $redirect
     * @param Response        $response
     */
    public function __construct(
        Authenticator $auth,
        LoggerInterface $log,
        Redirector $redirect,
        Response $response,
        Database $db,
        Message $message
    ) {
        $this->auth = $auth;
        $this->log = $log;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->db = $db;
        $this->message = $message;
    }

    /**
     * @returns Response with the list of conversations, each containing the other participant,
     * the most recent message, and the number of unread messages.
     */
    public function index(): Response
    {
        $user = $this->auth->user();

        $latest_messages = $this->latest_message_per_conversation($user);
        $number_of_unread_messages = $this->number_of_unread_messages_per_conversation($user);

        $conversations = [];
        foreach ($latest_messages as $msg) {
            $other_user = $msg->user_id == $user->id ? $msg->receiver : $msg->sender;
            $unread_messages = $number_of_unread_messages[$other_user->id] ?? 0;
            array_push($conversations, [
                'other_user' => $other_user,
                'latest_message' => $msg,
                'unread_messages' => $unread_messages
            ]);
        }

        return $this->response->withView(
            'pages/messages.twig',
            ['conversations' => $conversations]
        );
    }

    protected function number_of_unread_messages_per_conversation($user): Collection
    {
        return $user->messagesReceived()
            ->select('user_id', $this->raw('count(*) as amount'))
            ->where('read', false)
            ->groupBy('user_id')
            ->get(['user_id', 'amount'])
            ->mapWithKeys(function ($unread) {
                return [ $unread->user_id => $unread->amount ];
            });
    }

    protected function latest_message_per_conversation($user): Collection
    {
        $latest_message_ids = $this->message
            ->select($this->raw('max(id) as last_id'))
            ->where('user_id', "=", $user->id)
            ->orWhere('receiver_id', "=", $user->id)
            ->groupBy($this->raw('IF (user_id = '.$user->id.', receiver_id, user_id)'));

        return $this->message
            ->joinSub($latest_message_ids, 'conversations', function($join) {
                $join->on('messages.id', '=' , 'conversations.last_id');
            })
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    protected function raw($value): QueryExpression
    {
        return $this->db->getConnection()->raw($value);
    }
}
