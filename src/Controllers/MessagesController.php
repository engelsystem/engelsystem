<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;
use Illuminate\Support\Collection;
use Psr\Http\Message\RequestInterface;

class MessagesController extends BaseController
{
    protected RequestInterface $request;

    /** @var string[] */
    protected array $permissions = [
        'user_messages',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected Redirector $redirect,
        protected Response $response,
        Request $request,
        protected User $user
    ) {
        $this->request = $request;
    }

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

        /** @var Collection $users */
        $users = $this->user->orderBy('name')->get()
            ->except($currentUser->id)
            ->mapWithKeys(function (User $u) {
                return [$u->id => $u->displayName];
            });

        $users->prepend($currentUser->displayName, $currentUser->id);

        return $this->response->withView(
            'pages/messages/index.twig',
            [
                'conversations' => $conversations,
                'users' => $users,
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
        $userId = (int) $request->getAttribute('user_id');

        $currentUser = $this->auth->user();
        $otherUser = $this->user->findOrFail($userId);

        $messages = Message::conversationBetween($currentUser, $otherUser);

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
        $userId = (int) $request->getAttribute('user_id');

        $currentUser = $this->auth->user();

        $data = $this->validate($request, ['text' => 'required']);

        $otherUser = $this->user->findOrFail($userId);

        Message::sendMessage($currentUser, $otherUser, $data['text']);

        return $this->redirect->to('/messages/' . $otherUser->id . '#newest');
    }

    /**
     * Deletes a message with a given id, as long as this message was send by the current user.
     * The given user id is used to redirect back to the conversation with that user.
     */
    public function delete(Request $request): Response
    {
        $otherUserId = (int) $request->getAttribute('user_id');
        $msgId = (int) $request->getAttribute('msg_id');

        $currentUser = $this->auth->user();
        Message::deleteOwn($msgId, $currentUser);

        return $this->redirect->to('/messages/' . $otherUserId . '#newest');
    }

    /**
     * The number of unread messages per conversation of the current user.
     * @return Collection of unread message amounts. Each object with key=other user, value=amount of unread messages
     */
    protected function numberOfUnreadMessagesPerConversation(User $currentUser): Collection
    {
        return Message::unreadCountsPerConversationFor($currentUser);
    }

    /**
     * Returns the latest message for each conversation,
     * which were either send by or addressed to the current user.
     * @return Collection of messages
     */
    protected function latestMessagePerConversation(User $currentUser): Collection
    {
        return Message::latestPerConversationFor($currentUser)
            ->load(['receiver.personalData', 'receiver.state']);
    }
}
