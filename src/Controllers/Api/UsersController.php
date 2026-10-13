<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Api;

use Engelsystem\Config\GoodieType;
use Engelsystem\Controllers\Api\Resources\UserAngelTypeReferenceResource;
use Engelsystem\Controllers\Api\Resources\UserDetailResource;
use Engelsystem\Controllers\Api\Resources\UserResource;
use Engelsystem\Controllers\Api\Resources\WorklogResource;
use Engelsystem\Helpers\Goodie;
use Engelsystem\Helpers\UserVouchers;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\BaseModel;
use Engelsystem\Models\Group;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\User\PasswordReset;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Illuminate\Database\Eloquent\Collection;

class UsersController extends ApiController
{
    use UsesAuth;

    /**
     * Readable with the web privilege of the corresponding website page: the user list requires
     * "admin_user", profiles (including their shifts and work logs) are open to everyone and
     * filtered like on the website, angel type members are shown on the angel type page
     */
    protected function webPermission(string $method): string|array|bool|null
    {
        return match ($method) {
            'index' => 'admin_user',
            'user', 'worklogs' => true,
            'entriesByAngeltype' => 'angeltypes.view',
            default => null,
        };
    }

    public function index(): Response
    {
        $models = User::query()
            ->orderBy('name')
            ->get();

        $models = $models->map(function (BaseModel $model) {
            return UserResource::toIdentifierArray($model);
        });

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function user(Request $request): Response
    {
        $id = $request->getAttribute('user_id');
        $user = $this->getUser($id);

        $isSelf = $user->id == $this->auth?->user()?->id;

        $userData = $isSelf ? (new UserDetailResource($user))->toArray() : $this->publicUserData($user);
        $data = ['data' => [...$userData, ...$this->profileData($user, $isSelf)]];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function entriesByAngeltype(Request $request): Response
    {
        $id = (int) $request->getAttribute('angeltype_id');
        /** @var AngelType $angelType */
        $angelType = AngelType::findOrFail($id);

        /** @var User[]|Collection $models */
        $models = $angelType->userAngelTypes()
            ->with(['personalData', 'contact', 'license', 'state'])
            ->orderBy('name')
            ->get();

        // Like on the angel type page, unconfirmed members are only listed for users managing them
        $managesMembers = $this->hasApiPrivilege()
            || $this->auth?->can('user.angeltypes.edit')
            || $this->auth?->user()?->isAngelTypeSupporter($angelType);

        $models = $models
            ->map(function (User $model) {
                // Patch to use the existing user model instead of a partially populated one
                $model->pivot->setRelatedModel($model);
                return $model->pivot;
            })
            ->filter(fn(UserAngelType $member) => $managesMembers || $member->isConfirmed)
            ->map(fn(UserAngelType $member) => [
                ...(new UserAngelTypeReferenceResource($member))->toArray(),
                ...$this->memberData($member->pivotRelated, $angelType),
            ])
            ->values();

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }

    public function worklogs(Request $request): Response
    {
        $id = $request->getAttribute('user_id');
        $user = $this->getUser($id);

        $models = $user->worklogs();
        if (!$this->profileShiftsVisible($user)) {
            $models->whereRaw('1 = 0');
        }

        $models = $models
            ->orderBy('worked_at')
            ->get();

        $models = WorklogResource::collection($models);

        $data = ['data' => $models];
        return $this->response
            ->withContent(json_encode($data));
    }

    /**
     * Without the "api" privilege, other users' profile data is limited to what their profile page
     * shows, unless the user may edit users ("admin_user")
     */
    protected function publicUserData(User $user): array
    {
        $data = (new UserResource($user))->toArray();
        if ($this->hasApiPrivilege() || $this->auth?->can('admin_user')) {
            return $data;
        }

        if (!config('enable_full_name')) {
            $data['first_name'] = null;
            $data['last_name'] = null;
        }
        if (!config('enable_pronoun')) {
            $data['pronoun'] = null;
        }
        if (!config('enable_dect')) {
            $data['contact']['dect'] = null;
        }
        if (!config('enable_mobile_show') || !$user->settings?->mobile_show) {
            $data['contact']['mobile'] = null;
        }

        return $data;
    }

    /**
     * Profile details as shown on the user's profile page on the website
     */
    protected function profileData(User $user, bool $isSelf): array
    {
        $adminUser = (bool) $this->auth?->can('admin_user');
        $goodieEnabled = GoodieType::from(config('goodie_type')) !== GoodieType::None;

        $nextShift = null;
        if ($user->state?->arrived) {
            /** @var ShiftEntry|null $nextShiftEntry */
            $nextShiftEntry = $user->upcomingShiftEntries()->first();
            $nextShift = $nextShiftEntry ? [
                'starts_at' => $nextShiftEntry->shift->start,
                'ends_at' => $nextShiftEntry->shift->end,
            ] : null;
        }

        $vouchers = null;
        if (config('enable_voucher') && ($isSelf || $adminUser)) {
            $vouchers = [
                'received' => (int) $user->state?->got_voucher,
                'available' => (int) $user->state?->got_voucher + UserVouchers::eligibleVoucherCount($user),
            ];
        }

        $goodieAdmin = (bool) $this->auth?->can('user.goodie.edit');

        return [
            'groups' => $user->groups
                ->sortBy('id')
                ->map(fn(Group $group) => ['id' => $group->id, 'name' => $group->name])
                ->values()
                ->toArray(),
            'arrived' => (bool) $user->state?->arrived,
            'next_shift' => $nextShift,
            'vouchers' => $vouchers,
            'goodie_score' => $goodieEnabled && ($isSelf || $adminUser || $goodieAdmin)
                ? Goodie::userScore($user)
                : null,
            'user_info' => $this->auth?->can('user.info.view') ? $user->state?->user_info : null,
            'admin_state' => $adminUser ? $this->adminStateData($user, $goodieEnabled) : null,
        ];
    }

    /**
     * The user state only shown to users with the "admin_user" privilege
     */
    protected function adminStateData(User $user, bool $goodieEnabled): array
    {
        return [
            'arrival_date' => $user->state?->arrival_date,
            'planned_arrival_date' => $user->personalData?->planned_arrival_date,
            'active' => (bool) $user->state?->active,
            'force_active' => config('enable_force_active') ? (bool) $user->state?->force_active : null,
            'force_food' => config('enable_force_food') ? (bool) $user->state?->force_food : null,
            'got_goodie' => $goodieEnabled ? (bool) $user->state?->got_goodie : null,
            'freeloader' => $user->isFreeloader(),
            'password_reset' => PasswordReset::whereUserId($user->id)
                ->where('created_at', '>', $user->last_login_at ?: '')
                ->exists(),
        ];
    }

    /**
     * Whether the profile page on the website shows the user's shifts and work logs: to the user
     * themself, user admins, goodie admins and supporters of one of the user's angel types
     */
    protected function profileShiftsVisible(User $user): bool
    {
        $currentUser = $this->auth?->user();
        if (
            $currentUser?->id == $user->id
            || $this->hasApiPrivilege()
            || $this->auth?->can('admin_user')
            || $this->auth?->can('user.goodie.edit')
        ) {
            return true;
        }

        return (bool) $currentUser
            ?->userAngelTypes()
            ->wherePivot('supporter', true)
            ->whereIn('angel_types.id', $user->userAngelTypes()->pluck('angel_types.id'))
            ->exists();
    }

    /**
     * Member details as shown on the angel type page on the website
     */
    protected function memberData(User $user, AngelType $angelType): array
    {
        $isSupporter = (bool) $this->auth?->user()?->isAngelTypeSupporter($angelType);
        $editsAngelTypes = (bool) $this->auth?->can('angeltypes.edit');
        $license = $user->license;

        $driverLicense = null;
        if (
            config('driving_license_enabled')
            && $angelType->requires_driver_license
            && ($isSupporter || $editsAngelTypes || $this->auth?->can('user.drive.edit'))
        ) {
            $driverLicense = [
                'confirmed' => (bool) $license?->drive_confirmed,
                'wants_to_drive' => (bool) $license?->wantsToDrive(),
                'has_car' => (bool) $license?->has_car,
                'car' => (bool) $license?->drive_car,
                'transporter_3_5t' => (bool) $license?->drive_3_5t,
                'truck_7_5t' => (bool) $license?->drive_7_5t,
                'truck_12t' => (bool) $license?->drive_12t,
                'forklift' => (bool) $license?->drive_forklift,
            ];
        }

        $ifsg = null;
        if (
            config('ifsg_enabled')
            && $angelType->requires_ifsg_certificate
            && ($isSupporter || $editsAngelTypes || $this->auth?->can('user.ifsg.edit'))
        ) {
            $ifsg = [
                'confirmed' => (bool) $license?->ifsg_confirmed,
                'certificate' => (bool) $license?->ifsg_certificate,
                'certificate_light' => config('ifsg_light_enabled') ? (bool) $license?->ifsg_certificate_light : null,
            ];
        }

        $goodieType = GoodieType::from(config('goodie_type'));
        $goodieVisible = $goodieType !== GoodieType::None
            && $this->auth?->can('angeltypes.goodie.list')
            && $this->auth?->can('user.goodie.edit');

        return [
            'pronoun' => config('enable_pronoun') ? $user->personalData?->pronoun : null,
            'dect' => config('enable_dect') ? $user->contact?->dect : null,
            'driver_license' => $driverLicense,
            'ifsg' => $ifsg,
            'got_goodie' => $goodieVisible ? (bool) $user->state?->got_goodie : null,
            'shirt_size' => $goodieVisible && $goodieType === GoodieType::Tshirt
                ? $user->personalData?->shirt_size
                : null,
        ];
    }
}
