<?php

use Engelsystem\Mail\EngelsystemMailer;
use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Illuminate\Database\Eloquent\Collection;

/**
 * Display a hint for team/angeltype supporters if there are unconfirmed users for his angeltype.
 *
 * @return string|null
 */
function user_angeltypes_unconfirmed_hint()
{
    $restrictedSupportedAngelTypes = auth()
        ->user()
        ->userAngelTypes()
        ->wherePivot('supporter', true)
        ->where('restricted', true)
        ->get();

    /** @var Collection|UserAngelType[] $unconfirmed_user_angeltypes */
    $unconfirmed_user_angeltypes = UserAngelType::query()
        ->with('AngelType')
        ->select(['user_angel_type.*', UserAngelType::query()->raw('count(angel_type_id) as users_count')])
        ->whereIn('angel_type_id', $restrictedSupportedAngelTypes->pluck('id')->toArray())
        ->whereNull('confirm_user_id')
        ->groupBy('angel_type_id')
        ->get();

    if (!$unconfirmed_user_angeltypes->count()) {
        return null;
    }

    $unconfirmed_links = [];
    foreach ($unconfirmed_user_angeltypes as $user_angeltype) {
        $unconfirmed_links[] = '<a href="'
            . url('/angeltypes', ['action' => 'view', 'angeltype_id' => $user_angeltype->angel_type_id])
            . '">' . htmlspecialchars($user_angeltype->angelType->name)
            . ' (+' . $user_angeltype->count . ')'
            . '</a>';
    }

    $count = $unconfirmed_user_angeltypes->count();
    return
        _e(
            'There is %d unconfirmed angeltype.',
            'There are %d unconfirmed angeltypes.',
            $count,
            [$count]
        )
        . ' ' . __('Angel types which need approvals:')
        . ' ' . join(', ', $unconfirmed_links);
}

/**
 * Remove all unconfirmed users from a specific angeltype.
 *
 * @return array
 */
function user_angeltypes_delete_all_controller(): array
{
    $request = request();

    if (!$request->has('angeltype_id')) {
        error(__('Angeltype doesn\'t exist.'));
        throw_redirect(url('/angeltypes'));
    }

    $angeltype = AngelType::findOrFail($request->input('angeltype_id'));
    if (!auth()->user()->isAngelTypeSupporter($angeltype) && !auth()->can('admin_user_angeltypes')) {
        error(__('You are not allowed to delete all users for this angeltype.'));
        throw_redirect(url('/angeltypes'));
    }

    if ($request->hasPostData('deny_all')) {
        UserAngelType::whereAngelTypeId($angeltype->id)
            ->whereNull('confirm_user_id')
            ->delete();

        engelsystem_log(sprintf('Denied all users for angeltype %s', AngelType_name_render($angeltype, true)));
        success(sprintf(__('Denied all users for angeltype %s.'), $angeltype->name));
        throw_redirect(url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]));
    }

    return [
        __('Deny all users'),
        UserAngelTypes_delete_all_view($angeltype),
    ];
}

/**
 * Confirm all unconfirmed users for an angeltype.
 *
 * @return array
 */
function user_angeltypes_confirm_all_controller(): array
{
    $user = auth()->user();
    $request = request();

    if (!$request->has('angeltype_id')) {
        error(__('Angeltype doesn\'t exist.'));
        throw_redirect(url('/angeltypes'));
    }

    $angeltype = AngelType::findOrFail($request->input('angeltype_id'));
    if (!auth()->can('admin_user_angeltypes') && !$user->isAngelTypeSupporter($angeltype)) {
        error(__('You are not allowed to confirm all users for this angeltype.'));
        throw_redirect(url('/angeltypes'));
    }

    if ($request->hasPostData('confirm_all')) {
        /** @var Collection|User[] $users */
        $users = $angeltype->userAngelTypes()->wherePivot('confirm_user_id', '=', null)->get();
        UserAngelType::whereAngelTypeId($angeltype->id)
            ->whereNull('confirm_user_id')
            ->update(['confirm_user_id' => $user->id]);

        engelsystem_log(sprintf('Confirmed all users for angeltype %s', AngelType_name_render($angeltype, true)));
        success(sprintf(__('Confirmed all users for angeltype %s.'), $angeltype->name));

        foreach ($users as $user) {
            user_angeltype_confirm_email($user, $angeltype);
        }

        throw_redirect(url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]));
    }

    return [
        __('Confirm all users'),
        UserAngelTypes_confirm_all_view($angeltype),
    ];
}

/**
 * Confirm a user for an angeltype.
 *
 * @return array
 */
