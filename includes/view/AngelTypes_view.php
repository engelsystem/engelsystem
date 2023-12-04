<?php

use Engelsystem\Models\AngelType;
use Engelsystem\Models\User\License;
use Engelsystem\Models\User\User;
use Engelsystem\Models\UserAngelType;
use Engelsystem\ShiftCalendarRenderer;
use Engelsystem\ShiftsFilterRenderer;
use Illuminate\Support\Collection;

/**
 * AngelTypes
 */

/**
 * Renders the angeltypes name as link.
 *
 * @param AngelType $angeltype
 * @param bool      $plain
 * @return string
 */
function AngelType_name_render(AngelType $angeltype, $plain = false)
{
    if ($plain) {
        return sprintf('%s (%u)', $angeltype->name, $angeltype->id);
    }

    return '<a href="' . angeltype_link($angeltype->id) . '">'
        . ($angeltype->restricted ? icon('mortarboard-fill') : '') . htmlspecialchars($angeltype->name)
        . '</a>';
}

/**
 * Render angeltype membership state
 *
 * @param AngelType $user_angeltype UserAngelType and AngelType
 * @return string
 */
function AngelType_render_membership(AngelType $user_angeltype)
{
    if (!empty($user_angeltype->user_angel_type_id)) {
        if ($user_angeltype->restricted) {
            if (empty($user_angeltype->confirm_user_id)) {
                return icon('mortarboard-fill') . __('Unconfirmed');
            } elseif ($user_angeltype->supporter) {
                return icon_bool(true) . __('Supporter');
            }
            return icon_bool(true) . __('Member');
        } elseif ($user_angeltype->supporter) {
            return icon_bool(true) . __('Supporter');
        }
        return icon_bool(true) . __('Member');
    }
    return icon_bool(false);
}

/**
 * @param AngelType $angeltype
 * @return string
 */
function AngelType_delete_view(AngelType $angeltype)
{
    return page_with_title(sprintf(__('Delete angeltype %s'), htmlspecialchars($angeltype->name)), [
        info(sprintf(__('Do you want to delete angeltype %s?'), $angeltype->name), true),
        form([
            buttons([
                button(page_link_to('angeltypes'), icon('x-lg') . __('cancel')),
                form_submit('delete', icon('trash') . __('delete'), 'btn-danger', false),
            ]),
        ]),
    ], true);
}

/**
 * Render angeltype edit form.
 *
 * @param AngelType $angeltype The angeltype to edit
 * @param boolean   $supporter_mode Is the user a supporter of this angeltype?
 * @return string
 */
function AngelType_edit_view(AngelType $angeltype, bool $supporter_mode)
{
    return page_with_title(sprintf(__('Edit %s'), htmlspecialchars((string) $angeltype->name)), [
        buttons([
            button(page_link_to('angeltypes'), icon('person-lines-fill') . __('Angeltypes'), 'back'),
        ]),
        msg(),
        form([
            $supporter_mode
                ? form_info(__('Name'), htmlspecialchars($angeltype->name))
                : form_text('name', __('Name'), $angeltype->name),
            $supporter_mode
                ? form_info(__('Requires introduction'), $angeltype->restricted ? __('Yes') : __('No'))
                : form_checkbox('restricted', __('Requires introduction'), $angeltype->restricted),
            form_info(
                '',
                __('Angel types which require introduction can only be used by an angel if enabled by a supporter (double opt-in).')
            ),
            $supporter_mode
                ? form_info(__('No Self Sign Up allowed'), $angeltype->no_self_signup ? __('Yes') : __('No'))
                : form_checkbox('no_self_signup', __('No Self Sign Up allowed'), $angeltype->no_self_signup),
            $supporter_mode ?
                form_info(
                    __('Requires driver license'),
                    $angeltype->requires_driver_license
                        ? __('Yes')
                        : __('No')
                ) :
                form_checkbox(
                    'requires_driver_license',
                    __('Requires driver license'),
                    $angeltype->requires_driver_license
                ),
            $supporter_mode && config('ifsg_enabled') ?
                form_info(
                    __('angeltype.ifsg.required'),
                    $angeltype->requires_ifsg_certificate
                        ? __('Yes')
                        : __('No')
                ) :
                form_checkbox(
                    'requires_ifsg_certificate',
                    __('angeltype.ifsg.required'),
                    $angeltype->requires_ifsg_certificate
                ),
            $supporter_mode
                ? form_info(__('Show on dashboard'), $angeltype->show_on_dashboard ? __('Yes') : __('No'))
                : form_checkbox('show_on_dashboard', __('Show on dashboard'), $angeltype->show_on_dashboard),
            $supporter_mode
                ? form_info(__('Hide at Registration'), $angeltype->hide_register ? __('Yes') : __('No'))
                : form_checkbox('hide_register', __('Hide at Registration'), $angeltype->hide_register),
            form_textarea('description', __('Description'), $angeltype->description),
            form_info('', __('Please use markdown for the description.')),
            heading(__('Contact'), 3),
            form_info(
                '',
                __('Primary contact person/desk for user questions.')
            ),
            form_text('contact_name', __('Name'), $angeltype->contact_name),
            config('enable_dect') ? form_text('contact_dect', __('DECT'), $angeltype->contact_dect) : '',
            form_text('contact_email', __('E-Mail'), $angeltype->contact_email),
            form_submit('submit', __('Save')),
        ]),
    ]);
}

