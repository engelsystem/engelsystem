<?php

use Engelsystem\Database\Db;

/**
 * @return string
 */
function admin_groups_title()
{
    return __('Grouprights');
}

/**
 * @return string
 */
function admin_groups()
{
    $html = '';
    $request = request();
    $groups = Db::select('SELECT * FROM `Groups` ORDER BY `Name`');

    if (!$request->has('action')) {
        $groups_table = [];
        foreach ($groups as $group) {
            $privileges = Db::select('
                SELECT `name`
                FROM `GroupPrivileges`
                JOIN `Privileges` ON (`GroupPrivileges`.`privilege_id` = `Privileges`.`id`)
                WHERE `group_id`=?
                ORDER BY `name`
            ', [$group['UID']]);
            $privileges_html = [];

            foreach ($privileges as $privilege) {
                $privileges_html[] = $privilege['name'];
            }

            $groups_table[] = [
                'name'       => $group['Name'],
                'privileges' => join(', ', $privileges_html),
                'actions'    => button(
                    page_link_to('admin_groups',
                        ['action' => 'edit', 'id' => $group['UID']]),
                    __('edit'),
                    'btn-sm'
                )
            ];
        }

        return page_with_title(admin_groups_title(), [
            table([
                'name'       => __('Name'),
                'privileges' => __('Privileges'),
                'actions'    => ''
            ], $groups_table)
        ]);
    } else {
        switch ($request->input('action')) {
            case 'edit':
                if ($request->has('id') && preg_match('/^-\d{1,11}$/', $request->input('id'))) {
                    $group_id = $request->input('id');
                } else {
                    return error('Incomplete call, missing Groups ID.', true);
                }

                $group = Db::select('SELECT * FROM `Groups` WHERE `UID`=? LIMIT 1', [$group_id]);
                if (!empty($group)) {
                    $privileges = Db::select('
                        SELECT `Privileges`.*, `GroupPrivileges`.`group_id`
                        FROM `Privileges`
                        LEFT OUTER JOIN `GroupPrivileges`
                            ON (
                                `Privileges`.`id` = `GroupPrivileges`.`privilege_id`
                                AND `GroupPrivileges`.`group_id`=?
                            )
                        ORDER BY `Privileges`.`name`
                    ', [$group_id]);
                    $privileges_html = '';
                    $privileges_form = [];
                    foreach ($privileges as $privilege) {
                        $privileges_form[] = form_checkbox(
                            'privileges[]',
                            $privilege['desc'] . ' (' . $privilege['name'] . ')',
                            $privilege['group_id'] != '',
                            $privilege['id'],
                            'privilege-' . $privilege['name']
                        );
                        $privileges_html .= sprintf(
                            '<tr>'
                            . '<td><input type="checkbox" name="privileges[]" value="%s" %s /></td>'
                            . '<td>%s</td>'
                            . '<td>%s</td>'
                            . '</tr>',
                            $privilege['id'],
                            ($privilege['group_id'] != '' ? 'checked="checked"' : ''),
                            $privilege['name'],
                            $privilege['desc']
                        );
                    }

                    $privileges_form[] = form_submit('submit', __('Save'));
                    $html .= page_with_title(__('Edit group'), [
                        form(
                            $privileges_form,
                            page_link_to('admin_groups', ['action' => 'save', 'id' => $group_id])
                        )
                    ]);
                } else {
                    return error('No Group found.', true);
                }
                break;

            case 'save':
                if (
                    $request->has('id')
                    && preg_match('/^-\d{1,11}$/', $request->input('id'))
                    && $request->hasPostData('submit')
                ) {
                    $group_id = $request->input('id');
                } else {
                    return error('Incomplete call, missing Groups ID.', true);
                }

                $group = Db::selectOne('SELECT * FROM `Groups` WHERE `UID`=? LIMIT 1', [$group_id]);
                $privileges = $request->request->all('privileges');
                if (!empty($group)) {
                    Db::delete('DELETE FROM `GroupPrivileges` WHERE `group_id`=?', [$group_id]);
                    $privilege_names = [];
                    foreach ($privileges as $privilege) {
                        if (preg_match('/^\d+$/', $privilege)) {
                            $group_privileges_source = Db::selectOne(
                                'SELECT `name` FROM `Privileges` WHERE `id`=? LIMIT 1',
                                [$privilege]
                            );
                            if (!empty($group_privileges_source)) {
                                Db::insert(
                                    'INSERT INTO `GroupPrivileges` (`group_id`, `privilege_id`) VALUES (?, ?)',
                                    [$group_id, $privilege]
                                );
                                $privilege_names[] = $group_privileges_source['name'];
                            }
                        }
                    }
                    engelsystem_log(
                        'Group privileges of group ' . $group['Name']
                        . ' edited: ' . join(', ', $privilege_names)
                    );
                    throw_redirect(page_link_to('admin_groups'));
                } else {
                    return error('No Group found.', true);
                }
                break;
        }
    }
    return $html;
}