function user_angeltype_confirm_controller(): array
{
    $user = auth()->user();
    $request = request();

    if (!$request->has('user_angeltype_id')) {
        error(__('User angeltype doesn\'t exist.'));
        throw_redirect(url('/angeltypes'));
    }

    /** @var UserAngelType $user_angeltype */
    $user_angeltype = UserAngelType::findOrFail($request->input('user_angeltype_id'));
    $angeltype = $user_angeltype->angelType;
    if (!$user->isAngelTypeSupporter($angeltype) && !auth()->can('admin_user_angeltypes')) {
        error(__('You are not allowed to confirm this users angeltype.'));
        throw_redirect(url('/angeltypes'));
    }

    $user_source = $user_angeltype->user;
    if ($request->hasPostData('confirm_user')) {
        $user_angeltype->confirmUser()->associate($user);
        $user_angeltype->save();

        engelsystem_log(sprintf(
            '%s confirmed for angeltype %s',
            User_Nick_render($user_source, true),
            AngelType_name_render($angeltype, true)
        ));
        success(sprintf(__('%s confirmed for angeltype %s.'), $user_source->displayName, $angeltype->name));

        user_angeltype_confirm_email($user_source, $angeltype);

        throw_redirect(url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]));
    }

    return [
        __('Confirm angeltype for user'),
        UserAngelType_confirm_view($user_angeltype, $user_source, $angeltype),
    ];
}

function user_angeltype_confirm_email(User $user, AngelType $angeltype): void
{
    if (!$user->settings->email_shiftinfo) {
        return;
    }

    /** @var EngelsystemMailer $mailer */
    $mailer = app(EngelsystemMailer::class);
    $mailer->sendViewTranslated(
        $user,
        'notification.angeltype.confirmed',
        'emails/angeltype-confirmed',
        ['name' => $angeltype->name, 'angeltype' => $angeltype, 'username' => $user->displayName]
    );
}

function user_angeltype_add_email(User $user, AngelType $angeltype): void
{
    if (!$user->settings->email_shiftinfo || $user->id == auth()->user()->id) {
        return;
    }

    /** @var EngelsystemMailer $mailer */
    $mailer = app(EngelsystemMailer::class);
    $mailer->sendViewTranslated(
        $user,
        'notification.angeltype.added',
        'emails/angeltype-added',
        ['name' => $angeltype->name, 'angeltype' => $angeltype, 'username' => $user->displayName]
    );
}

/**
 * Remove a user from an Angeltype.
 *
 * @return array
 */
function user_angeltype_delete_controller(): array
{
    $request = request();
    $user = auth()->user();

    if (!$request->has('user_angeltype_id')) {
        error(__('User angeltype doesn\'t exist.'));
        throw_redirect(url('/angeltypes'));
    }

    /** @var UserAngelType $user_angeltype */
    $user_angeltype = UserAngelType::findOrFail($request->input('user_angeltype_id'));
    $angeltype = $user_angeltype->angelType;
    $user_source = $user_angeltype->user;
    $isOwnAngelType = $user->id == $user_source->id;
    if (
        !$isOwnAngelType
        && !$user->isAngelTypeSupporter($angeltype)
        && !auth()->can('admin_user_angeltypes')
    ) {
        error(__('You are not allowed to delete this users angeltype.'));
        throw_redirect(url('/angeltypes'));
    }

    if ($request->hasPostData('delete')) {
        $user_angeltype->delete();

        engelsystem_log(sprintf('User "%s" removed from "%s".', User_Nick_render($user_source, true), $angeltype->name));
        success(sprintf($isOwnAngelType ? __('You successfully left "%2$s".') : __('User "%s" removed from "%s".'), $user_source->displayName, $angeltype->name));

        throw_redirect(url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]));
    }

    return [
        __('Leave angeltype'),
        UserAngelType_delete_view($user_angeltype, $user_source, $angeltype, $isOwnAngelType),
    ];
}

/**
 * Update an UserAngelType.
 *
 * @return array
 */
function user_angeltype_update_controller(): array
{
    $supporter = false;
    $request = request();

    if (!auth()->can('admin_angel_types') && !config('supporters_can_promote')) {
        error(__('You are not allowed to set supporter rights.'));
        throw_redirect(url('/angeltypes'));
    }

    if (!$request->has('user_angeltype_id')) {
        error(__('User angeltype doesn\'t exist.'));
        throw_redirect(url('/angeltypes'));
    }

    if ($request->has('supporter') && preg_match('/^[01]$/', $request->input('supporter'))) {
        $supporter = $request->input('supporter') == '1';
    } else {
        error(__('No supporter update given.'));
        throw_redirect(url('/angeltypes'));
    }

    /** @var UserAngelType $user_angeltype */
    $user_angeltype = UserAngelType::findOrFail($request->input('user_angeltype_id'));
    $angeltype = $user_angeltype->angelType;
    $user_source = $user_angeltype->user;

    if ($request->hasPostData('submit')) {
        $user_angeltype->supporter = $supporter;
        $user_angeltype->save();

        $msg = $supporter
            ? __('Added supporter rights for %s to %s.')
            : __('Removed supporter rights for %s from %s.');
        engelsystem_log(sprintf(
            $msg,
            AngelType_name_render($angeltype, true),
            User_Nick_render($user_source, true)
        ));
        success(sprintf($msg, $angeltype->name, $user_source->displayName));

        throw_redirect(url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]));
    }

    return [
        $supporter ? __('Add supporter rights') : __('Remove supporter rights'),
        UserAngelType_update_view($user_angeltype, $user_source, $angeltype, $supporter),
    ];
}