/**
 * Renders the buttons for the angeltype view.
 *
 * @param AngelType          $angeltype
 * @param UserAngelType|null $user_angeltype
 * @param bool               $admin_angeltypes
 * @param bool               $supporter
 * @param License            $user_driver_license
 * @param User|null          $user
 * @return string
 */
function AngelType_view_buttons(
    AngelType $angeltype,
    ?UserAngelType $user_angeltype,
    $admin_angeltypes,
    $supporter,
    $user_driver_license,
    $user
) {
    $buttons = [
        button(page_link_to('angeltypes'), icon('person-lines-fill') . __('Angeltypes'), 'back'),
    ];

    if ($angeltype->requires_driver_license) {
        $buttons[] = button(
            user_driver_license_edit_link($user),
            icon('person-vcard') . __('my driving license')
        );
    }
    if (config('isfg_enabled') && $angeltype->requires_ifsg_certificate) {
        $buttons[] = button(
            url('/settings/certificates'),
            icon('card-checklist') . __('angeltype.ifsg.own')
        );
    }

    if (is_null($user_angeltype)) {
        $buttons[] = button(
            page_link_to('user_angeltypes', ['action' => 'add', 'angeltype_id' => $angeltype->id]),
            icon('box-arrow-in-right') . __('join'),
            'add'
        );
    } else {
        if ($angeltype->requires_driver_license && !$user_driver_license->wantsToDrive()) {
            error(__('This angeltype requires a driver license. Please enter your driver license information!'));
        }

        if (
            config('ifsg_enabled') && $angeltype->requires_ifsg_certificate && !(
            $user->license->ifsg_certificate_light || $user->license->ifsg_certificate
            )
        ) {
            error(__('angeltype.ifsg.required.info'));
        }

        if ($angeltype->restricted && !$user_angeltype->confirm_user_id) {
            error(sprintf(
                __('You are unconfirmed for this angeltype. Please go to the introduction for %s to get confirmed.'),
                $angeltype->name
            ));
        }
        $buttons[] = button(
            page_link_to('user_angeltypes', ['action' => 'delete', 'user_angeltype_id' => $user_angeltype->id]),
            icon('box-arrow-right') . __('leave')
        );
    }

    if ($admin_angeltypes || $supporter) {
        $buttons[] = button(
            page_link_to('angeltypes', ['action' => 'edit', 'angeltype_id' => $angeltype->id]),
            icon('pencil') . __('edit')
        );
    }
    if ($admin_angeltypes) {
        $buttons[] = button(
            page_link_to('angeltypes', ['action' => 'delete', 'angeltype_id' => $angeltype->id]),
            icon('trash') . __('delete')
        );
    }

    return buttons($buttons);
}

