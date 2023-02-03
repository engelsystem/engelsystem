<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use Carbon\Carbon;
use Engelsystem\Models\Group;
use Engelsystem\Models\User\User;
use Engelsystem\Models\User\User as UserRepository;
use Illuminate\Support\Str;
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
     * Load the user from session or api auth
     */
    public function user(): ?User
    {
        if ($this->user) {
            return $this->user;
        }

        $this->user = $this->userFromSession();
        if (!$this->user && request()->getAttribute('route-api', false)) {
            $this->user = $this->userFromApi();
        }

        return $this->user;
    }

    /**
     * Load the user from session
     */
    public function userFromSession(): ?User
    {
        if ($this->user) {
            return $this->user;
        }

        $userId = $this->session->get('user_id');
        if (!$userId) {
            return null;
        }

        $this->user = $this
            ->userRepository
            ->find($userId);

        return $this->user;
    }

    /**
     * Get the user by its api key
     */
    public function userFromApi(): ?User
    {
        if ($this->user) {
            return $this->user;
        }

        $this->user = $this->userByHeaders();
        if ($this->user) {
            return $this->user;
        }

        $this->user = $this->userByQueryParam();

        return $this->user;
    }

    /**
     * @param string[]|string $abilities
     */
    public function can(array|string $abilities): bool
    {
        $abilities = (array) $abilities;

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

    /**
     * Get the user by authorization bearer or x-api-key headers
     */
    protected function userByHeaders(): ?User
    {
        $header = $this->request->getHeader('authorization');
        if (!empty($header) && Str::startsWith(Str::lower($header[0]), 'bearer ')) {
            return $this->userByApiKey(Str::substr($header[0], 7));
        }

        $header = $this->request->getHeader('x-api-key');
        if (!empty($header)) {
            return $this->userByApiKey($header[0]);
        }

        return null;
    }

    /**
     * Get the user by query parameters
     */
    protected function userByQueryParam(): ?User
    {
        $params = $this->request->getQueryParams();
        if (!empty($params['key'])) {
            $this->user = $this->userByApiKey($params['key']);
        }

        return $this->user;
    }

    /**
     * Get the user by its api key
     */
    protected function userByApiKey(string $key): ?User
    {
        $this->user = $this
            ->userRepository
            ->whereApiKey($key)
            ->first();

        return $this->user;
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
