<?php

namespace Engelsystem\Controllers;

use Engelsystem\Database\Database;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Redirector;
use Engelsystem\Http\Response;
use Engelsystem\Models\Message;
use Illuminate\Database\Query\Expression as QueryExpression;
use Psr\Log\LoggerInterface;

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
     * @return Response
     */
    public function index(): Response
    {
        $user = $this->auth->user();

        $idsForLatestMessagesPerConversation = $this->message
            ->select($this->raw('max(id) as last_id'))
            ->where('user_id', "=", $user->id)
            ->orWhere('receiver_id', "=", $user->id)
            ->groupBy($this->raw('IF (user_id = '.$user->id.', receiver_id, user_id)'));

        $latestMessagesPerConversation = $this->message
            ->joinSub($idsForLatestMessagesPerConversation, 'conversations', function($join) {
                $join->on('messages.id', '=' , 'conversations.last_id');
            })
            ->get();

        return $this->response->withView(
            'pages/messages.twig',
            ['latestMessagesPerConversation' => $latestMessagesPerConversation]
        );
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
