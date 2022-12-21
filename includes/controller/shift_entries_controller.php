<?php

use Engelsystem\Models\AngelType;
use Engelsystem\Models\Room;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
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
        throw_redirect(page_link_to('login'));
    }

    $action = strip_request_item('action');
    if (empty($action)) {
        throw_redirect(user_link($user->id));
    }

    return match ($action) {
        'create' => shift_entry_create_controller(),
        'delete' => shift_entry_delete_controller(),
        default  => ['', ''],
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

    if (User_is_freeloader($user)) {
        throw_redirect(page_link_to('user_myshifts'));
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
 * @param array          $shift
 * @param AngelType|null $angeltype
 * @return array
 */
function shift_entry_create_controller_admin($shift, ?AngelType $angeltype): array
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
        ShiftEntry_create([
            'SID'              => $shift['SID'],
            'TID'              => $angeltype->id,
            'UID'              => $signup_user->id,
            'Comment'          => '',
            'freeloaded'       => false,
            'freeload_comment' => ''
        ]);

        success(sprintf(__('%s has been subscribed to the shift.'), User_Nick_render($signup_user)));
        throw_redirect(shift_link($shift));
    }

    /** @var User[]|Collection $users */
    $users = User::query()->orderBy('name')->get();
    $users_select = [];
    foreach ($users as $user) {
        $users_select[$user->id] = $user->name;
    }

    $angeltypes_select = $angeltypes->pluck('name', 'id')->toArray();
    $room = Room::find($shift['RID']);
    return [
        ShiftEntry_create_title(),
        ShiftEntry_create_view_admin($shift, $room, $angeltype, $angeltypes_select, $signup_user, $users_select)
    ];
}

/**
 * Sign up for a shift.
 * Case: Supporter
 *
 * @param array     $shift
 * @param AngelType $angeltype
 * @return array
 */
function shift_entry_create_controller_supporter($shift, AngelType $angeltype): array
{
    $request = request();
    $signup_user = auth()->user();

    if ($request->has('user_id')) {
        $signup_user = User::find($request->input('user_id'));
    }

    if (!$signup_user->userAngelTypes()->wherePivot('angel_type_id', $angeltype->id)->exists()) {
        error(__('User is not in angeltype.'));
        throw_redirect(shift_link($shift));
    }

    if ($request->hasPostData('submit')) {
        ShiftEntry_create([
            'SID'              => $shift['SID'],
            'TID'              => $angeltype->id,
            'UID'              => $signup_user->id,
            'Comment'          => '',
            'freeloaded'       => false,
            'freeload_comment' => ''
        ]);

        success(sprintf(__('%s has been subscribed to the shift.'), User_Nick_render($signup_user)));
        throw_redirect(shift_link($shift));
    }

    $users = $angeltype->userAngelTypes->sortBy('name');
    $users_select = [];
    foreach ($users as $u) {
        $users_select[$u->id] = $u->name;
    }

    $room = Room::find($shift['RID']);
    return [
        ShiftEntry_create_title(),
        ShiftEntry_create_view_supporter($shift, $room, $angeltype, $signup_user, $users_select)
    ];
}

/**
 * Generates an error message for the given shift signup state.
 *
 * @param ShiftSignupState $shift_signup_state
 */
function shift_entry_error_message(ShiftSignupState $shift_signup_state)
{
    if ($shift_signup_state->getState() == ShiftSignupState::ANGELTYPE) {
        error(__('You need be accepted member of the angeltype.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::COLLIDES) {
        error(__('This shift collides with one of your shifts.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::OCCUPIED) {
        error(__('This shift is already occupied.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::SHIFT_ENDED) {
        error(__('This shift ended already.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::NOT_ARRIVED) {
        error(__('You are not marked as arrived.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::NOT_YET) {
        error(__('You are not allowed to sign up yet.'));
    } elseif ($shift_signup_state->getState() == ShiftSignupState::SIGNED_UP) {
        error(__('You are signed up for this shift.'));
    }
}

/**
 * Sign up for a shift.
 * Case: User
 *
 * @param array     $shift
 * @param AngelType $angeltype
 * @return array
 */
function shift_entry_create_controller_user($shift, AngelType $angeltype): array
{
    $request = request();

    $signup_user = auth()->user();
    $needed_angeltype = (new AngelType())->forceFill(NeededAngeltype_by_Shift_and_Angeltype($shift, $angeltype));
    $shift_entries = ShiftEntries_by_shift_and_angeltype($shift['SID'], $angeltype->id);
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
        ShiftEntry_create([
            'SID'              => $shift['SID'],
            'TID'              => $angeltype->id,
            'UID'              => $signup_user->id,
            'Comment'          => $comment,
            'freeloaded'       => false,
            'freeload_comment' => ''
        ]);

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

    $room = Room::find($shift['RID']);
    return [
        ShiftEntry_create_title(),
        ShiftEntry_create_view_user($shift, $room, $angeltype, $comment)
    ];
}

/**
 * Link to create a shift entry.
 *
 * @param array     $shift
 * @param AngelType $angeltype
 * @param array     $params
 * @return string URL
 */
function shift_entry_create_link($shift, AngelType $angeltype, $params = [])
{
    $params = array_merge([
        'action'       => 'create',
        'shift_id'     => $shift['SID'],
        'angeltype_id' => $angeltype->id
    ], $params);
    return page_link_to('shift_entries', $params);
}

/**
 * Link to create a shift entry as admin.
 *
 * @param array $shift
 * @param array $params
 * @return string URL
 */
function shift_entry_create_link_admin($shift, $params = [])
{
    $params = array_merge([
        'action'   => 'create',
        'shift_id' => $shift['SID']
    ], $params);
    return page_link_to('shift_entries', $params);
}

/**
 * Load a shift entry from get parameter shift_entry_id.
 *
 * @return array
 */
function shift_entry_load()
{
    $request = request();

    if (!$request->has('shift_entry_id') || !test_request_int('shift_entry_id')) {
        throw_redirect(page_link_to('user_shifts'));
    }
    $shiftEntry = ShiftEntry($request->input('shift_entry_id'));
    if (empty($shiftEntry)) {
        error(__('Shift entry not found.'));
        throw_redirect(page_link_to('user_shifts'));
    }

    return $shiftEntry;
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

    $shift = Shift($shiftEntry['SID']);
    $angeltype = AngelType::find($shiftEntry['TID']);
    $signout_user = User::find($shiftEntry['UID']);
    if (!Shift_signout_allowed($shift, $angeltype, $signout_user->id)) {
        error(__(
            'You are not allowed to remove this shift entry. If necessary, ask your supporter or heaven to do so.'
        ));
        throw_redirect(user_link($signout_user->id));
    }

    if ($request->hasPostData('delete')) {
        ShiftEntry_delete($shiftEntry);
        success(__('Shift entry removed.'));
        throw_redirect(shift_link($shift));
    }

    if ($user->id == $signout_user->id) {
        return [
            ShiftEntry_delete_title(),
            ShiftEntry_delete_view($shift, $angeltype, $signout_user->id)
        ];
    }

    return [
        ShiftEntry_delete_title(),
        ShiftEntry_delete_view_admin($shift, $angeltype, $signout_user)
    ];
}

/**
 * Link to delete a shift entry.
 *
 * @param array $shiftEntry
 * @param array $params
 * @return string URL
 */
function shift_entry_delete_link($shiftEntry, $params = [])
{
    $params = array_merge([
        'action'         => 'delete',
        'shift_entry_id' => $shiftEntry['id']
    ], $params);
    return page_link_to('shift_entries', $params);
}