/**
 * User joining an Angeltype (Or supporter doing this for him).
 *
 * @return array
 */
function user_angeltype_add_controller(): array
{
    $angeltype = AngelType::findOrFail(request()->input('angeltype_id'));

    // User is joining by itself
    if (!auth()->user()->isAngelTypeSupporter($angeltype) && !auth()->can('admin_user_angeltypes')) {
        return user_angeltype_join_controller($angeltype);
    }

    // Allow to add any user

    // Default selection
    $user_source = auth()->user();

    // Load all users with userAngelTypes
    /** @var Collection|User[] $users */
    $users = User::with('userAngelTypes')->orderBy('name')->get();

    // Add membership state to displayname
    $users_select = [];
    foreach ($users as $user) {
        $name = $user->displayName;
        /** @var AngelType|null $userAngelType */
        $userAngelType = $user->userAngelTypes->where('id', $angeltype->id)->first();
        if ($userAngelType) {
            $membershipState = __('Member');
            if ($userAngelType->pivot->supporter) {
                $membershipState = __('Supporter');
            } elseif (
                !$userAngelType->pivot->isConfirmed
            ) {
                $membershipState = __('Unconfirmed');
            }
            $name = __('%s (%s)', [$name, $membershipState]);
        }
        $users_select[$user->id] = $name;
    }

    $request = request();
    if ($request->hasPostData('submit')) {
        $user_source = load_user();

        if (!$angeltype->userAngelTypes()->wherePivot('user_id', $user_source->id)->exists()) {
            $userAngelType = new UserAngelType();
            $userAngelType->user()->associate($user_source);
            $userAngelType->angelType()->associate($angeltype);
            $userAngelType->save();

            engelsystem_log(sprintf(
                'User %s added to %s.',
                User_Nick_render($user_source, true),
                AngelType_name_render($angeltype, true)
            ));
            success(sprintf(__('User %s added to %s.'), $user_source->displayName, $angeltype->name));

            if ($request->hasPostData('auto_confirm_user')) {
                $userAngelType->confirmUser()->associate($user_source);
                $userAngelType->save();

                engelsystem_log(sprintf(
                    'User %s confirmed as %s.',
                    User_Nick_render($user_source, true),
                    AngelType_name_render($angeltype, true)
                ));
            }

            user_angeltype_add_email($user_source, $angeltype);

            throw_redirect(url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]));
        }
    }

    return [
        __('Add user to angeltype'),
        UserAngelType_add_view($angeltype, $users_select, $user_source->id),
    ];
}

/**
 * A user joins an angeltype.
 *
 * @param AngelType $angeltype
 * @return array
 */
function user_angeltype_join_controller(AngelType $angeltype)
{
    $user = auth()->user();

    /** @var UserAngelType $user_angeltype */
    $user_angeltype = UserAngelType::whereUserId($user->id)->where('angel_type_id', $angeltype->id)->first();
    if (!empty($user_angeltype)) {
        error(sprintf(__('You are already a %s.'), $angeltype->name));
        throw_redirect(url('/angeltypes'));
    }

    $request = request();
    if ($request->hasPostData('submit')) {
        $userAngelType = new UserAngelType();
        $userAngelType->user()->associate($user);
        $userAngelType->angelType()->associate($angeltype);
        $userAngelType->save();

        engelsystem_log(sprintf(
            'User %s joined %s.',
            User_Nick_render($user, true),
            AngelType_name_render($angeltype, true)
        ));
        success(sprintf(__('You joined %s.'), $angeltype->name));

        if (auth()->can('admin_user_angeltypes') && $request->hasPostData('auto_confirm_user')) {
            $userAngelType->confirmUser()->associate($user);
            $userAngelType->save();

            engelsystem_log(sprintf(
                'User %s confirmed as %s.',
                User_Nick_render($user, true),
                AngelType_name_render($angeltype, true)
            ));
        }

        throw_redirect(url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]));
    }

    return [
        sprintf(__('Become a %s'), htmlspecialchars($angeltype->name)),
        UserAngelType_join_view($user, $angeltype),
    ];
}

/**
 * Route UserAngelType actions.
 *
 * @return array
 */
function user_angeltypes_controller(): array
{
    $request = request();
    if (!$request->has('action')) {
        throw_redirect(url('/angeltypes'));
    }

    return match ($request->input('action')) {
        'delete_all'  => user_angeltypes_delete_all_controller(),
        'confirm_all' => user_angeltypes_confirm_all_controller(),
        'confirm'     => user_angeltype_confirm_controller(),
        'delete'      => user_angeltype_delete_controller(),
        'update'      => user_angeltype_update_controller(),
        'add'         => user_angeltype_add_controller(),
        default       => throw_redirect(url('/angeltyps')),
    };
}
