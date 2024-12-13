<?php

use Engelsystem\Models\Group;
use Engelsystem\Models\Privilege;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * @return string
 */
function admin_groups_title()
{
    return __('Group rights');
}

/**
 * @return string
 */
function admin_groups()
{
    $html = '';
    $request = request();
    /** @var Group[]|Collection $groups */
    $groups = Group::with('privileges')->orderBy('name')->get();

    if (!$request->has('action')) {
        $groups_table = [];
        foreach ($groups as $group) {
            /** @var Privilege[]|Collection $privileges */
            $privileges = $group->privileges->sortBy('name');
            $privileges_html = [];

            foreach ($privileges as $privilege) {
                $privileges_html[] = htmlspecialchars($privilege['name']);
            }

            $groups_table[] = [
                'name'       => htmlspecialchars($group->name),
                'privileges' => join(', ', $privileges_html),
                'actions'    => button(
                    url(
                        '/admin-groups',
                        ['action' => 'edit', 'id' => $group->id]
                    ),
                    icon('pencil'),
                    'btn-sm',
                    '',
                    __('form.edit')
                ),
            ];
        }

        return page_with_title(admin_groups_title(), [
            table([
                'name'       => __('general.name'),
                'privileges' => __('Privileges'),
                'actions'    => '',
            ], $groups_table),
        ]);
    } else {
        switch ($request->input('action')) {
            case 'edit':
                if ($request->has('id')) {
                    $group_id = (int) $request->input('id');
                } else {
                    return error('Incomplete call, missing Groups ID.', true);
                }

                /** @var Group|null $group */
                $group = Group::find($group_id);
                if (!empty($group)) {
                    $privileges = groupPrivilegesWithSelected($group);
                    $privileges_form = [];
                    foreach ($privileges as $privilege) {
                        $privileges_form[] = form_checkbox(
                            'privileges[]',
                            htmlspecialchars($privilege->description . ' (' . $privilege->name . ')'),
                            $privilege->selected != '',
                            $privilege->id,
                            'privilege-' . htmlspecialchars($privilege->name)
                        );
                    }

                    $privileges_form[] = form_submit('submit', icon('save') . __('form.save'));
                    $html .= page_with_title(__('Edit group') . ' ' . htmlspecialchars($group->name), [
                        form(
                            $privileges_form,
                            url('/admin-groups', ['action' => 'save', 'id' => $group->id])
                        ),
                    ]);
                } else {
                    return error('No Group found.', true);
                }
                break;

            case 'save':
                if (
                    $request->has('id')
                    && $request->hasPostData('submit')
                ) {
                    $group_id = (int) $request->input('id');
                } else {
                    return error('Incomplete call, missing Groups ID.', true);
                }

                /** @var Group|null $group */
                $group = Group::find($group_id);
                $privileges = $request->request->all('privileges');
                if (!empty($group)) {
                    $group->privileges()->detach();
                    $privilege_names = [];
                    foreach ($privileges as $privilege) {
                        $privilege = Privilege::find($privilege);
                        if ($privilege) {
                            $group->privileges()->attach($privilege);
                            $privilege_names[] = $privilege->name;
                        }
                    }
                    engelsystem_log(
                        'Group privileges of group ' . $group->name
                        . ' edited: ' . join(', ', $privilege_names)
                    );
                    throw_redirect(url('/admin-groups'));
                }

                return error('No Group found.', true);
        }
    }
    return $html;
}

/**
 * @param Group $group
 * @return Collection|Privilege[]
 */
function groupPrivilegesWithSelected(Group $group): Collection
{
    return Privilege::query()
        ->join('group_privileges', function ($query) use ($group) {
            /** @var JoinClause $query */
            $query
                ->where('privileges.id', '=', $query->raw('group_privileges.privilege_id'))
                ->where('group_privileges.group_id', $group->id)
            ;
        }, null, null, 'left outer')
        ->orderBy('name')
        ->get([
            'privileges.*',
            'group_privileges.group_id as selected',
        ]);
}
