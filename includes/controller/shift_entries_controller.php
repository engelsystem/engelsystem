<?php

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Shifts\Shift;
use Engelsystem\Models\Shifts\ShiftEntry;
use Engelsystem\Models\Shifts\ShiftSignupStatus;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\Services\MinorRestrictionService;
use Engelsystem\ShiftSignupState;
use Illuminate\Database\Eloquent\Collection;

/**
 * Route shift entry actions.
 *
 * @return array
 */
function shift_entries_controller(): array
{
    $user = auth()->user();
    if (!$user) {
        throw_redirect(url('/login'));
    }

    $action = strip_request_item('action');
    if (empty($action)) {
        throw_redirect(user_link($user->id));
    }

    return match ($action) {
        'create' => shift_entry_create_controller(),
        'delete' => shift_entry_delete_controller(),
        default => ['', ''],
    };
}

/**
 * Sign up for a shift.
 *
 * @return array
 */
function shift_entry_create_controller(): array
{
    $user = auth()->user();
    $request = request();

    if ($user->isFreeloader()) {
        throw_redirect(url('/user_myshifts'));
    }

    $shift = Shift($request->input('shift_id'));
    if (empty($shift)) {
        throw_redirect(user_link($user->id));
    }

    $angeltype = AngelType::find($request->input('angeltype_id'));

    if (auth()->can('user_shifts_admin')) {
        return shift_entry_create_controller_admin($shift, $angeltype);
    }

    if (empty($angeltype)) {
        throw_redirect(user_link($user->id));
    }

    if ($user->isAngelTypeSupporter($angeltype) || auth()->can('admin_user_angeltypes')) {
        return shift_entry_create_controller_supporter($shift, $angeltype);
    }

    return shift_entry_create_controller_user($shift, $angeltype);
}

/**
 * Sign up for a shift.
 * Case: Admin
 *
 * @param Shift          $shift
 * @param AngelType|null $angeltype
 * @return array
 */
function shift_entry_create_controller_admin(Shift $shift, ?AngelType $angeltype): array
{
    $signup_user = auth()->user();
    $request = request();

    if ($request->has('user_id')) {
        $signup_user = User::find($request->input('user_id'));
    }
    if (!$signup_user) {
        throw_redirect(shift_link($shift));
    }

    $angeltypes = AngelType::all();
    if ($request->hasPostData('angeltype_id')) {
        $angeltype = AngelType::find($request->postData('angeltype_id'));
    }
    if (empty($angeltype)) {
        if (count($angeltypes) == 0) {
            throw_redirect(shift_link($shift));
        }
        $angeltype = $angeltypes[0];
    }

    if ($request->hasPostData('submit')) {
        // Log admin override if minor restrictions would have blocked this signup
        if ($signup_user->isMinor()) {
            /** @var MinorRestrictionService $minorService */
            $minorService = app(MinorRestrictionService::class);
            $validation = $minorService->canWorkShift($signup_user, $shift);
            if (!$validation->isValid) {
                $adminUser = auth()->user();
                $errorList = implode(', ', $validation->errors);
                engelsystem_log(sprintf(
                    'Admin override: %s signed up minor %s (ID: %d) for shift %d despite restrictions: %s',
                    $adminUser->name,
                    $signup_user->name,
                    $signup_user->id,
                    $shift->id,
                    $errorList
                ));
            }
        }

        $shiftEntry = new ShiftEntry();
        $shiftEntry->shift()->associate($shift);
        $shiftEntry->angelType()->associate($angeltype);
        $shiftEntry->user()->associate($signup_user);
        $shiftEntry->save();
        ShiftEntry_onCreate($shiftEntry);

        success(sprintf(__('%s has been subscribed to the shift.'), $signup_user->displayName));
        throw_redirect(shift_link($shift));
    }

    /** @var User[]|Collection $users */
    $users = User::with(['userAngelTypes', 'shiftEntries'])->orderBy('name')->get();
    $users_select = [];
    foreach ($users as $user) {
        $name = $user->displayName;
        if ($user->userAngelTypes->where('id', $angeltype->id)->isEmpty()) {
            $name = __('%s (not "%s")', [$name, $angeltype->name]);
        }
        if ($user->shiftEntries->where('shift_id', $shift->id)->isNotEmpty()) {
            $name = __('%s (already in shift)', [$name]);
        }
        $users_select[$user->id] = $name;
    }

    $angeltypes_select = $angeltypes->pluck('name', 'id')->toArray();
    $location = $shift->location;
    return [
        ShiftEntry_create_title(),
        ShiftEntry_create_view_admin($shift, $location, $angeltype, $angeltypes_select, $signup_user, $users_select),
    ];
}

