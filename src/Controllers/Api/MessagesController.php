<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Controllers\Api\Resources\MessageResource;
use Engelsystem\Controllers\Api\Resources\UserResource;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\Message;
use Engelsystem\Models\User\User;

class MessagesController extends ApiController
{
    use UsesAuth;

    /**
     * Messages require the "user_messages" privilege, same as on the website. The read-only "api"
     * privilege only grants reading them, never sending, deleting or marking them as read.
     */
    protected function webPermission(string $method): string|array|bool|null
    {
        return 'user_messages';
    }

    public function index(): Response
    {
        $currentUser = $this->auth->user();
        $latest = Message::latestPerConversationFor($currentUser);
        $unread = Message::unreadCountsPerConversationFor($currentUser);

        $conversations = $latest->map(function (Message $msg) use ($currentUser, $unread) {
            $otherUser = $msg->user_id == $currentUser->id ? $msg->receiver : $msg->sender;
            return [
                'other_user' => UserResource::toIdentifierArray($otherUser),
                'latest_message' => (new MessageResource($msg))->toArray(),
                'unread_messages' => $unread[$otherUser->id] ?? 0,
            ];
        });

        $data = ['data' => $conversations->values()];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function show(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $currentUser = $this->auth->user();
        $otherUser = User::findOrFail($userId);

        $data = [
            'data' => [
                'other_user' => UserResource::toIdentifierArray($otherUser),
                'messages' => MessageResource::collection(Message::conversationBetween($currentUser, $otherUser)),
            ],
        ];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function send(Request $request): Response
    {
        $userId = (int) $request->getAttribute('user_id');
        $currentUser = $this->auth->user();
        $data = $this->validate($request, ['text' => 'required']);
        $otherUser = User::findOrFail($userId);

        $newMessage = Message::sendMessage($currentUser, $otherUser, $data['text']);

        $data = ['data' => (new MessageResource($newMessage))->toArray()];
        return $this->response
            ->withStatus(201)
            ->withContent(json_encode($data));
    }

    public function delete(Request $request): Response
    {
        $msgId = (int) $request->getAttribute('msg_id');
        Message::deleteOwn($msgId, $this->auth->user());

        return $this->response
            ->withStatus(204);
    }

    public function setRead(Request $request): Response
    {
        $msgId = (int) $request->getAttribute('msg_id');
        // 'required' maps to Respect's NotEmpty, which treats false as "empty" - use boolType
        // instead so both true and false are accepted, only a missing/non-boolean value fails.
        $data = $this->validate($request, ['read' => 'boolType']);
        $message = Message::setRead($msgId, $this->auth->user(), (bool) $data['read']);

        $data = ['data' => (new MessageResource($message))->toArray()];
        return $this->response
            ->withContent(json_encode($data));
    }
}
