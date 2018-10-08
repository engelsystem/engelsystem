<?php

namespace Engelsystem\Helpers;

use Engelsystem\Models\BaseModel;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\User as UserRepository;
use Symfony\Component\HttpFoundation\Session\Session;

class Authenticator
{
    /** @var UserRepository */
    protected $user = null;

    /** @var Session */
    protected $session;

    /** @var BaseModel */
    protected $userRepository;

    /**
     * @param Session        $session
     * @param UserRepository $userRepository
     */
    public function __construct(Session $session, UserRepository $userRepository)
    {
        $this->session = $session;
        $this->userRepository = $userRepository;
    }

    /**
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

        return $user;
    }
}
