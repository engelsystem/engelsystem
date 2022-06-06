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
use Illuminate\Support\Collection;
use Engelsystem\Http\Exceptions\HttpForbidden;

class MessagesController extends BaseController
{
    /** @var Authenticator */
    protected $auth;

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
     * @param Redirector      $redirect
     * @param Response        $response
     * @param Request         $request
     * @param Database        $db
     * @param Message         $message
     * @param User            $user
     */
    public function __construct(
        Authenticator $auth,
        Redirector $redirect,
        Response $response,
        Request $request,
        Database $db,
        Message $message,
        User $user
    ) {
        $this->auth = $auth;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->request = $request;
        $this->db = $db;
        $this->message = $message;
        $this->user = $user;
    }

    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->listConversations();
    }

    /**
     * Returns a list of conversations of the current user, each containing the other user,
     * the most recent message, and the number of unread messages.
     */
    public function listConversations(): Response
    {
        $currentUser = $this->auth->user();

        $latestMessages = $this->latestMessagePerConversation($currentUser);
        $numberOfUnreadMessages = $this->numberOfUnreadMessagesPerConversation($currentUser);

        $conversations = [];
        foreach ($latestMessages as $msg) {
            $otherUser = $msg->user_id == $currentUser->id ? $msg->receiver : $msg->sender;
            $unreadMessages = $numberOfUnreadMessages[$otherUser->id] ?? 0;

            $conversations[] = [
                'other_user' => $otherUser,
                'latest_message' => $msg,
                'unread_messages' => $unreadMessages,
            ];
        }

        $users = $this->user->orderBy('name')->get()
            ->except($currentUser->id)
            ->mapWithKeys(function ($u) {
                return [ $u->id => $u->name ];
            });
        $users->prepend($currentUser->name, $currentUser->id);

        return $this->response->withView(
            'pages/messages/overview.twig',
            [
                'conversations' => $conversations,
                'users' => $users
            ]
        );
    }

    /**
     * Redirects to the conversation with the user of the given id.
     */
    public function redirectToConversation(Request $request): Response
    {
        $data = $this->validate($request, ['user_id' => 'required|int']);
        return $this->redirect->to('/messages/' . $data['user_id'] . '#newest');
    }

    /**
     * Returns a list of messages between the current user and a user with the given id. Unread messages will be marked
     * as read during this call. Still, they will be shown as unread in the frontend to show that they are new.
     */
    public function messagesOfConversation(Request $request): Response
    {
        $currentUser = $this->auth->user();
        $otherUser = $this->user->findOrFail($request->getAttribute('user_id'));

        $messages = $this->message
            ->where(function ($query) use ($currentUser, $otherUser) {
                $query->whereUserId($currentUser->id)
                    ->whereReceiverId($otherUser->id);
            })
            ->orWhere(function ($query) use ($currentUser, $otherUser) {
                $query->whereUserId($otherUser->id)
                    ->whereReceiverId($currentUser->id);
            })
            ->orderBy('created_at')
            ->get();

        $unreadMessages = $messages->filter(function ($m) use ($otherUser) {
            return $m->user_id == $otherUser->id && !$m->read;
        });

        foreach ($unreadMessages as $msg) {
            $msg->read = true;
            $msg->save();
            $msg->read = false; // change back to true to display it to the frontend one more time.
        }

        return $this->response->withView(
            'pages/messages/conversation.twig',
            ['messages' => $messages, 'other_user' => $otherUser]
        );
    }

    /**
     * Sends a message to another user.
     */
    public function send(Request $request): Response
    {
        $currentUser = $this->auth->user();

        $data = $this->validate($request, ['text' => 'required']);

        $otherUser = $this->user->findOrFail($request->getAttribute('user_id'));

        $newMessage = new Message();
        $newMessage->sender()->associate($currentUser);
        $newMessage->receiver()->associate($otherUser);
        $newMessage->text = $data['text'];
        $newMessage->read = $otherUser->id == $currentUser->id; // if its to myself, I obviously read it.
        $newMessage->save();

        return $this->redirect->to('/messages/' . $otherUser->id . '#newest');
    }

    /**
     * Deletes a message with a given id, as long as this message was send by the current user.
     * The given user id is used to redirect back to the conversation with that user.
     */
    public function delete(Request $request): Response
    {
        $currentUser = $this->auth->user();
        $otherUserId = $request->getAttribute('user_id');
        $msgId = $request->getAttribute('msg_id');
        $msg = $this->message->findOrFail($msgId);

        if ($msg->user_id == $currentUser->id) {
            $msg->delete();
        } else {
            throw new HttpForbidden('You can not delete a message you haven\'t send');
        }

        return $this->redirect->to('/messages/' . $otherUserId . '#newest');
    }

    /**
     * The number of unread messages per conversation of the current user.
     * @return Collection of unread message amounts. Each object with key=other user, value=amount of unread messages
     */
    protected function numberOfUnreadMessagesPerConversation($currentUser): Collection
    {
        return $currentUser->messagesReceived()
            ->select('user_id', $this->raw('count(*) as amount'))
            ->where('read', false)
            ->groupBy('user_id')
            ->get(['user_id', 'amount'])
            ->mapWithKeys(function ($unread) {
                return [ $unread->user_id => $unread->amount ];
            });
    }

    /**
     * Returns the latest message for each conversation,
     * which were either send by or addressed to the current user.
     * @return Collection of messages
     */
    protected function latestMessagePerConversation($currentUser): Collection
    {
        /* requesting the IDs first, grouped by "conversation".
        The more complex grouping is required for associating the messages to the correct conversations.
        Without this, a database change might have been needed to realize the "conversations" concept. */
        $latestMessageIds = $this->message
            ->select($this->raw('max(id) as last_id'))
            ->where('user_id', '=', $currentUser->id)
            ->orWhere('receiver_id', '=', $currentUser->id)
            ->groupBy($this->raw(
                '(CASE WHEN user_id = ' . (int) $currentUser->id .
                ' THEN receiver_id ELSE user_id END)'
            ));

        // then getting the full message objects for each ID.
        return $this->message
            ->joinSub($latestMessageIds, 'conversations', function ($join) {
                $join->on('messages.id', '=', 'conversations.last_id');
            })
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * @param mixed $value
     * @return QueryExpression
     */
    protected function raw($value): QueryExpression
    {
        return $this->db->getConnection()->raw($value);
    }
}
