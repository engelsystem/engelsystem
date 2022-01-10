<?php

use Engelsystem\Models\User\User;

/**
 * @param array $user_angeltype
 * @param User  $user
 * @param array $angeltype
 * @param bool  $supporter
 * @return string
 */
function UserAngelType_update_view($user_angeltype, $user, $angeltype, $supporter)
{
    return page_with_title($supporter ? __('Add supporter rights') : __('Remove supporter rights'), [
        msg(),
        info(sprintf(
            $supporter
                ? __('Do you really want to add supporter rights for %s to %s?')
                : __('Do you really want to remove supporter rights for %s from %s?'),
            $angeltype['name'],
            User_Nick_render($user)
        ), true),
        form([
            buttons([
                button(
                    page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]),
                    icon('x-lg') . __('cancel')
                ),
                form_submit('submit', icon('check-lg') . __('yes'), 'btn-primary', false),
            ]),
        ], page_link_to('user_angeltypes', [
            'action'            => 'update',
            'user_angeltype_id' => $user_angeltype['id'],
            'supporter'         => ($supporter ? '1' : '0'),
        ])),
    ]);
}

/**
 * @param array $angeltype
 * @return string
 */
function UserAngelTypes_delete_all_view($angeltype)
{
    return page_with_title(__('Deny all users'), [
        msg(),
        info(sprintf(__('Do you really want to deny all users for %s?'), $angeltype['name']), true),
        form([
            buttons([
                button(
                    page_link_to(
                        'angeltypes',
                        ['action' => 'view', 'angeltype_id' => $angeltype['id']]
                    ),
                    icon('x-lg') . __('cancel')
                ),
                form_submit('deny_all', icon('check-lg') . __('yes'), 'btn-primary', false)
            ]),
        ], page_link_to('user_angeltypes', ['action' => 'delete_all', 'angeltype_id' => $angeltype['id']])),
    ]);
}

/**
 * @param array $angeltype
 * @return string
 */
function UserAngelTypes_confirm_all_view($angeltype)
{
    return page_with_title(__('Confirm all users'), [
        msg(),
        info(sprintf(__('Do you really want to confirm all users for %s?'), $angeltype['name']), true),
        form([
            buttons([
                button(angeltype_link($angeltype['id']), icon('x-lg') . __('cancel')),
                form_submit('confirm_all', icon('check-lg') . __('yes'), 'btn-primary', false),
            ]),
        ], page_link_to('user_angeltypes', ['action' => 'confirm_all', 'angeltype_id' => $angeltype['id']])),
    ]);
}

/**
 * @param array $user_angeltype
 * @param User  $user
 * @param array $angeltype
 * @return string
 */
function UserAngelType_confirm_view($user_angeltype, $user, $angeltype)
{
    return page_with_title(__('Confirm angeltype for user'), [
        msg(),
        info(sprintf(
            __('Do you really want to confirm %s for %s?'),
            User_Nick_render($user),
            $angeltype['name']
        ), true),
        form([
            buttons([
                button(angeltype_link($angeltype['id']), icon('x-lg') . __('cancel')),
                form_submit('confirm_user', icon('check-lg') . __('yes'), 'btn-primary', false),
            ]),
        ], page_link_to('user_angeltypes', ['action' => 'confirm', 'user_angeltype_id' => $user_angeltype['id']])),
    ]);
}

/**
 * @param array $user_angeltype
 * @param User  $user
 * @param array $angeltype
 * @return string
 */
function UserAngelType_delete_view($user_angeltype, $user, $angeltype)
{
    return page_with_title(__('Remove angeltype'), [
        msg(),
        info(sprintf(
            __('Do you really want to delete %s from %s?'),
            User_Nick_render($user),
            $angeltype['name']
        ), true),
        form([
            buttons([
                button(angeltype_link($angeltype['id']), icon('x-lg') . __('cancel')),
                form_submit('delete', icon('check-lg') . __('yes'), 'btn-primary', false),
            ]),
        ], page_link_to('user_angeltypes', ['action' => 'delete', 'user_angeltype_id' => $user_angeltype['id']])),
    ], true);
}

/**
 * @param array  $angeltype
 * @param User[] $users_source
 * @param int    $user_id
 * @return string
 */
function UserAngelType_add_view($angeltype, $users_source, $user_id)
{
    $users = [];
    foreach ($users_source as $user_source) {
        $users[$user_source->id] = User_Nick_render($user_source);
    }

    return page_with_title(__('Add user to angeltype'), [
        msg(),
        buttons([
            button(
                page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]),
                __('back'),
                'back'
            )
        ]),
        form([
            form_info(__('Angeltype'), $angeltype['name']),
            form_checkbox('auto_confirm_user', __('Confirm user'), true),
            form_select('user_id', __('User'), $users, $user_id),
            form_submit('submit', __('Add'))
        ])
    ]);
}

/**
 * @param User  $user
 * @param array $angeltype
 * @return string
 */
function UserAngelType_join_view($user, $angeltype)
{
    return page_with_title(sprintf(__('Become a %s'), $angeltype['name']), [
        msg(),
        info(sprintf(
            __('Do you really want to add %s to %s?'),
            User_Nick_render($user),
            $angeltype['name']
        ), true),
        form([
            auth()->can('admin_user_angeltypes') ? form_checkbox('auto_confirm_user', __('Confirm user'), true) : '',
            buttons([
                button(angeltype_link($angeltype['id']), icon('x-lg') . __('cancel')),
                form_submit('submit', icon('check-lg') . __('save'), 'btn-primary', false)
            ]),
        ], page_link_to(
            'user_angeltypes',
            ['action' => 'add', 'angeltype_id' => $angeltype['id'], 'user_id' => $user->id]
        )),
    ]);
}