/**
 * Renders and sorts the members of an angeltype into supporters, members and unconfirmed members.
 *
 * @param AngelType $angeltype
 * @param User[]    $members
 * @param bool      $admin_user_angeltypes
 * @param bool      $admin_angeltypes
 * @return array [supporters, members, unconfirmed members]
 */
function AngelType_view_members(AngelType $angeltype, $members, $admin_user_angeltypes, $admin_angeltypes)
{
    $supporters = [];
    $members_confirmed = [];
    $members_unconfirmed = [];
    foreach ($members as $member) {
        $member->name = User_Nick_render($member) . User_Pronoun_render($member);
        if (config('enable_dect')) {
            $member['dect'] = htmlspecialchars((string) $member->contact->dect);
        }
        if ($angeltype->requires_driver_license) {
            $member['wants_to_drive'] = icon_bool($member->license->wantsToDrive());
            $member['has_car'] = icon_bool($member->license->has_car);
            $member['has_license_car'] = icon_bool($member->license->drive_car);
            $member['has_license_3_5t_transporter'] = icon_bool($member->license->drive_3_5t);
            $member['has_license_7_5t_truck'] = icon_bool($member->license->drive_7_5t);
            $member['has_license_12t_truck'] = icon_bool($member->license->drive_12t);
            $member['has_license_forklift'] = icon_bool($member->license->drive_forklift);
        }
        if ($angeltype->requires_ifsg_certificate && config('ifsg_enabled')) {
            $member['ifsg_certificate'] = icon_bool($member->license->ifsg_certificate);
            if (config('ifsg_light_enabled')) {
                $member['ifsg_certificate_light'] = icon_bool($member->license->ifsg_certificate_light);
            }
        }

        if ($angeltype->restricted && empty($member->pivot->confirm_user_id)) {
            $member['actions'] = table_buttons([
                button(
                    page_link_to(
                        'user_angeltypes',
                        ['action' => 'confirm', 'user_angeltype_id' => $member->pivot->id]
                    ),
                    __('confirm'),
                    'btn-sm'
                ),
                button(
                    page_link_to(
                        'user_angeltypes',
                        ['action' => 'delete', 'user_angeltype_id' => $member->pivot->id]
                    ),
                    __('deny'),
                    'btn-sm'
                ),
            ]);
            $members_unconfirmed[] = $member;
        } elseif ($member->pivot->supporter) {
            if ($admin_angeltypes) {
                $member['actions'] = table_buttons([
                    button(
                        page_link_to('user_angeltypes', [
                            'action'            => 'update',
                            'user_angeltype_id' => $member->pivot->id,
                            'supporter'         => 0,
                        ]),
                        icon('person-fill-down') . __('Remove supporter rights'),
                        'btn-sm'
                    ),
                ]);
            } else {
                $member['actions'] = '';
            }
            $supporters[] = $member;
        } else {
            if ($admin_user_angeltypes) {
                $member['actions'] = table_buttons([
                    $admin_angeltypes ?
                        button(
                            page_link_to('user_angeltypes', [
                                'action'            => 'update',
                                'user_angeltype_id' => $member->pivot->id,
                                'supporter'         => 1,
                            ]),
                            icon('person-fill-up') . __('Add supporter rights'),
                            'btn-sm'
                        ) :
                        '',
                    button(
                        page_link_to('user_angeltypes', [
                            'action'            => 'delete',
                            'user_angeltype_id' => $member->pivot->id,
                        ]),
                        icon('trash') . __('remove'),
                        'btn-sm'
                    ),
                ]);
            }
            $members_confirmed[] = $member;
        }
    }

    return [
        $supporters,
        $members_confirmed,
        $members_unconfirmed,
    ];
}

/**
 * Creates the needed member table headers according to given rights and settings from the angeltype.
 *
 * @param AngelType $angeltype
 * @param bool      $supporter
 * @param bool      $admin_angeltypes
 * @return array
 */
