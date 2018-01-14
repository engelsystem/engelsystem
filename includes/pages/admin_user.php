<?php

use Engelsystem\Database\DB;

/**
 * @return string
 */
function admin_user_title()
{
    return _('All Angels');
}

/**
 * @return string
 */
function admin_user()
{
    global $user, $privileges;
    $tshirt_sizes = config('tshirt_sizes');
    $request = request();

    foreach ($tshirt_sizes as $key => $size) {
        if (empty($size)) {
            unset($tshirt_sizes[$key]);
        }
    }

    $html = '';

    if (!$request->has('id')) {
        redirect(users_link());
    }

    $user_id = $request->input('id');
    if (!$request->has('action')) {
        $user_source = User($user_id);
        if (empty($user_source)) {
            error(_('This user does not exist.'));
            redirect(users_link());
        }

        $html .= 'Hallo,<br />'
            . 'hier kannst du den Eintrag &auml;ndern. Unter dem Punkt \'Gekommen\' '
            . 'wird der Engel als anwesend markiert, ein Ja bei Aktiv bedeutet, '
            . 'dass der Engel aktiv war und damit ein Anspruch auf ein T-Shirt hat. '
            . 'Wenn T-Shirt ein \'Ja\' enth&auml;lt, bedeutet dies, dass der Engel '
            . 'bereits sein T-Shirt erhalten hat.<br /><br />' . "\n";

        $html .= '<form action="'
            . page_link_to('admin_user', ['action' => 'save', 'id' => $user_id])
            . '" method="post">' . "\n";
        $html .= '<table border="0">' . "\n";
        $html .= '<input type="hidden" name="Type" value="Normal">' . "\n";
        $html .= '<tr><td>' . "\n";
        $html .= '<table>' . "\n";
        $html .= '  <tr><td>Nick</td><td>' . '<input size="40" name="eNick" value="' . $user_source['Nick'] . '" class="form-control"></td></tr>' . "\n";
        $html .= '  <tr><td>Last login</td><td><p class="help-block">'
            . date('Y-m-d H:i', $user_source['lastLogIn'])
            . '</p></td></tr>' . "\n";
        $html .= '  <tr><td>Name</td><td>' . '<input size="40" name="eName" value="' . $user_source['Name'] . '" class="form-control"></td></tr>' . "\n";
        $html .= '  <tr><td>Vorname</td><td>' . '<input size="40" name="eVorname" value="' . $user_source['Vorname'] . '" class="form-control"></td></tr>' . "\n";
        $html .= '  <tr><td>Alter</td><td>' . '<input size="5" name="eAlter" value="' . $user_source['Alter'] . '" class="form-control"></td></tr>' . "\n";
        $html .= '  <tr><td>Telefon</td><td>' . '<input size="40" name="eTelefon" value="' . $user_source['Telefon'] . '" class="form-control"></td></tr>' . "\n";
        $html .= '  <tr><td>Handy</td><td>' . '<input size="40" name="eHandy" value="' . $user_source['Handy'] . '" class="form-control"></td></tr>' . "\n";
        $html .= '  <tr><td>DECT</td><td>' . '<input size="4" name="eDECT" value="' . $user_source['DECT'] . '" class="form-control"></td></tr>' . "\n";
        if ($user_source['email_by_human_allowed']) {
            $html .= "  <tr><td>email</td><td>" . '<input size="40" name="eemail" value="' . $user_source['email'] . '" class="form-control"></td></tr>' . "\n";
        }
        $html .= "  <tr><td>jabber</td><td>" . '<input size="40" name="ejabber" value="' . $user_source['jabber'] . '" class="form-control"></td></tr>' . "\n";
        $html .= '  <tr><td>Size</td><td>'
            . html_select_key('size', 'eSize', $tshirt_sizes, $user_source['Size']) . '</td></tr>' . "\n";

        $options = [
            '1' => _('Yes'),
            '0' => _('No')
        ];

        // Gekommen?
        $html .= '  <tr><td>Gekommen</td><td>' . "\n";
        $html .= html_options('eGekommen', $options, $user_source['Gekommen']) . '</td></tr>' . "\n";

        // Aktiv?
        $html .= '  <tr><td>Aktiv</td><td>' . "\n";
        $html .= html_options('eAktiv', $options, $user_source['Aktiv']) . '</td></tr>' . "\n";

        // Aktiv erzwingen
        if (in_array('admin_active', $privileges)) {
            $html .= '  <tr><td>' . _('Force active') . '</td><td>' . "\n";
            $html .= html_options('force_active', $options, $user_source['force_active']) . '</td></tr>' . "\n";
        }

        // T-Shirt bekommen?
        $html .= '  <tr><td>T-Shirt</td><td>' . "\n";
        $html .= html_options('eTshirt', $options, $user_source['Tshirt']) . '</td></tr>' . "\n";

        $html .= '  <tr><td>Hometown</td><td>' . '<input size="40" name="Hometown" value="' . $user_source['Hometown'] . '" class="form-control"></td></tr>' . "\n";

        $html .= '</table>' . "\n" . '</td><td valign="top"></td></tr>';

        $html .= '</td></tr>' . "\n";
        $html .= '</table>' . "\n" . '<br />' . "\n";
        $html .= '<input type="submit" value="Speichern" class="btn btn-primary">';
        $html .= '</form>';

        $html .= '<hr />';

        $html .= form_info('', _('Please visit the angeltypes page or the users profile to manage users angeltypes.'));

        $html .= 'Hier kannst Du das Passwort dieses Engels neu setzen:<form action="'
            . page_link_to('admin_user', ['action' => 'change_pw', 'id' => $user_id])
            . '" method="post">' . "\n";
        $html .= '<table>' . "\n";
        $html .= '  <tr><td>Passwort</td><td>' . '<input type="password" size="40" name="new_pw" value="" class="form-control"></td></tr>' . "\n";
        $html .= '  <tr><td>Wiederholung</td><td>' . '<input type="password" size="40" name="new_pw2" value="" class="form-control"></td></tr>' . "\n";

        $html .= '</table>' . "\n" . '<br />' . "\n";
        $html .= '<input type="submit" value="Speichern" class="btn btn-primary">' . "\n";
        $html .= '</form>';

        $html .= '<hr />';

        $my_highest_group = DB::selectOne(
            'SELECT group_id FROM `UserGroups` WHERE `uid`=? ORDER BY `group_id` LIMIT 1',
            [$user['UID']]
        );
        if (!empty($my_highest_group)) {
            $my_highest_group = $my_highest_group['group_id'];
        }

        $his_highest_group = DB::selectOne(
            'SELECT `group_id` FROM `UserGroups` WHERE `uid`=? ORDER BY `group_id` LIMIT 1',
            [$user_id]
        );
        if (!empty($his_highest_group)) {
            $his_highest_group = $his_highest_group['group_id'];
        }

        if ($user_id != $user['UID'] && $my_highest_group <= $his_highest_group) {
            $html .= 'Hier kannst Du die Benutzergruppen des Engels festlegen:<form action="'
                . page_link_to('admin_user', ['action' => 'save_groups', 'id' => $user_id])
                . '" method="post">' . "\n";
            $html .= '<table>';

            $groups = DB::select('
                    SELECT *
                    FROM `Groups`
                    LEFT OUTER JOIN `UserGroups` ON (
                        `UserGroups`.`group_id` = `Groups`.`UID`
                        AND `UserGroups`.`uid` = ?
                    )
                    WHERE `Groups`.`UID` >= ?
                    ORDER BY `Groups`.`Name`
                ',
                [
                    $user_id,
                    $my_highest_group,
                ]
            );
            foreach ($groups as $group) {
                $html .= '<tr><td><input type="checkbox" name="groups[]" value="' . $group['UID'] . '" '
                    . ($group['group_id'] != '' ? ' checked="checked"' : '')
                    . ' /></td><td>' . $group['Name'] . '</td></tr>';
            }

            $html .= '</table><br>';

            $html .= '<input type="submit" value="Speichern" class="btn btn-primary">' . "\n";
            $html .= '</form>';

            $html .= '<hr />';
        }

        $html .= buttons([
            button(user_delete_link($user_source), glyph('lock') . _('delete'), 'btn-danger')
        ]);

        $html .= "<hr />";
    } else {
        switch ($request->input('action')) {
            case 'save_groups':
                if ($user_id != $user['UID']) {
                    $my_highest_group = DB::selectOne(
                        'SELECT * FROM `UserGroups` WHERE `uid`=? ORDER BY `group_id`',
                        [$user['UID']]
                    );
                    $his_highest_group = DB::selectOne(
                        'SELECT * FROM `UserGroups` WHERE `uid`=? ORDER BY `group_id`',
                        [$user_id]
                    );

                    if (
                        count($my_highest_group) > 0
                        && (
                            count($his_highest_group) == 0
                            || ($my_highest_group['group_id'] <= $his_highest_group['group_id'])
                        )
                    ) {
                        $groups_source = DB::select('
                                SELECT *
                                FROM `Groups`
                                LEFT OUTER JOIN `UserGroups` ON (
                                    `UserGroups`.`group_id` = `Groups`.`UID`
                                    AND `UserGroups`.`uid` = ?
                                )
                                WHERE `Groups`.`UID` >= ?
                                ORDER BY `Groups`.`Name`
                            ',
                            [
                                $user_id,
                                $my_highest_group['group_id'],
                            ]
                        );
                        $groups = [];
                        $grouplist = [];
                        foreach ($groups_source as $group) {
                            $groups[$group['UID']] = $group;
                            $grouplist[] = $group['UID'];
                        }

                        $groupsRequest = $request->input('groups');
                        if (!is_array($groupsRequest)) {
                            $groupsRequest = [];
                        }

                        DB::delete('DELETE FROM `UserGroups` WHERE `uid`=?', [$user_id]);
                        $user_groups_info = [];
                        foreach ($groupsRequest as $group) {
                            if (in_array($group, $grouplist)) {
                                DB::insert(
                                    'INSERT INTO `UserGroups` (`uid`, `group_id`) VALUES (?, ?)',
                                    [$user_id, $group]
                                );
                                $user_groups_info[] = $groups[$group]['Name'];
                            }
                        }
                        $user_source = User($user_id);
                        engelsystem_log(
                            'Set groups of ' . User_Nick_render($user_source) . ' to: '
                            . join(', ', $user_groups_info)
                        );
                        $html .= success('Benutzergruppen gespeichert.', true);
                    } else {
                        $html .= error('Du kannst keine Engel mit mehr Rechten bearbeiten.', true);
                    }
                } else {
                    $html .= error('Du kannst Deine eigenen Rechte nicht bearbeiten.', true);
                }
                break;

            case 'save':
                $force_active = $user['force_active'];
                $user_source = User($user_id);
                if (in_array('admin_active', $privileges)) {
                    $force_active = $request->input('force_active');
                }
                $sql = '
                    UPDATE `User` SET
                      `Nick` = ?,
                      `Name` = ?,
                      `Vorname` = ?,
                      `Telefon` = ?,
                      `Handy` = ?,
                      `Alter` =?,
                      `DECT` = ?,
                      ' . ($user_source['email_by_human_allowed']
                        ? '`email` = ' . DB::getPdo()->quote($request->postData('eemail')) . ','
                        : '') . '
                      `jabber` = ?,
                      `Size` = ?,
                      `Gekommen`= ?,
                      `Aktiv`= ?,
                      `force_active`= ?,
                      `Tshirt` = ?,
                      `Hometown` = ?
                      WHERE `UID` = ?
                      LIMIT 1';
                DB::update($sql, [
                    User_validate_Nick($request->postData('eNick')),
                    $request->postData('eName'),
                    $request->postData('eVorname'),
                    $request->postData('eTelefon'),
                    $request->postData('eHandy'),
                    $request->postData('eAlter'),
                    $request->postData('eDECT'),
                    $request->postData('ejabber'),
                    $request->postData('eSize'),
                    $request->postData('eGekommen'),
                    $request->postData('eAktiv'),
                    $force_active,
                    $request->postData('eTshirt'),
                    $request->postData('Hometown'),
                    $user_id,
                ]);
                engelsystem_log(
                    'Updated user: ' . $request->postData('eNick') . ', ' . $request->postData('eSize')
                    . ', arrived: ' . $request->postData('eVorname')
                    . ', active: ' . $request->postData('eAktiv')
                    . ', tshirt: ' . $request->postData('eTshirt')
                );
                $html .= success('Änderung wurde gespeichert...' . "\n", true);
                break;

            case 'change_pw':
                if (
                    $request->postData('new_pw') != ''
                    && $request->postData('new_pw') == $request->postData('new_pw2')
                ) {
                    set_password($user_id, $request->postData('new_pw'));
                    $user_source = User($user_id);
                    engelsystem_log('Set new password for ' . User_Nick_render($user_source));
                    $html .= success('Passwort neu gesetzt.', true);
                } else {
                    $html .= error(
                        'Die Eingaben müssen übereinstimmen und dürfen nicht leer sein!',
                        true
                    );
                }
                break;
        }
    }

    return page_with_title(_('Edit user'), [
        $html
    ]);
}
