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
        buttons([
            button(
                page_link_to('angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype['id']]),
                glyph('remove') . __('cancel')
            ),
            button(
                page_link_to('user_angeltypes', [
                    'action'            => 'update',
                    'user_angeltype_id' => $user_angeltype['id'],
                    'supporter'         => ($supporter ? '1' : '0'),
                    'confirmed'         => 1,
                ]),
                glyph('ok') . __('yes'),
                'btn-primary'
            )
        ])
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
        buttons([
            button(
                page_link_to(
                    'angeltypes',
                    ['action' => 'view', 'angeltype_id' => $angeltype['id']]
                ),
                glyph('remove') . __('cancel')
            ),
            button(
                page_link_to(
                    'user_angeltypes',
                    ['action' => 'delete_all', 'angeltype_id' => $angeltype['id'], 'confirmed' => 1]
                ),
                glyph('ok') . __('yes'),
                'btn-primary'
            )
        ])
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
        buttons([
            button(angeltype_link($angeltype['id']), glyph('remove') . __('cancel')),
            button(
                page_link_to('user_angeltypes',
                    ['action' => 'confirm_all', 'angeltype_id' => $angeltype['id'], 'confirmed' => 1]),
                glyph('ok') . __('yes'),
                'btn-primary'
            )
        ])
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
        buttons([
            button(angeltype_link($angeltype['id']), glyph('remove') . __('cancel')),
            button(
                page_link_to(
                    'user_angeltypes',
                    ['action' => 'confirm', 'user_angeltype_id' => $user_angeltype['id'], 'confirmed' => 1]
                ),
                glyph('ok') . __('yes'),
                'btn-primary'
            )
        ])
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
        buttons([
            button(angeltype_link($angeltype['id']), glyph('remove') . __('cancel')),
            button(
                page_link_to('user_angeltypes',
                    ['action' => 'delete', 'user_angeltype_id' => $user_angeltype['id'], 'confirmed' => 1]),
                glyph('ok') . __('yes'),
                'btn-primary'
            )
        ])
    ]);
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
        buttons([
            button(angeltype_link($angeltype['id']), glyph('remove') . __('cancel')),
            button(
                page_link_to(
                    'user_angeltypes',
                    ['action' => 'add', 'angeltype_id' => $angeltype['id'], 'user_id' => $user->id, 'confirmed' => 1]
                ),
                glyph('ok') . __('save'),
                'btn-primary'
            )
        ])
    ]);
}
