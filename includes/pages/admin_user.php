<?php

use Carbon\Carbon;
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
    $user_info_edit = auth()->can('user.info.edit');
    $user_goodie_edit = auth()->can('user.goodie.edit');
    $user_nick_edit = auth()->can('user.nick.edit');
    $admin_arrive = auth()->can('admin_arrive');

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
        if ($goodie_enabled && $user_goodie_edit) {
            $html .= ' ' . __('If the angel is active, it can claim a goodie. If goodie is set to \'Yes\', the angel already got their goodie.');
        }
        $html .= '<br><br>';
        $html .= '<form action="'
            . url('/admin-user', ['action' => 'save', 'id' => $user_id])
            . '" method="post">' . "\n";
        $html .= form_csrf();
        $html .= '<table>' . "\n";
        $html .= '<input type="hidden" name="Type" value="Normal">' . "\n";
        $html .= '<tr><td>' . "\n";
        $html .= '<table>' . "\n";
        $html .= '  <tr><td>' . __('general.nick') . '</td><td>'
            . '<input size="40" name="nick" value="' . htmlspecialchars($user_source->name)
            . '" class="form-control" maxlength="24" ' . ($user_nick_edit ? '' : 'disabled') . '>'
            . '</td></tr>' . "\n";
        $html .= '  <tr><td>' . __('Last login') . '</td><td><p class="help-block">'
            . ($user_source->last_login_at ? $user_source->last_login_at->format(__('general.datetime')) : '-')
            . '</p></td></tr>' . "\n";
        if (config('enable_full_name')) {
            $html .= '  <tr><td>' . __('settings.profile.firstname') . '</td><td>'
                . '<input size="40" name="first_name" value="' . htmlspecialchars((string) $user_source->personalData->first_name) . '" class="form-control" maxlength="64">'
                . '</td></tr>' . "\n";
            $html .= '  <tr><td>' . __('settings.profile.lastname') . '</td><td>'
                . '<input size="40" name="last_name" value="' . htmlspecialchars((string) $user_source->personalData->last_name) . '" class="form-control" maxlength="64">'
                . '</td></tr>' . "\n";
        }
        $html .= '  <tr><td>' . __('settings.profile.mobile') . '</td><td>'
            . '<input type= "tel" size="40" name="mobile" value="' . htmlspecialchars((string) $user_source->contact->mobile) . '" class="form-control" maxlength="40">'
            . '</td></tr>' . "\n";
        if (config('enable_dect')) {
            $html .= '  <tr><td>' . __('general.dect') . '</td><td>'
                . '<input size="40" name="dect" value="' . htmlspecialchars((string) $user_source->contact->dect) . '" class="form-control" maxlength="40">'
                . '</td></tr>' . "\n";
        }
        if ($user_source->settings->email_human) {
            $html .= '  <tr><td>' . __('general.email') . '</td><td>'
                . '<input type="email" size="40" name="mail" value="' . htmlspecialchars($user_source->email) . '" class="form-control" maxlength="254">'
                . '</td></tr>' . "\n";
        }
        if ($goodie_tshirt && $user_goodie_edit) {
            $html .= '  <tr><td>' . __('user.shirt_size') . '</td><td>'
                . html_select_key(
                    'size',
                    'shirt_size',
                    $tshirt_sizes,
                    $user_source->personalData->shirt_size,
                    __('form.select_placeholder')
                )
                . '</td></tr>' . "\n";
        }

        // User info
        if ($user_info_edit) {
            $html .= '  <tr><td>'
            . __('user.info')
            . ' <span class="bi bi-info-circle-fill text-info" data-bs-toggle="tooltip" title="'
            . __('user.info.hint')
            . '"></span>'
            . '</td><td>'
            . '<textarea cols="40" rows="" name="userInfo" class="form-control">'
            . htmlspecialchars((string) $user_source->state->user_info)
            . '</textarea>'
            . '</td></tr>' . "\n";
        }

        $options = [
            '1' => __('Yes'),
            '0' => __('No'),
        ];

        // Arrived?
        $html .= '  <tr><td>' . __('user.arrived') . '</td><td>' . "\n";
        $html .= $admin_arrive
            ? html_options('arrive', $options, $user_source->state->arrived)
            : icon_bool($user_source->state->arrived);
        $html .= '</td></tr>' . "\n";

        // Active?
        $html .= '  <tr><td>' . __('user.active') . '</td><td>' . "\n";
        $html .= $user_goodie_edit
            ? html_options('active', $options, $user_source->state->active)
            : icon_bool($user_source->state->active);
        $html .= '</td></tr>' . "\n";

        // Forced active?
        if (config('enable_force_active')) {
            $html .= '  <tr><td>' . __('Force active') . '</td><td>' . "\n";
            $html .= auth()->can('user.fa.edit')
                ? html_options('force_active', $options, $user_source->state->force_active)
                : icon_bool($user_source->state->force_active);
            $html .= '</td></tr>' . "\n";
        }

        // Forced food?
        if (config('enable_force_food')) {
            $html .= '  <tr><td>' . __('Force food') . '</td><td>' . "\n";
            $html .= auth()->can('user.ff.edit')
                ? html_options('force_food', $options, $user_source->state->force_food)
                : icon_bool($user_source->state->force_food);
            $html .= '</td></tr>' . "\n";
        }

        if ($goodie_enabled) {
            // got goodie?
            $html .= '  <tr><td>'
                . __('Goodie')
                . '</td><td>' . "\n";
            $html .= $user_goodie_edit
                ? html_options('goodie', $options, $user_source->state->got_goodie)
                : icon_bool($user_source->state->got_goodie);
            $html .= '</td></tr>' . "\n";
        }

        // Minor consent approval section
        if ($user_source->isMinor()) {
            $html .= '</table>' . "\n";
            $html .= '</td></tr></table>' . "\n";
            $html .= '<button type="submit" class="btn btn-primary">'
                . icon('save') . __('form.save') . '</button>' . "\n";
            $html .= '</form>';

            $html .= '<hr>';
            $html .= '<h4>' . icon('shield-check') . ' ' . __('admin.user.minor_consent') . '</h4>';
            $html .= '<table class="table">' . "\n";
            $html .= '  <tr><td>' . __('admin.user.minor_category') . '</td><td>'
                . htmlspecialchars($user_source->minorCategory->name ?? '-')
                . '</td></tr>' . "\n";

            if ($user_source->hasConsentApproved()) {
                $html .= '  <tr><td>' . __('admin.user.consent_status') . '</td><td>'
                    . '<span class="badge bg-success">' . icon('check-circle-fill') . ' ' . __('admin.user.consent_approved') . '</span>'
                    . '</td></tr>' . "\n";
                $html .= '  <tr><td>' . __('admin.user.consent_approved_by') . '</td><td>'
                    . User_Nick_render($user_source->consentApprovedBy)
                    . '</td></tr>' . "\n";
                $html .= '  <tr><td>' . __('admin.user.consent_approved_at') . '</td><td>'
                    . ($user_source->consent_approved_at ? $user_source->consent_approved_at->format(__('general.datetime')) : '-')
                    . '</td></tr>' . "\n";
            } else {
                $html .= '  <tr><td>' . __('admin.user.consent_status') . '</td><td>'
                    . '<span class="badge bg-warning text-dark">' . icon('exclamation-triangle-fill') . ' ' . __('admin.user.consent_pending') . '</span>'
                    . '</td></tr>' . "\n";
            }
            $html .= '</table>' . "\n";

            // Consent action buttons
            if (auth()->can('user.info.edit')) {
                $html .= '<form action="'
                    . url('/admin-user', ['action' => 'consent', 'id' => $user_id])
                    . '" method="post" class="d-inline">' . "\n";
                $html .= form_csrf();
                if ($user_source->hasConsentApproved()) {
                    $html .= '<button type="submit" name="consent_action" value="revoke" class="btn btn-danger" '
                        . 'onclick="return confirm(\'' . __('admin.user.consent_revoke_confirm') . '\')">'
                        . icon('x-circle') . ' ' . __('admin.user.consent_revoke') . '</button>';
                } else {
                    $html .= '<button type="submit" name="consent_action" value="approve" class="btn btn-success">'
                        . icon('check-circle') . ' ' . __('admin.user.consent_approve') . '</button>';
                }
                $html .= '</form>';
            }

            // Continue with remaining form sections
            goto password_section;
        }

        $html .= '</table>' . "\n" . '</td><td></td></tr>';

        $html .= '</td></tr>' . "\n";
        $html .= '</table>' . "\n" . '<br>' . "\n";
        $html .= '<button type="submit" class="btn btn-primary">'
            . icon('save') . __('form.save') . '</button>' . "\n";
        $html .= '</form>';

        password_section:
        $html .= '<hr>';
        $html .= __('Here you can reset the password of this angel:');

        $html .= '<form action="'
            . url('/admin-user', ['action' => 'change_pw', 'id' => $user_id])
            . '" method="post">' . "\n";
        $html .= form_csrf();
        $html .= '<table>' . "\n";
        $html .= '  <tr><td>' . __('settings.password')
            . ' <span class="bi bi-info-circle-fill text-info" data-bs-toggle="tooltip" title="'
            . __('password.minimal_length', [config('password_min_length')]) . '"></span>'
            . '</td><td>'
            . '<input type="password" size="40" name="new_pw" value="" class="form-control" autocomplete="new-password">'
            . '</td></tr>' . "\n";
        $html .= '  <tr><td>' . __('password.reset.confirm') . '</td><td>'
            . '<input type="password" size="40" name="new_pw2" value="" class="form-control" autocomplete="new-password">'
            . '</td></tr>' . "\n";

        $html .= '</table>' . "\n" . '<br>' . "\n";
        $html .= '<button type="submit" class="btn btn-primary">'
            . icon('save') . __('form.save') . '</button>' . "\n";
        $html .= '</form>';

        $html .= '<hr>';

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
                . url('/admin-user', ['action' => 'save_groups', 'id' => $user_id])
                . '" method="post">' . "\n";
            $html .= form_csrf();
            $html .= '<div>';

            $groups = changeableGroups($my_highest_group, $user_id);
            foreach ($groups as $group) {
                $html .= '<div class="form-check">'
                    . '<input class="form-check-input" type="checkbox" id="' . $group->id . '" name="groups[]" value="' . $group->id . '" '
                    . ($group->selected ? ' checked="checked"' : '')
                    . ' /><label class="form-check-label" for="' . $group->id . '">'
                    . htmlspecialchars($group->name)
                    . '</label></div>';
            }

            $html .= '</div><br>';

            $html .= '<button type="submit" class="btn btn-primary">'
                . icon('save') . __('form.save') . '</button>' . "\n";
            $html .= '</form>';

            $html .= '<hr>';
        }

        $html .= buttons([
            button(user_delete_link($user_source->id), icon('trash') . __('form.delete'), 'btn-danger'),
        ]);

        $html .= '<hr>';
    } else {
        switch ($request->input('action')) {
            case 'save_groups':
                /** @var User $angel */
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

                        $defaultGroup = auth()->getDefaultRole();
                        if (
                            !in_array($defaultGroup, $groupsRequest)
                            && $angel->groups->where('id', $defaultGroup)->count()
                        ) {
                            if (!auth()->can('admin_groups') && !config('default_group_removable')) {
                                $html .= error(__('You cannot remove the default group.'), true);
                                break;
                            } else {
                                $html .= warning(
                                    __('You removed the default group, this has unintended side effects!'),
                                    true
                                );
                            }
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
                /** @var User $user_source */
                $user_source = User::findOrFail($user_id);

                $changed_email = false;
                $email = $request->postData('mail');
                if (
                    $user_source->email !== $email
                    && User::whereEmail($email)->whereNot('id', $user_source->id)->exists()
                ) {
                    $html .= error(__('settings.profile.email.already-taken') . "\n", true);
                    break;
                }
                if ($user_source->settings->email_human) {
                    $changed_email = $user_source->email !== $email;
                    $user_source->email = $email;
                }

                $changed_nick = false;
                $nick = trim((string) $request->get('nick'));
                $nickValid = (new Username())->validate($nick);
                if (
                    $user_source->name !== $nick
                    && User::whereName($nick)->whereNot('id', $user_source->id)->exists()
                ) {
                    $html .= error(__('settings.profile.nick.already-taken') . "\n", true);
                    break;
                }
                $old_nick = $user_source->name;
                if ($nickValid && $user_nick_edit) {
                    $changed_nick = $user_source->name !== $nick
                        && !User::whereName($nick)->whereNot('id', $user_source->id)->exists();
                    $user_source->name = $nick;
                }
                $user_source->save();

                if (config('enable_full_name')) {
                    $user_source->personalData->first_name = $request->postData('first_name');
                    $user_source->personalData->last_name = $request->postData('last_name');
                }
                if ($goodie_tshirt && $user_goodie_edit) {
                    $user_source->personalData->shirt_size = $request->postData('shirt_size');
                }
                $user_source->personalData->save();

                $user_source->contact->mobile = $request->postData('mobile');
                if (config('enable_dect')) {
                    $user_source->contact->dect = $request->postData('dect');
                }
                $user_source->contact->save();

                if ($goodie_enabled && $user_goodie_edit) {
                    $user_source->state->got_goodie = $request->postData('goodie');
                }
                if ($user_info_edit) {
                    $user_source->state->user_info = $request->postData('userInfo');
                }
                if ($admin_arrive) {
                    if ($user_source->state->arrived != $request->postData('arrive')) {
                        if ($request->postData('arrive')) {
                            $user_source->state->arrival_date = new Carbon();
                        } else {
                            $user_source->state->arrival_date = null;
                        }
                    }
                }

                if ($user_goodie_edit) {
                    $user_source->state->active = $request->postData('active');
                }
                if (auth()->can('user.fa.edit') && config('enable_force_active')) {
                    $user_source->state->force_active = $request->input('force_active');
                }
                if (auth()->can('user.ff.edit') && config('enable_force_food')) {
                    $user_source->state->force_food = $request->input('force_food');
                }
                $user_source->state->save();

                engelsystem_log(
                    'Updated user: ' . ($changed_nick
                        ? ('nick modified from ' . $old_nick . ' (' . $user_source->id . ') to ' . $user_source->name)
                        : $user_source->name)
                    . ' (' . $user_source->id . ')'
                    . ($changed_email ? ', e-mail modified' : '')
                    . ($goodie_tshirt ? ', T-shirt size: ' . $user_source->personalData->shirt_size : '')
                    . ', arrived: ' . $user_source->state->arrived
                    . ', active: ' . $user_source->state->active
                    . (config('enable_force_active') ? (', force-active: ' . $user_source->state->force_active) : '')
                    . (config('enable_force_food') ? (', force-food: ' . $user_source->state->force_food) : '')
                    . ($goodie_enabled ? ', goodie: ' . $user_source->state->got_goodie : '')
                    . ($user_info_edit ? ', user-info: ' . $user_source->state->user_info : '')
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

            case 'consent':
                if (!auth()->can('user.info.edit')) {
                    $html .= error(__('No permission to manage consent.'), true);
                    break;
                }

                /** @var User $user_source */
                $user_source = User::findOrFail($user_id);

                if (!$user_source->isMinor()) {
                    $html .= error(__('admin.user.not_minor'), true);
                    break;
                }

                $consent_action = $request->postData('consent_action');
                if ($consent_action === 'approve') {
                    $user_source->consent_approved_by_user_id = $user->id;
                    $user_source->consent_approved_at = Carbon::now();
                    $user_source->save();
                    engelsystem_log(
                        'Approved minor consent for ' . User_Nick_render($user_source, true)
                    );
                    $html .= success(__('admin.user.consent_approved_success'), true);
                } elseif ($consent_action === 'revoke') {
                    $user_source->consent_approved_by_user_id = null;
                    $user_source->consent_approved_at = null;
                    $user_source->save();
                    engelsystem_log(
                        'Revoked minor consent for ' . User_Nick_render($user_source, true)
                    );
                    $html .= success(__('admin.user.consent_revoked_success'), true);
                }
                break;
        }
    }

    $link = button(url('/users', ['action' => 'view', 'user_id' => $user_id]), icon('chevron-left'), 'btn-sm', '', __('general.back'));
    return page_with_title(
        $link . ' ' . __('Edit user'),
        [
        $html,
        ]
    );
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
