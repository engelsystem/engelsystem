<?php

use Engelsystem\Models\User\User;

/**
 * Display a hint for team/angeltype supporters if there are unconfirmed users for his angeltype.
 *
 * @return string|null
 */
function user_angeltypes_unconfirmed_hint()
{
    $unconfirmed_user_angeltypes = User_unconfirmed_AngelTypes(auth()->user()->id);
    if (count($unconfirmed_user_angeltypes) == 0) {
        return null;
    }

    $unconfirmed_links = [];
    foreach ($unconfirmed_user_angeltypes as $user_angeltype) {
        $unconfirmed_links[] = '<a href="'
            . page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $user_angeltype['angeltype_id']])
            . '">' . $user_angeltype['name']
            . ' (+' . $user_angeltype['count'] . ')'
            . '</a>';
    }

    $count = count($unconfirmed_user_angeltypes);
    return _e(
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
function user_angeltypes_delete_all_controller()
{
    $request = request();

    if (!$request->has('angeltype_id')) {
        error(__('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($request->input('angeltype_id'));
    if (empty($angeltype)) {
        error(__('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if (!User_is_AngelType_supporter(auth()->user(), $angeltype)) {
        error(__('You are not allowed to delete all users for this angeltype.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->hasPostData('deny_all')) {
        UserAngelTypes_delete_all($angeltype['id']);

        engelsystem_log(sprintf('Denied all users for angeltype %s', AngelType_name_render($angeltype)));
        success(sprintf(__('Denied all users for angeltype %s.'), AngelType_name_render($angeltype)));
        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        __('Deny all users'),
        UserAngelTypes_delete_all_view($angeltype)
    ];
}

/**
 * Confirm all unconfirmed users for an angeltype.
 *
 * @return array
 */
function user_angeltypes_confirm_all_controller()
{
    global $privileges;
    $user = auth()->user();
    $request = request();

    if (!$request->has('angeltype_id')) {
        error(__('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($request->input('angeltype_id'));
    if (empty($angeltype)) {
        error(__('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if (!in_array('admin_user_angeltypes', $privileges) && !User_is_AngelType_supporter($user, $angeltype)) {
        error(__('You are not allowed to confirm all users for this angeltype.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->hasPostData('confirm_all')) {
        UserAngelTypes_confirm_all($angeltype['id'], $user->id);

        engelsystem_log(sprintf('Confirmed all users for angeltype %s', AngelType_name_render($angeltype)));
        success(sprintf(__('Confirmed all users for angeltype %s.'), AngelType_name_render($angeltype)));
        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        __('Confirm all users'),
        UserAngelTypes_confirm_all_view($angeltype)
    ];
}

/**
 * Confirm an user for an angeltype.
 *
 * @return array
 */
function user_angeltype_confirm_controller()
{
    $user = auth()->user();
    $request = request();

    if (!$request->has('user_angeltype_id')) {
        error(__('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_angeltype = UserAngelType($request->input('user_angeltype_id'));
    if (empty($user_angeltype)) {
        error(__('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($user_angeltype['angeltype_id']);
    if (empty($angeltype)) {
        error(__('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if (!User_is_AngelType_supporter($user, $angeltype)) {
        error(__('You are not allowed to confirm this users angeltype.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_source = User::find($user_angeltype['user_id']);
    if (!$user_source) {
        error(__('User doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->hasPostData('confirm_user')) {
        UserAngelType_confirm($user_angeltype['id'], $user->id);

        engelsystem_log(sprintf(
            '%s confirmed for angeltype %s',
            User_Nick_render($user_source),
            AngelType_name_render($angeltype)
        ));
        success(sprintf(
            __('%s confirmed for angeltype %s.'),
            User_Nick_render($user_source),
            AngelType_name_render($angeltype)
        ));
        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        __('Confirm angeltype for user'),
        UserAngelType_confirm_view($user_angeltype, $user_source, $angeltype)
    ];
}

/**
 * Remove a user from an Angeltype.
 *
 * @return array
 */
function user_angeltype_delete_controller()
{
    $request = request();
    $user = auth()->user();

    if (!$request->has('user_angeltype_id')) {
        error(__('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_angeltype = UserAngelType($request->input('user_angeltype_id'));
    if (empty($user_angeltype)) {
        error(__('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($user_angeltype['angeltype_id']);
    if (empty($angeltype)) {
        error(__('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_source = User::find($user_angeltype['user_id']);
    if (!$user_source) {
        error(__('User doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($user->id != $user_angeltype['user_id'] && !User_is_AngelType_supporter($user, $angeltype)) {
        error(__('You are not allowed to delete this users angeltype.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->hasPostData('delete')) {
        UserAngelType_delete($user_angeltype);

        $success_message = sprintf(__('User %s removed from %s.'), User_Nick_render($user_source), $angeltype['name']);
        engelsystem_log($success_message);
        success($success_message);

        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        __('Remove angeltype'),
        UserAngelType_delete_view($user_angeltype, $user_source, $angeltype)
    ];
}

/**
 * Update an UserAngelType.
 *
 * @return array
 */
function user_angeltype_update_controller()
{
    global $privileges;
    $supporter = false;
    $request = request();

    if (!in_array('admin_angel_types', $privileges)) {
        error(__('You are not allowed to set supporter rights.'));
        redirect(page_link_to('angeltypes'));
    }

    if (!$request->has('user_angeltype_id')) {
        error(__('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->has('supporter') && preg_match('/^[01]$/', $request->input('supporter'))) {
        $supporter = $request->input('supporter') == '1';
    } else {
        error(__('No supporter update given.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_angeltype = UserAngelType($request->input('user_angeltype_id'));
    if (empty($user_angeltype)) {
        error(__('User angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $angeltype = AngelType($user_angeltype['angeltype_id']);
    if (empty($angeltype)) {
        error(__('Angeltype doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    $user_source = User::find($user_angeltype['user_id']);
    if (!$user_source) {
        error(__('User doesn\'t exist.'));
        redirect(page_link_to('angeltypes'));
    }

    if ($request->hasPostData('submit')) {
        UserAngelType_update($user_angeltype['id'], $supporter);

        $success_message = sprintf(
            $supporter
                ? __('Added supporter rights for %s to %s.')
                : __('Removed supporter rights for %s from %s.'),
            AngelType_name_render($angeltype),
            User_Nick_render($user_source)
        );
        engelsystem_log($success_message);
        success($success_message);

        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        $supporter ? __('Add supporter rights') : __('Remove supporter rights'),
        UserAngelType_update_view($user_angeltype, $user_source, $angeltype, $supporter)
    ];
}

/**
 * User joining an Angeltype (Or supporter doing this for him).
 *
 * @return array
 */
function user_angeltype_add_controller()
{
    $angeltype = load_angeltype();

    // User is joining by itself
    if (!User_is_AngelType_supporter(auth()->user(), $angeltype)) {
        return user_angeltype_join_controller($angeltype);
    }

    // Allow to add any user

    // Default selection
    $user_source = auth()->user();

    // Load possible users, that are not in the angeltype already
    $users_source = Users_by_angeltype_inverted($angeltype);

    if (request()->hasPostData('submit')) {
        $user_source = load_user();

        if (!UserAngelType_exists($user_source->id, $angeltype)) {
            $user_angeltype_id = UserAngelType_create($user_source->id, $angeltype);

            engelsystem_log(sprintf(
                'User %s added to %s.',
                User_Nick_render($user_source),
                AngelType_name_render($angeltype)
            ));
            success(sprintf(
                __('User %s added to %s.'),
                User_Nick_render($user_source),
                AngelType_name_render($angeltype)
            ));

            UserAngelType_confirm($user_angeltype_id, $user_source->id);
            engelsystem_log(sprintf(
                'User %s confirmed as %s.',
                User_Nick_render($user_source),
                AngelType_name_render($angeltype)
            ));

            redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
        }
    }

    return [
        __('Add user to angeltype'),
        UserAngelType_add_view($angeltype, $users_source, $user_source->id)
    ];
}

/**
 * A user joins an angeltype.
 *
 * @param array $angeltype
 * @return array
 */
function user_angeltype_join_controller($angeltype)
{
    global $privileges;
    $user = auth()->user();

    $user_angeltype = UserAngelType_by_User_and_AngelType($user->id, $angeltype);
    if (!empty($user_angeltype)) {
        error(sprintf(__('You are already a %s.'), $angeltype['name']));
        redirect(page_link_to('angeltypes'));
    }

    if (request()->hasPostData('submit')) {
        $user_angeltype_id = UserAngelType_create($user->id, $angeltype);

        $success_message = sprintf(__('You joined %s.'), $angeltype['name']);
        engelsystem_log(sprintf(
            'User %s joined %s.',
            User_Nick_render($user),
            AngelType_name_render($angeltype)
        ));
        success($success_message);

        if (in_array('admin_user_angeltypes', $privileges)) {
            UserAngelType_confirm($user_angeltype_id, $user->id);
            engelsystem_log(sprintf(
                'User %s confirmed as %s.',
                User_Nick_render($user),
                AngelType_name_render($angeltype)
            ));
        }

        redirect(page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]));
    }

    return [
        sprintf(__('Become a %s'), $angeltype['name']),
        UserAngelType_join_view($user, $angeltype)
    ];
}

/**
 * Route UserAngelType actions.
 *
 * @return array
 */
function user_angeltypes_controller()
{
    $request = request();
    if (!$request->has('action')) {
        redirect(page_link_to('angeltypes'));
    }

    switch ($request->input('action')) {
        case 'delete_all':
            return user_angeltypes_delete_all_controller();
        case 'confirm_all':
            return user_angeltypes_confirm_all_controller();
        case 'confirm':
            return user_angeltype_confirm_controller();
        case 'delete':
            return user_angeltype_delete_controller();
        case 'update':
            return user_angeltype_update_controller();
        case 'add':
            return user_angeltype_add_controller();
        default:
            redirect(page_link_to('angeltypes'));
    }
}
