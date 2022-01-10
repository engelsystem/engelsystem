<?php

namespace Engelsystem\Helpers;

use Carbon\Carbon;
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

    /** @var string[] */
    protected $permissions;

    /** @var int|string|null */
    protected $passwordAlgorithm = PASSWORD_DEFAULT;

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

        $userId = $this->session->get('user_id');
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

    /**
     * @param string[]|string $abilities
     * @return bool
     */
    public function can($abilities): bool
    {
        $abilities = (array)$abilities;

        if (empty($this->permissions)) {
            $user = $this->user();

            if ($user) {
                $this->permissions = $this->getPermissionsByUser($user);

                $user->last_login_at = new Carbon();
                $user->save();
            } elseif ($this->session->get('user_id')) {
                $this->session->remove('user_id');
            }

            if (empty($this->permissions)) {
                $this->permissions = $this->getPermissionsByGroup(-10);
            }
        }

        foreach ($abilities as $ability) {
            if (!in_array($ability, $this->permissions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $login
     * @param string $password
     * @return User|null
     */
    public function authenticate(string $login, string $password)
    {
        /** @var User $user */
        $user = $this->userRepository->whereName($login)->first();
        if (!$user) {
            $user = $this->userRepository->whereEmail($login)->first();
        }

        if (!$user) {
            return null;
        }

        if (!$this->verifyPassword($user, $password)) {
            return null;
        }

        return $user;
    }

    /**
     * @param User   $user
     * @param string $password
     * @return bool
     */
    public function verifyPassword(User $user, string $password)
    {
        if (!password_verify($password, $user->password)) {
            return false;
        }

        if (password_needs_rehash($user->password, $this->passwordAlgorithm)) {
            $this->setPassword($user, $password);
        }

        return true;
    }

    /**
     * @param User   $user
     * @param string $password
     */
    public function setPassword(User $user, string $password)
    {
        $user->password = password_hash($password, $this->passwordAlgorithm);
        $user->save();
    }

    /**
     * @return int|string|null
     */
    public function getPasswordAlgorithm()
    {
        return $this->passwordAlgorithm;
    }

    /**
     * @param int|string|null $passwordAlgorithm
     */
    public function setPasswordAlgorithm($passwordAlgorithm)
    {
        $this->passwordAlgorithm = $passwordAlgorithm;
    }

    /**
     * @param User $user
     * @return array
     * @codeCoverageIgnore
     */
    protected function getPermissionsByUser($user)
    {
        return privileges_for_user($user->id);
    }

    /**
     * @param int $groupId
     * @return array
     * @codeCoverageIgnore
     */
    protected function getPermissionsByGroup(int $groupId)
    {
        return privileges_for_group($groupId);
    }
}
