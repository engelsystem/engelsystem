<?php

use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;

/**
 * @param UserAngelType $user_angeltype
 * @param User          $user
 * @param AngelType     $angeltype
 * @param bool          $supporter
 * @return string
 */
function UserAngelType_update_view(UserAngelType $user_angeltype, User $user, AngelType $angeltype, bool $supporter)
{
    return page_with_title($supporter ? __('Add supporter rights') : __('Remove supporter rights'), [
        msg(),
        info(sprintf(
            $supporter
                ? __('Do you really want to add supporter rights for %s to %s?')
                : __('Do you really want to remove supporter rights for %s from %s?'),
            $angeltype->name,
            $user->displayName
        ), true),
        form([
            buttons([
                button(
                    url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]),
                    icon('x-lg') . __('form.cancel')
                ),
                form_submit('submit', icon('check-lg') . __('Yes'), 'btn-primary', false),
            ]),
        ], url('/user-angeltypes', [
            'action'            => 'update',
            'user_angeltype_id' => $user_angeltype->id,
            'supporter'         => ($supporter ? '1' : '0'),
        ])),
    ]);
}

/**
 * @param AngelType $angeltype
 * @return string
 */
function UserAngelTypes_delete_all_view(AngelType $angeltype)
{
    return page_with_title(__('Deny all users'), [
        msg(),
        info(sprintf(__('Do you really want to deny all users for %s?'), $angeltype->name), true),
        form([
            buttons([
                button(
                    url(
                        '/angeltypes',
                        ['action' => 'view', 'angeltype_id' => $angeltype->id]
                    ),
                    icon('x-lg') . __('form.cancel')
                ),
                form_submit('deny_all', icon('check-lg') . __('Yes'), 'btn-primary', false),
            ]),
        ], url('/user-angeltypes', ['action' => 'delete_all', 'angeltype_id' => $angeltype->id])),
    ]);
}

/**
 * @param AngelType $angeltype
 * @return string
 */
function UserAngelTypes_confirm_all_view(AngelType $angeltype)
{
    return page_with_title(__('Confirm all users'), [
        msg(),
        info(sprintf(__('Do you really want to confirm all users for %s?'), $angeltype->name), true),
        form([
            buttons([
                button(angeltype_link($angeltype->id), icon('x-lg') . __('form.cancel')),
                form_submit('confirm_all', icon('check-lg') . __('Yes'), 'btn-primary', false),
            ]),
        ], url('/user-angeltypes', ['action' => 'confirm_all', 'angeltype_id' => $angeltype->id])),
    ]);
}

/**
 * @param UserAngelType $user_angeltype
 * @param User          $user
 * @param AngelType     $angeltype
 * @return string
 */
function UserAngelType_confirm_view(UserAngelType $user_angeltype, User $user, AngelType $angeltype)
{
    return page_with_title(__('Confirm angeltype for user'), [
        msg(),
        info(sprintf(
            __('Do you really want to confirm %s for %s?'),
            $user->displayName,
            $angeltype->name
        ), true),
        form([
            buttons([
                button(angeltype_link($angeltype->id), icon('x-lg') . __('form.cancel')),
                form_submit('confirm_user', icon('check-lg') . __('Yes'), 'btn-primary', false),
            ]),
        ], url('/user-angeltypes', ['action' => 'confirm', 'user_angeltype_id' => $user_angeltype->id])),
    ]);
}

/**
 * @param UserAngelType $user_angeltype
 * @param User          $user
 * @param AngelType     $angeltype
 * @param bool          $isOwnAngelType
 * @return string
 */
function UserAngelType_delete_view(UserAngelType $user_angeltype, User $user, AngelType $angeltype, bool $isOwnAngelType)
{
    return page_with_title(__('Leave angeltype'), [
        msg(),
        info(sprintf(
            $isOwnAngelType ? __('Do you really want to leave "%2$s"?') : __('Do you really want to remove "%s" from "%s"?'),
            $user->displayName,
            $angeltype->name
        ), true),
        form([
            buttons([
                button(angeltype_link($angeltype->id), icon('x-lg') . __('form.cancel')),
                form_submit('delete', icon('check-lg') . __('Yes'), 'btn-primary', false),
            ]),
        ], url('/user-angeltypes', ['action' => 'delete', 'user_angeltype_id' => $user_angeltype->id])),
    ], true);
}

/**
 * @param AngelType $angeltype
 * @param array     $users_select
 * @param int       $user_id
 * @return string
 */
function UserAngelType_add_view(AngelType $angeltype, $users_select, $user_id)
{
    $link = button(
        url('/angeltypes', ['action' => 'view', 'angeltype_id' => $angeltype->id]),
        icon('chevron-left'),
        'btn-sm',
        '',
        __('general.back')
    );
    return page_with_title($link . ' ' . __('Add user to angeltype'), [
        msg(),
        form([
            form_info(__('Angeltype'), htmlspecialchars($angeltype->name)),
            $angeltype->restricted
                ? form_checkbox('auto_confirm_user', __('Confirm user'), true)
                : '',
            form_select('user_id', __('general.user'), $users_select, $user_id),
            form_submit('submit', icon('plus-lg') . __('general.add')),
        ]),
    ]);
}

/**
 * @param User      $user
 * @param AngelType $angeltype
 * @return string
 */
function UserAngelType_join_view($user, AngelType $angeltype)
{
    $isOther = $user->id != auth()->user()->id;
    return page_with_title(sprintf(__('Become a %s'), htmlspecialchars($angeltype->name)), [
        msg(),
        info(sprintf(
            $isOther ? __('Do you really want to add %s to %s?') : __('Do you want to become a %2$s?'),
            $user->displayName,
            $angeltype->name
        ), true),
        form([
            auth()->can('admin_user_angeltypes') ? form_checkbox('auto_confirm_user', __('Confirm user'), true) : '',
            buttons([
                button(angeltype_link($angeltype->id), icon('x-lg') . __('form.cancel')),
                form_submit('submit', icon('save') . __('form.save'), 'btn-primary', false),
            ]),
        ], url(
            '/user-angeltypes',
            ['action' => 'add', 'angeltype_id' => $angeltype->id, 'user_id' => $user->id]
        )),
    ]);
}
