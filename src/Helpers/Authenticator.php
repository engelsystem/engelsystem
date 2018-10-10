<?php

namespace Engelsystem\Helpers;

use Engelsystem\Models\User\User;
use Engelsystem\Models\User\User as UserRepository;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Authenticator
{
    /** @var User */
    protected $user = null;

    /** @var ServerRequestInterface */
    protected $request;

    /** @var Session */
    protected $session;

    /** @var UserRepository */
    protected $userRepository;

    /**
     * @param ServerRequestInterface $request
     * @param Session                $session
     * @param UserRepository         $userRepository
     */
    public function __construct(ServerRequestInterface $request, Session $session, UserRepository $userRepository)
    {
        $this->request = $request;
        $this->session = $session;
        $this->userRepository = $userRepository;
    }

    /**
     * Load the user from session
     *
     * @return User|null
     */
    public function user()
    {
        if ($this->user) {
            return $this->user;
        }

        $userId = $this->session->get('uid');
        if (!$userId) {
            return null;
        }

        $user = $this
            ->userRepository
            ->find($userId);
        if (!$user) {
            return null;
        }

        $this->user = $user;

        return $this->user;
    }

    /**
     * Get the user by his api key
     *
     * @param string $parameter
     * @return User|null
     */
    public function apiUser($parameter = 'api_key')
    {
        if ($this->user) {
            return $this->user;
        }

        $params = $this->request->getQueryParams();
        if (!isset($params[$parameter])) {
            return null;
        }

        $user = $this
            ->userRepository
            ->whereApiKey($params[$parameter])
            ->first();
        if (!$user) {
            return $this->user();
        }

        $this->user = $user;

        return $this->user;
    }
}