/**
 * Sign up for a shift.
 * Case: Supporter
 *
 * @param Shift     $shift
 * @param AngelType $angeltype
 * @return array
 */
function shift_entry_create_controller_supporter(Shift $shift, AngelType $angeltype): array
{
    $request = request();
    $signup_user = auth()->user();

    if ($request->has('user_id')) {
        $signup_user = User::find($request->input('user_id'));
    }

    if (!$signup_user->userAngelTypes()->wherePivot('angel_type_id', $angeltype->id)->exists()) {
        error(__('User is not in angel type.'));
        throw_redirect(shift_link($shift));
    }

    if ($request->hasPostData('submit')) {
        $shiftEntry = new ShiftEntry();
        $shiftEntry->shift()->associate($shift);
        $shiftEntry->angelType()->associate($angeltype);
        $shiftEntry->user()->associate($signup_user);
        $shiftEntry->save();
        ShiftEntry_onCreate($shiftEntry);

        success(sprintf(__('%s has been subscribed to the shift.'), $signup_user->displayName));
        throw_redirect(shift_link($shift));
    }

    $users = $angeltype->userAngelTypes->sortBy('name');
    $users_select = [];
    foreach ($users as $u) {
        $users_select[$u->id] = $u->displayName;
    }

    $location = $shift->location;
    return [
        ShiftEntry_create_title(),
        ShiftEntry_create_view_supporter($shift, $location, $angeltype, $signup_user, $users_select),
    ];
}

/**
 * Generates an error message for the given shift signup state.
 *
 * @param ShiftSignupState $shift_signup_state
 */
function shift_entry_error_message(ShiftSignupState $shift_signup_state)
{
    match ($shift_signup_state->getState()) {
        ShiftSignupStatus::ANGELTYPE   => error(__('You need be accepted member of the angel type.')),
        ShiftSignupStatus::COLLIDES    => error(__('This shift collides with one of your shifts.')),
        ShiftSignupStatus::OCCUPIED    => error(__('This shift is already occupied.')),
        ShiftSignupStatus::SHIFT_ENDED => error(__('This shift ended already.')),
        ShiftSignupStatus::NOT_ARRIVED => error(__('You are not marked as arrived.')),
        ShiftSignupStatus::NOT_YET     => error(__('You are not allowed to sign up yet.')),
        ShiftSignupStatus::SIGNED_UP   => error(__('You are signed up for this shift.')),
        ShiftSignupStatus::MINOR_RESTRICTED => shift_entry_minor_restriction_errors($shift_signup_state),
        default => null, // ShiftSignupStatus::FREE|ShiftSignupStatus::ADMIN
    };
}

/**
 * Display minor restriction error messages.
 *
 * @param ShiftSignupState $shift_signup_state
 */
function shift_entry_minor_restriction_errors(ShiftSignupState $shift_signup_state)
{
    $errors = $shift_signup_state->getMinorErrors();
    if (empty($errors)) {
        error(__('shift.signup.minor_restricted'));
    } else {
        // Errors are already translated by MinorRestrictionService
        foreach ($errors as $errorMessage) {
            error($errorMessage);
        }
    }
}

/**
 * Sign up for a shift.
 * Case: User
 *
 * @param Shift     $shift
 * @param AngelType $angeltype
 * @return array
 */