function AngelType_view_table_headers(AngelType $angeltype, $supporter, $admin_angeltypes)
{
    $headers = [
        'name'    => __('Nick'),
    ];

    if (config('enable_dect')) {
        $headers['dect'] = __('DECT');
    }

    if ($angeltype->requires_driver_license && ($supporter || $admin_angeltypes)) {
        $headers = array_merge($headers, [
            'wants_to_drive'               => __('Driver'),
            'has_car'                      => __('Has car'),
            'has_license_car'              => __('Car'),
            'has_license_3_5t_transporter' => __('3,5t Transporter'),
            'has_license_7_5t_truck'       => __('7,5t Truck'),
            'has_license_12t_truck'        => __('12t Truck'),
            'has_license_forklift'         => __('Forklift'),
        ]);
    }

    if (config('ifsg_enabled') && $angeltype->requires_ifsg_certificate && ($supporter || $admin_angeltypes)) {
        if (config('ifsg_light_enabled')) {
            $headers['ifsg_certificate_light'] = __('ifsg.certificate_light');
        }
        $headers['ifsg_certificate'] = __('ifsg.certificate');
    }

    $headers['actions'] = '';

    return $headers;
}

/**
 * Render an angeltype page containing the member lists.
 *
 * @param AngelType             $angeltype
 * @param User[]                $members
 * @param UserAngelType|null    $user_angeltype
 * @param bool                  $admin_user_angeltypes
 * @param bool                  $admin_angeltypes
 * @param bool                  $supporter
 * @param License               $user_driver_license
 * @param User                  $user
 * @param ShiftsFilterRenderer  $shiftsFilterRenderer
 * @param ShiftCalendarRenderer $shiftCalendarRenderer
 * @param int                   $tab The selected tab
 * @return string
 */
function AngelType_view(
    AngelType $angeltype,
    $members,
    ?UserAngelType $user_angeltype,
    $admin_user_angeltypes,
    $admin_angeltypes,
    $supporter,
    $user_driver_license,
    $user,
    ShiftsFilterRenderer $shiftsFilterRenderer,
    ShiftCalendarRenderer $shiftCalendarRenderer,
    $tab
) {
    return page_with_title(sprintf(__('Team %s'), htmlspecialchars($angeltype->name)), [
        AngelType_view_buttons($angeltype, $user_angeltype, $admin_angeltypes, $supporter, $user_driver_license, $user),
        msg(),
        tabs([
            __('Info')   => AngelType_view_info(
                $angeltype,
                $members,
                $admin_user_angeltypes,
                $admin_angeltypes,
                $supporter
            ),
            __('Shifts') => AngelType_view_shifts(
                $angeltype,
                $shiftsFilterRenderer,
                $shiftCalendarRenderer
            ),
        ], $tab),
    ], true);
}

/**
 * @param AngelType             $angeltype
 * @param ShiftsFilterRenderer  $shiftsFilterRenderer
 * @param ShiftCalendarRenderer $shiftCalendarRenderer
 * @return string HTML
 */
function AngelType_view_shifts(AngelType $angeltype, $shiftsFilterRenderer, $shiftCalendarRenderer)
{
    $shifts = $shiftsFilterRenderer->render(page_link_to('angeltypes', [
        'action'       => 'view',
        'angeltype_id' => $angeltype->id,
    ]), ['type' => $angeltype->id]);
    $shifts .= $shiftCalendarRenderer->render();

    return div('first', $shifts);
}

/**
 * @param AngelType $angeltype
 * @param User[]    $members
 * @param bool      $admin_user_angeltypes
 * @param bool      $admin_angeltypes
 * @param bool      $supporter
 * @return string HTML
 */
