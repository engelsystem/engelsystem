<?php

namespace Engelsystem\Helpers;

use Carbon\Carbon;
use Engelsystem\Models\Group;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\User as UserRepository;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class Authenticator
{
    protected ?User $user = null;

    /** @var string[] */
    protected array $permissions = [];

    protected int|string|null $passwordAlgorithm = PASSWORD_DEFAULT;

    protected int $defaultRole = 20;

    protected int $guestRole = 10;

    public function __construct(
        protected ServerRequestInterface $request,
        protected Session $session,
        protected UserRepository $userRepository
    ) {
    }

    /**
     * Load the user from session
     */
    public function user(): ?User
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
     */
    public function apiUser(string $parameter = 'api_key'): ?User
    {
        if ($this->user) {
            return $this->user;
        }

        $params = $this->request->getQueryParams();
        if (!isset($params[$parameter])) {
            return null;
        }

        /** @var User|null $user */
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
     */
    public function can(array|string $abilities): bool
    {
        $abilities = (array)$abilities;

        if (empty($this->permissions)) {
            $user = $this->user();

            if ($user) {
                $this->permissions = $user->privileges->pluck('name')->toArray();

                $user->last_login_at = new Carbon();
                $user->save();
            } elseif ($this->session->get('user_id')) {
                $this->session->remove('user_id');
            }

            if (empty($this->permissions)) {
                /** @var Group $group */
                $group = Group::find($this->guestRole);
                $this->permissions = $group->privileges->pluck('name')->toArray();
            }
        }

        foreach ($abilities as $ability) {
            if (!in_array($ability, $this->permissions)) {
                return false;
            }
        }

        return true;
    }

    public function authenticate(string $login, string $password): ?User
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

    public function verifyPassword(User $user, string $password): bool
    {
        if (!password_verify($password, $user->password)) {
            return false;
        }

        if (password_needs_rehash($user->password, $this->passwordAlgorithm)) {
            $this->setPassword($user, $password);
        }

        return true;
    }

    public function setPassword(User $user, string $password): void
    {
        $user->password = password_hash($password, $this->passwordAlgorithm);
        $user->save();
    }

    public function getPasswordAlgorithm(): int|string|null
    {
        return $this->passwordAlgorithm;
    }

    public function setPasswordAlgorithm(int|string|null $passwordAlgorithm): void
    {
        $this->passwordAlgorithm = $passwordAlgorithm;
    }

    public function getDefaultRole(): int
    {
        return $this->defaultRole;
    }

    public function setDefaultRole(int $defaultRole): void
    {
        $this->defaultRole = $defaultRole;
    }

    public function getGuestRole(): int
    {
        return $this->guestRole;
    }

    public function setGuestRole(int $guestRole): void
    {
        $this->guestRole = $guestRole;
    }
}