function shift_entry_create_controller_user(Shift $shift, AngelType $angeltype): array
{
    $request = request();

    $signup_user = auth()->user();
    $needed_angeltype = (new AngelType())->forceFill(NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype) ?: []);
    $shift_entries = $shift->shiftEntries()
        ->where('angel_type_id', $angeltype->id)
        ->get();
    $shift_signup_state = Shift_signup_allowed(
        $signup_user,
        $shift,
        $angeltype,
        null,
        null,
        $needed_angeltype,
        $shift_entries
    );
    if (!$shift_signup_state->isSignupAllowed()) {
        shift_entry_error_message($shift_signup_state);
        throw_redirect(shift_link($shift));
    }

    $comment = '';
    if ($request->hasPostData('submit')) {
        $comment = strip_request_item_nl('comment');

        $shiftEntry = new ShiftEntry();
        $shiftEntry->shift()->associate($shift);
        $shiftEntry->angelType()->associate($angeltype);
        $shiftEntry->user()->associate($signup_user);
        $shiftEntry->user_comment = $comment;
        $shiftEntry->save();
        ShiftEntry_onCreate($shiftEntry);

        if (
            !$angeltype->restricted
            && !$angeltype->userAngelTypes()->wherePivot('user_id', $signup_user->id)->exists()
        ) {
            $userAngelType = new UserAngelType();
            $userAngelType->user()->associate($signup_user);
            $userAngelType->angelType()->associate($angeltype);
            $userAngelType->save();
        }

        success(__('You are subscribed. Thank you!'));
        throw_redirect(shift_link($shift));
    }

    $location = $shift->location;
    return [
        ShiftEntry_create_title(),
        ShiftEntry_create_view_user($shift, $location, $angeltype, $comment),
    ];
}

/**
 * Link to create a shift entry.
 *
 * @param Shift     $shift
 * @param AngelType $angeltype
 * @param array     $params
 * @return string URL
 */
function shift_entry_create_link(Shift $shift, AngelType $angeltype, $params = [])
{
    $params = array_merge([
        'action'       => 'create',
        'shift_id'     => $shift->id,
        'angeltype_id' => $angeltype->id,
    ], $params);
    return url('/shift-entries', $params);
}

/**
 * Link to create a shift entry as admin.
 *
 * @param Shift $shift
 * @param array $params
 * @return string URL
 */
function shift_entry_create_link_admin(Shift $shift, $params = [])
{
    $params = array_merge([
        'action'   => 'create',
        'shift_id' => $shift->id,
    ], $params);
    return url('/shift-entries', $params);
}

/**
 * Load a shift entry from get parameter shift_entry_id.
 *
 * @return ShiftEntry
 */
function shift_entry_load()
{
    $request = request();

    if (!$request->has('shift_entry_id') || !test_request_int('shift_entry_id')) {
        throw_redirect(url('/user-shifts'));
    }

    return ShiftEntry::findOrFail($request->input('shift_entry_id'));
}

/**
 * Remove somebody from a shift.
 *
 * @return array
 */
function shift_entry_delete_controller()
{
    $user = auth()->user();
    $request = request();
    $shiftEntry = shift_entry_load();

    $shift = Shift($shiftEntry->shift);
    $angeltype = $shiftEntry->angelType;
    $signout_user = $shiftEntry->user;
    if (!Shift_signout_allowed($shift, $angeltype, $signout_user->id)) {
        error(__(
            'You are not allowed to remove this shift entry. If necessary, ask your supporter or heaven to do so.'
        ));
        throw_redirect(user_link($signout_user->id));
    }

    if ($request->hasPostData('delete')) {
        $shiftEntry->delete();
        ShiftEntry_onDelete($shiftEntry);
        success(__('Shift entry removed.'));
        throw_redirect(shift_link($shift));
    }

    if ($user->id == $signout_user->id) {
        return [
            ShiftEntry_delete_title(),
            ShiftEntry_delete_view($shift, $angeltype, $signout_user),
        ];
    }

    return [
        ShiftEntry_delete_title(),
        ShiftEntry_delete_view_admin($shift, $angeltype, $signout_user),
    ];
}

/**
 * Link to delete a shift entry.
 *
 * @param Shift|ShiftEntry $shiftEntry
 * @param array            $params
 * @return string URL
 */
function shift_entry_delete_link($shiftEntry, $params = [])
{
    $params = array_merge([
        'action'         => 'delete',
        'shift_entry_id' => $shiftEntry['shift_entry_id'] ?? $shiftEntry['id'],
    ], $params);
    return url('/shift-entries', $params);
}