function AngelType_view_info(
    AngelType $angeltype,
    $members,
    $admin_user_angeltypes,
    $admin_angeltypes,
    $supporter
) {
    $info = [];
    if ($angeltype->hasContactInfo()) {
        $info[] = AngelTypes_render_contact_info($angeltype);
    }

    $info[] = '<h3>' . __('Description') . '</h3>';
    $parsedown = new Parsedown();
    if ($angeltype->description != '') {
        $info[] = $parsedown->parse(htmlspecialchars($angeltype->description));
    }

    list($supporters, $members_confirmed, $members_unconfirmed) = AngelType_view_members(
        $angeltype,
        $members,
        $admin_user_angeltypes,
        $admin_angeltypes
    );
    $table_headers = AngelType_view_table_headers($angeltype, $supporter, $admin_angeltypes);

    if (count($supporters) > 0) {
        $info[] = '<h3>' . __('Supporters') . '</h3>';
        $info[] = table($table_headers, $supporters);
    }

    if (count($members_confirmed) > 0) {
        $members_confirmed[] = [
            'name'    => __('Sum'),
            'dect'    => count($members_confirmed),
            'actions' => '',
        ];
    }

    if (count($members_unconfirmed) > 0) {
        $members_unconfirmed[] = [
            'name'    => __('Sum'),
            'dect'    => count($members_unconfirmed),
            'actions' => '',
        ];
    }

    $info[] = '<h3>' . __('Members') . '</h3>';
    if ($admin_user_angeltypes) {
        $info[] = buttons([
            button(
                page_link_to(
                    'user_angeltypes',
                    ['action' => 'add', 'angeltype_id' => $angeltype->id]
                ),
                __('Add'),
                'add'
            ),
        ]);
    }
    $info[] = table($table_headers, $members_confirmed);

    if ($admin_user_angeltypes && $angeltype->restricted && count($members_unconfirmed) > 0) {
        $info[] = '<h3>' . __('Unconfirmed') . '</h3>';
        $info[] = buttons([
            button(
                page_link_to('user_angeltypes', ['action' => 'confirm_all', 'angeltype_id' => $angeltype->id]),
                icon('check-lg') . __('confirm all')
            ),
            button(
                page_link_to('user_angeltypes', ['action' => 'delete_all', 'angeltype_id' => $angeltype->id]),
                icon('trash') . __('deny all')
            ),
        ]);
        $info[] = table($table_headers, $members_unconfirmed);
    }

    return join($info);
}

/**
 * Renders the contact info
 *
 * @param AngelType $angeltype
 * @return string HTML
 */
function AngelTypes_render_contact_info(AngelType $angeltype)
{
    $info = [
        __('Name')  => [
            htmlspecialchars($angeltype->contact_name),
            htmlspecialchars($angeltype->contact_name),
        ],
        __('DECT')  => config('enable_dect')
            ? [
                sprintf('<a href="tel:%s">%1$s</a>', htmlspecialchars($angeltype->contact_dect)),
                htmlspecialchars($angeltype->contact_dect),
            ]
            : null,
        __('E-Mail') => [
            sprintf('<a href="mailto:%s">%1$s</a>', htmlspecialchars($angeltype->contact_email)),
            htmlspecialchars($angeltype->contact_email),
        ],
    ];
    $contactInfo = [];
    foreach ($info as $name => $data) {
        if (!empty($data[1])) {
            $contactInfo[$name] = $data[0];
        }
    }

    return heading(__('Contact'), 3) . description($contactInfo);
}

/**
 * Display the list of angeltypes.
 *
 * @param AngelType[]|Collection $angeltypes
 * @param bool                   $admin_angeltypes
 * @return string
 */
function AngelTypes_list_view($angeltypes, bool $admin_angeltypes)
{
    return page_with_title(angeltypes_title(), [
        msg(),
        buttons([
            $admin_angeltypes
                ? button(page_link_to('angeltypes', ['action' => 'edit']), __('New angeltype'), 'add')
                : '',
            button(url('/angeltypes/about'), __('angeltypes.about')),
        ]),
        table([
            'name'                   => __('Name'),
            'is_restricted'          => icon('mortarboard-fill') . __('Requires introduction'),
            'no_self_signup_allowed' => icon('pencil-square') . __('Self Sign Up Allowed'),
            'membership'             => __('Membership'),
            'actions'                => '',
        ], $angeltypes),
    ], true);
}
