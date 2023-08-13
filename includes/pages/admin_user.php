<?php

use Engelsystem\Config\GoodieType;
use Engelsystem\Http\Validation\Rules\Username;
use Engelsystem\Models\Group;
use Engelsystem\Models\User\User;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * @return string
 */
function admin_user_title()
{
    return __('All Angels');
}

/**
 * @return string
 */
function admin_user()
{
    $user = auth()->user();
    $tshirt_sizes = config('tshirt_sizes');
    $request = request();
    $html = '';
    $goodie = GoodieType::from(config('goodie_type'));
    $goodie_enabled = $goodie !== GoodieType::None;
    $goodie_tshirt = $goodie === GoodieType::Tshirt;

    if (!$request->has('id')) {
        throw_redirect(users_link());
    }

    $user_id = $request->input('id');
    if (!$request->has('action')) {
        $user_source = User::find($user_id);
        if (!$user_source) {
            error(__('This user does not exist.'));
            throw_redirect(users_link());
        }

        $html .= __('Here you can change the user entry. Under the item \'Arrived\' the angel is marked as present, a yes at Active means that the angel was active.');
        if ($goodie_enabled) {
            if ($goodie_tshirt) {
                $html .= ' ' . __('If the angel is active, it can claim a T-shirt. If T-shirt is set to \'Yes\', the angel already got their T-shirt.');
            } else {
                $html .= ' ' . __('If the angel is active, it can claim a goodie. If goodie is set to \'Yes\', the angel already got their goodie.');
            }
        }
        $html .= '<br /><br />';
        $html .= '<form action="'
            . page_link_to('admin_user', ['action' => 'save', 'id' => $user_id])
            . '" method="post">' . "\n";
        $html .= form_csrf();
        $html .= '<table>' . "\n";
        $html .= '<input type="hidden" name="Type" value="Normal">' . "\n";
        $html .= '<tr><td>' . "\n";
        $html .= '<table>' . "\n";
        $html .= '  <tr><td>' . __('Nickname') . '</td><td>' . '<input size="40" name="eNick" value="' . $user_source->name . '" class="form-control" maxlength="24"></td></tr>' . "\n";
        $html .= '  <tr><td>' . __('Last login') . '</td><td><p class="help-block">'
            . ($user_source->last_login_at ? $user_source->last_login_at->format(__('Y-m-d H:i')) : '-')
            . '</p></td></tr>' . "\n";
        if (config('enable_user_name')) {
            $html .= '  <tr><td>' . __('Prename') . '</td><td>' . '<input size="40" name="eName" value="' . $user_source->personalData->last_name . '" class="form-control" maxlength="64"></td></tr>' . "\n";
            $html .= '  <tr><td>' . __('Last name') . '</td><td>' . '<input size="40" name="eVorname" value="' . $user_source->personalData->first_name . '" class="form-control" maxlength="64"></td></tr>' . "\n";
        }
        $html .= '  <tr><td>' . __('Mobile') . '</td><td>' . '<input type= "tel" size="40" name="eHandy" value="' . $user_source->contact->mobile . '" class="form-control" maxlength="40"></td></tr>' . "\n";
        if (config('enable_dect')) {
            $html .= '  <tr><td>' . __('DECT') . '</td><td>' . '<input size="40" name="eDECT" value="' . $user_source->contact->dect . '" class="form-control" maxlength="40"></td></tr>' . "\n";
        }
        if ($user_source->settings->email_human) {
            $html .= '  <tr><td>' . __('settings.profile.email') . '</td><td>' . '<input type="email" size="40" name="eemail" value="' . $user_source->email . '" class="form-control" maxlength="254"></td></tr>' . "\n";
        }
        if ($goodie_tshirt) {
            $html .= '  <tr><td>' . __('user.shirt_size') . '</td><td>'
                . html_select_key(
                    'size',
                    'eSize',
                    $tshirt_sizes,
                    $user_source->personalData->shirt_size,
                    __('form.select_placeholder')
                )
                . '</td></tr>' . "\n";
        }

        $options = [
            '1' => __('Yes'),
            '0' => __('No'),
        ];

        // Gekommen?
        $html .= '  <tr><td>' . __('Arrived') . '</td><td>' . "\n";
        if ($user_source->state->arrived) {
            $html .= __('Yes');
        } else {
            $html .= __('No');
        }
        $html .= '</td></tr>' . "\n";

        // Aktiv?
        $html .= '  <tr><td>' . __('user.active') . '</td><td>' . "\n";
        $html .= html_options('eAktiv', $options, $user_source->state->active) . '</td></tr>' . "\n";

        // Aktiv erzwingen
        if (auth()->can('admin_active')) {
            $html .= '  <tr><td>' . __('Force active') . '</td><td>' . "\n";
            $html .= html_options('force_active', $options, $user_source->state->force_active) . '</td></tr>' . "\n";
        }

        if ($goodie_enabled) {
            // T-Shirt bekommen?
            if ($goodie_tshirt) {
                $html .= '  <tr><td>' . __('T-Shirt') . '</td><td>' . "\n";
            } else {
                $html .= '  <tr><td>' . __('Goodie') . '</td><td>' . "\n";
            }
            $html .= html_options('eTshirt', $options, $user_source->state->got_shirt) . '</td></tr>' . "\n";
        }
        $html .= '</table>' . "\n" . '</td><td></td></tr>';

        $html .= '</td></tr>' . "\n";
        $html .= '</table>' . "\n" . '<br />' . "\n";
        $html .= '<button type="submit" class="btn btn-primary">' . __('form.save') . '</button>' . "\n";
        $html .= '</form>';

        $html .= '<hr />';

        $html .= form_info('', __('Please visit the angeltypes page or the users profile to manage the users angeltypes.'));

        $html .= ' ' . __('Here you can reset the password of this angel:') . '<form action="'
            . page_link_to('admin_user', ['action' => 'change_pw', 'id' => $user_id])
            . '" method="post">' . "\n";
        $html .= form_csrf();
        $html .= '<table>' . "\n";
        $html .= '  <tr><td>' . __('Password') . '</td><td>' . '<input type="password" size="40" name="new_pw" value="" class="form-control" autocomplete="new-password"></td></tr>' . "\n";
        $html .= '  <tr><td>' . __('Confirm password') . '</td><td>' . '<input type="password" size="40" name="new_pw2" value="" class="form-control" autocomplete="new-password"></td></tr>' . "\n";

        $html .= '</table>' . "\n" . '<br />' . "\n";
        $html .= '<button type="submit" class="btn btn-primary">' . __('form.save') . '</button>' . "\n";
        $html .= '</form>';

        $html .= '<hr />';

        /** @var Group $my_highest_group */
        $my_highest_group = $user->groups()->orderByDesc('id')->first();
        if (!empty($my_highest_group)) {
            $my_highest_group = $my_highest_group->id;
        }

        $angel_highest_group = $user_source->groups()->orderByDesc('id')->first();
        if (!empty($angel_highest_group)) {
            $angel_highest_group = $angel_highest_group->id;
        }

        if (
            ($user_id != $user->id || auth()->can('admin_groups'))
            && ($my_highest_group >= $angel_highest_group || is_null($angel_highest_group))
        ) {
            $html .= __('Here you can define the user groups of the angel:') . '<form action="'
                . page_link_to('admin_user', ['action' => 'save_groups', 'id' => $user_id])
                . '" method="post">' . "\n";
            $html .= form_csrf();
            $html .= '<div>';

            $groups = changeableGroups($my_highest_group, $user_id);
            foreach ($groups as $group) {
                $html .= '<div class="form-check">'
                    . '<input class="form-check-input" type="checkbox" id="' . $group->id . '" name="groups[]" value="' . $group->id . '" '
                    . ($group->selected ? ' checked="checked"' : '')
                    . ' /><label class="form-check-label" for="' . $group->id . '">' . $group->name . '</label></div>';
            }

            $html .= '</div><br>';

            $html .= '<button type="submit" class="btn btn-primary">' . __('form.save') . '</button>' . "\n";
            $html .= '</form>';

            $html .= '<hr />';
        }

        $html .= buttons([
            button(user_delete_link($user_source->id), icon('trash') . __('delete'), 'btn-danger'),
        ]);

        $html .= '<hr>';
    } else {
        switch ($request->input('action')) {
            case 'save_groups':
                $angel = User::findOrFail($user_id);
                if ($angel->id != $user->id || auth()->can('admin_groups')) {
                    /** @var Group $my_highest_group */
                    $my_highest_group = $user->groups()->orderByDesc('id')->first();
                    /** @var Group $angel_highest_group */
                    $angel_highest_group = $angel->groups()->orderByDesc('id')->first();

                    if (
                        $my_highest_group
                        && (
                            empty($angel_highest_group)
                            || ($my_highest_group->id >= $angel_highest_group->id)
                        )
                    ) {
                        $groups_source = changeableGroups($my_highest_group->id, $angel->id);
                        $groups = [];
                        $groupList = [];
                        foreach ($groups_source as $group) {
                            $groups[$group->id] = $group;
                            $groupList[] = $group->id;
                        }

                        $groupsRequest = $request->input('groups');
                        if (!is_array($groupsRequest)) {
                            $groupsRequest = [];
                        }

                        $angel->groups()->detach();
                        $user_groups_info = [];
                        foreach ($groupsRequest as $group) {
                            if (in_array($group, $groupList)) {
                                $group = $groups[$group];
                                $angel->groups()->attach($group);
                                $user_groups_info[] = $group->name;
                            }
                        }
                        engelsystem_log(
                            'Set groups of ' . User_Nick_render($angel, true) . ' to: '
                            . join(', ', $user_groups_info)
                        );
                        $html .= success(__('User groups saved.'), true);
                    } else {
                        $html .= error(__('You cannot edit angels with more rights.'), true);
                    }
                } else {
                    $html .= error(__('You cannot edit your own rights.'), true);
                }
                break;

            case 'save':
                $force_active = $user->state->force_active;
                $user_source = User::find($user_id);
                if (auth()->can('admin_active')) {
                    $force_active = $request->input('force_active');
                }
                if ($user_source->settings->email_human) {
                    $user_source->email = $request->postData('eemail');
                }

                $nick = trim($request->get('eNick'));
                $nickValid = (new Username())->validate($nick);

                if ($nickValid) {
                    $user_source->name = $nick;
                }
                $user_source->save();

                if (config('enable_user_name')) {
                    $user_source->personalData->first_name = $request->postData('eVorname');
                    $user_source->personalData->last_name = $request->postData('eName');
                }
                if ($goodie_tshirt) {
                    $user_source->personalData->shirt_size = $request->postData('eSize');
                }
                $user_source->personalData->save();

                $user_source->contact->mobile = $request->postData('eHandy');
                $user_source->contact->dect = $request->postData('eDECT');
                $user_source->contact->save();

                if ($goodie_enabled) {
                    $user_source->state->got_shirt = $request->postData('eTshirt');
                }
                $user_source->state->active = $request->postData('eAktiv');
                $user_source->state->force_active = $force_active;
                $user_source->state->save();

                engelsystem_log(
                    'Updated user: ' . $user_source->name . ' (' . $user_source->id . ')'
                    . ($goodie_tshirt ? ', t-shirt: ' : '' . $user_source->personalData->shirt_size)
                    . ', active: ' . $user_source->state->active
                    . ', force-active: ' . $user_source->state->force_active
                    . ($goodie_tshirt ? ', tshirt: ' : ', goodie: ' . $user_source->state->got_shirt)
                );
                $html .= success(__('Changes were saved.') . "\n", true);
                break;

            case 'change_pw':
                if (
                    $request->postData('new_pw') != ''
                    && $request->postData('new_pw') == $request->postData('new_pw2')
                ) {
                    $user_source = User::find($user_id);
                    auth()->setPassword($user_source, $request->postData('new_pw'));
                    engelsystem_log('Set new password for ' . User_Nick_render($user_source, true));
                    $html .= success(__('Password reset done.'), true);
                } else {
                    $html .= error(
                        __('The entries must match and must not be empty!'),
                        true
                    );
                }
                break;
        }
    }

    return page_with_title(__('Edit user'), [
        $html,
    ]);
}

/**
 * @param $myHighestGroup
 * @param $angelId
 * @return Collection|Group[]
 */
function changeableGroups($myHighestGroup, $angelId): Collection
{
    return Group::query()
        ->where('groups.id', '<=', $myHighestGroup)
        ->join('users_groups', function ($query) use ($angelId) {
            /** @var JoinClause $query */
            $query->where('users_groups.group_id', '=', $query->raw('groups.id'))
                ->where('users_groups.user_id', $angelId);
        }, null, null, 'left outer')
        ->orderBy('name')
        ->get([
            'groups.*',
            'users_groups.group_id as selected',
        ]);
}
