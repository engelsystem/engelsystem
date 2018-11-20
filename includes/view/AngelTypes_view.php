<?php

use Engelsystem\Models\User\User;
use Engelsystem\ShiftCalendarRenderer;
use Engelsystem\ShiftsFilterRenderer;

/**
 * AngelTypes
 */

/**
 * Renders the angeltypes name as link.
 *
 * @param array $angeltype
 * @return string
 */
function AngelType_name_render($angeltype)
{
    return '<a href="' . angeltype_link($angeltype['id']) . '">'
        . ($angeltype['restricted'] ? glyph('lock') : '') . $angeltype['name']
        . '</a>';
}

/**
 * Render angeltype membership state
 *
 * @param array $user_angeltype UserAngelType and AngelType
 * @return string
 */
function AngelType_render_membership($user_angeltype)
{
    if (!empty($user_angeltype['user_angeltype_id'])) {
        if ($user_angeltype['restricted']) {
            if (empty($user_angeltype['confirm_user_id'])) {
                return glyph('lock') . __('Unconfirmed');
            } elseif ($user_angeltype['supporter']) {
                return glyph_bool(true) . __('Supporter');
            }
            return glyph_bool(true) . __('Member');
        } elseif ($user_angeltype['supporter']) {
            return glyph_bool(true) . __('Supporter');
        }
        return glyph_bool(true) . __('Member');
    }
    return glyph_bool(false);
}

/**
 * @param array $angeltype
 * @return string
 */
function AngelType_delete_view($angeltype)
{
    return page_with_title(sprintf(__('Delete angeltype %s'), $angeltype['name']), [
        info(sprintf(__('Do you want to delete angeltype %s?'), $angeltype['name']), true),
        form([
            buttons([
                button(page_link_to('angeltypes'), glyph('remove') . __('cancel')),
                form_submit('delete', glyph('ok') . __('delete'), 'btn-danger', false),
            ])
        ]),
    ]);
}

/**
 * Render angeltype edit form.
 *
 * @param array   $angeltype      The angeltype to edit
 * @param boolean $supporter_mode Is the user a supporter of this angeltype?
 * @return string
 */
function AngelType_edit_view($angeltype, $supporter_mode)
{
    return page_with_title(sprintf(__('Edit %s'), $angeltype['name']), [
        buttons([
            button(page_link_to('angeltypes'), __('Angeltypes'), 'back')
        ]),
        msg(),
        form([
            $supporter_mode
                ? form_info(__('Name'), $angeltype['name'])
                : form_text('name', __('Name'), $angeltype['name']),
            $supporter_mode
                ? form_info(__('Restricted'), $angeltype['restricted'] ? __('Yes') : __('No'))
                : form_checkbox('restricted', __('Restricted'), $angeltype['restricted']),
            form_info(
                '',
                __('Restricted angel types can only be used by an angel if enabled by a supporter (double opt-in).')
            ),
            $supporter_mode
                ? form_info(__('No Self Sign Up'), $angeltype['no_self_signup'] ? __('Yes') : __('No'))
                : form_checkbox('no_self_signup', __('No Self Sign Up'), $angeltype['no_self_signup']),
            $supporter_mode
                ? form_info(__('Requires driver license'),
                $angeltype['requires_driver_license']
                    ? __('Yes')
                    : __('No'))
                : form_checkbox(
                'requires_driver_license',
                __('Requires driver license'),
                $angeltype['requires_driver_license']),
            $supporter_mode
                ? form_info(__('Show on dashboard'), $angeltype['show_on_dashboard'] ? __('Yes') : __('No'))
                : form_checkbox('show_on_dashboard', __('Show on dashboard'), $angeltype['show_on_dashboard']),
            form_textarea('description', __('Description'), $angeltype['description']),
            form_info('', __('Please use markdown for the description.')),
            heading(__('Contact'), 3),
            form_info(
                '',
                __('Primary contact person/desk for user questions.')
            ),
            form_text('contact_name', __('Name'), $angeltype['contact_name']),
            form_text('contact_dect', __('DECT'), $angeltype['contact_dect']),
            form_text('contact_email', __('E-Mail'), $angeltype['contact_email']),
            form_submit('submit', __('Save'))
        ])
    ]);
}

/**
 * Renders the buttons for the angeltype view.
 *
 * @param array      $angeltype
 * @param array|null $user_angeltype
 * @param bool       $admin_angeltypes
 * @param bool       $supporter
 * @param array|null $user_driver_license
 * @param User|null  $user
 * @return string
 */
function AngelType_view_buttons($angeltype, $user_angeltype, $admin_angeltypes, $supporter, $user_driver_license, $user)
{
    $buttons = [
        button(page_link_to('angeltypes'), __('Angeltypes'), 'back')
    ];

    if ($angeltype['requires_driver_license']) {
        $buttons[] = button(
            user_driver_license_edit_link($user),
            glyph('road') . __('my driving license')
        );
    }

    if (is_null($user_angeltype)) {
        $buttons[] = button(
            page_link_to('user_angeltypes', ['action' => 'add', 'angeltype_id' => $angeltype['id']]),
            __('join'),
            'add'
        );
    } else {
        if ($angeltype['requires_driver_license'] && empty($user_driver_license)) {
            error(__('This angeltype requires a driver license. Please enter your driver license information!'));
        }

        if ($angeltype['restricted'] && empty($user_angeltype['confirm_user_id'])) {
            error(sprintf(
                __('You are unconfirmed for this angeltype. Please go to the introduction for %s to get confirmed.'),
                $angeltype['name']
            ));
        }
        $buttons[] = button(
            page_link_to('user_angeltypes', ['action' => 'delete', 'user_angeltype_id' => $user_angeltype['id']]),
            __('leave')
        );
    }

    if ($admin_angeltypes || $supporter) {
        $buttons[] = button(
            page_link_to('angeltypes', ['action' => 'edit', 'angeltype_id' => $angeltype['id']]),
            __('edit'),
            'edit'
        );
    }
    if ($admin_angeltypes) {
        $buttons[] = button(
            page_link_to('angeltypes', ['action' => 'delete', 'angeltype_id' => $angeltype['id']]),
            __('delete'),
            'delete'
        );
    }

    return buttons($buttons);
}

/**
 * Renders and sorts the members of an angeltype into supporters, members and unconfirmed members.
 *
 * @param array  $angeltype
 * @param User[] $members
 * @param bool   $admin_user_angeltypes
 * @param bool   $admin_angeltypes
 * @return array [supporters, members, unconfirmed members]
 */
function AngelType_view_members($angeltype, $members, $admin_user_angeltypes, $admin_angeltypes)
{
    $supporters = [];
    $members_confirmed = [];
    $members_unconfirmed = [];
    foreach ($members as $member) {
        $member->name = User_Nick_render($member);
        $member['dect'] = $member->contact->dect;
        if ($angeltype['requires_driver_license']) {
            $member['wants_to_drive'] = glyph_bool($member['wants_to_drive']);
            $member['has_car'] = glyph_bool($member['has_car']);
            $member['has_license_car'] = glyph_bool($member['has_license_car']);
            $member['has_license_3_5t_transporter'] = glyph_bool($member['has_license_3_5t_transporter']);
            $member['has_license_7_5t_truck'] = glyph_bool($member['has_license_7_5t_truck']);
            $member['has_license_12_5t_truck'] = glyph_bool($member['has_license_12_5t_truck']);
            $member['has_license_forklift'] = glyph_bool($member['has_license_forklift']);
        }

        if ($angeltype['restricted'] && empty($member['confirm_user_id'])) {
            $member['actions'] = table_buttons([
                button(
                    page_link_to(
                        'user_angeltypes',
                        ['action' => 'confirm', 'user_angeltype_id' => $member['user_angeltype_id']]
                    ),
                    __('confirm'),
                    'btn-xs'
                ),
                button(
                    page_link_to(
                        'user_angeltypes',
                        ['action' => 'delete', 'user_angeltype_id' => $member['user_angeltype_id']]
                    ),
                    __('deny'),
                    'btn-xs'
                )
            ]);
            $members_unconfirmed[] = $member;
        } elseif ($member['supporter']) {
            if ($admin_angeltypes) {
                $member['actions'] = table_buttons([
                    button(
                        page_link_to('user_angeltypes', [
                            'action'            => 'update',
                            'user_angeltype_id' => $member['user_angeltype_id'],
                            'supporter'         => 0
                        ]),
                        __('Remove supporter rights'),
                        'btn-xs'
                    )
                ]);
            } else {
                $member['actions'] = '';
            }
            $supporters[] = $member;
        } else {
            if ($admin_user_angeltypes) {
                $member['actions'] = table_buttons([
                    $admin_angeltypes
                        ? button(page_link_to('user_angeltypes', [
                        'action'            => 'update',
                        'user_angeltype_id' => $member['user_angeltype_id'],
                        'supporter'         => 1
                    ]),
                        __('Add supporter rights'), 'btn-xs')
                        : '',
                    button(
                        page_link_to('user_angeltypes', [
                            'action'            => 'delete',
                            'user_angeltype_id' => $member['user_angeltype_id']
                        ]),
                        __('remove'),
                        'btn-xs'
                    )
                ]);
            }
            $members_confirmed[] = $member;
        }
    }

    return [
        $supporters,
        $members_confirmed,
        $members_unconfirmed
    ];
}

/**
 * Creates the needed member table headers according to given rights and settings from the angeltype.
 *
 * @param array $angeltype
 * @param bool  $supporter
 * @param bool  $admin_angeltypes
 * @return array
 */
function AngelType_view_table_headers($angeltype, $supporter, $admin_angeltypes)
{
    if ($angeltype['requires_driver_license'] && ($supporter || $admin_angeltypes)) {
        return [
            'name'                         => __('Nick'),
            'dect'                         => __('DECT'),
            'wants_to_drive'               => __('Driver'),
            'has_car'                      => __('Has car'),
            'has_license_car'              => __('Car'),
            'has_license_3_5t_transporter' => __('3,5t Transporter'),
            'has_license_7_5t_truck'       => __('7,5t Truck'),
            'has_license_12_5t_truck'      => __('12,5t Truck'),
            'has_license_forklift'         => __('Forklift'),
            'actions'                      => ''
        ];
    }
    return [
        'name'    => __('Nick'),
        'dect'    => __('DECT'),
        'actions' => ''
    ];
}

/**
 * Render an angeltype page containing the member lists.
 *
 * @param array                 $angeltype
 * @param User[]                $members
 * @param array                 $user_angeltype
 * @param bool                  $admin_user_angeltypes
 * @param bool                  $admin_angeltypes
 * @param bool                  $supporter
 * @param array                 $user_driver_license
 * @param User                  $user
 * @param ShiftsFilterRenderer  $shiftsFilterRenderer
 * @param ShiftCalendarRenderer $shiftCalendarRenderer
 * @param int                   $tab The selected tab
 * @return string
 */
function AngelType_view(
    $angeltype,
    $members,
    $user_angeltype,
    $admin_user_angeltypes,
    $admin_angeltypes,
    $supporter,
    $user_driver_license,
    $user,
    ShiftsFilterRenderer $shiftsFilterRenderer,
    ShiftCalendarRenderer $shiftCalendarRenderer,
    $tab
) {
    return page_with_title(sprintf(__('Team %s'), $angeltype['name']), [
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
            )
        ], $tab)
    ]);
}

/**
 * @param array                 $angeltype
 * @param ShiftsFilterRenderer  $shiftsFilterRenderer
 * @param ShiftCalendarRenderer $shiftCalendarRenderer
 * @return string HTML
 */
function AngelType_view_shifts($angeltype, $shiftsFilterRenderer, $shiftCalendarRenderer)
{
    $shifts = $shiftsFilterRenderer->render(page_link_to('angeltypes', [
        'action'       => 'view',
        'angeltype_id' => $angeltype['id']
    ]));
    $shifts .= $shiftCalendarRenderer->render();

    return div('first', $shifts);
}

/**
 * @param array  $angeltype
 * @param User[] $members
 * @param bool   $admin_user_angeltypes
 * @param bool   $admin_angeltypes
 * @param bool   $supporter
 * @return string HTML
 */
function AngelType_view_info(
    $angeltype,
    $members,
    $admin_user_angeltypes,
    $admin_angeltypes,
    $supporter
) {
    $info = [];
    if (AngelType_has_contact_info($angeltype)) {
        $info[] = AngelTypes_render_contact_info($angeltype);
    }

    $info[] = '<h3>' . __('Description') . '</h3>';
    $parsedown = new Parsedown();
    if ($angeltype['description'] != '') {
        $info[] = '<div class="well">' . $parsedown->parse($angeltype['description']) . '</div>';
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
            'actions' => ''
        ];
    }

    if (count($members_unconfirmed) > 0) {
        $members_unconfirmed[] = [
            'name'    => __('Sum'),
            'dect'    => count($members_unconfirmed),
            'actions' => ''
        ];
    }

    $info[] = '<h3>' . __('Members') . '</h3>';
    if ($admin_user_angeltypes) {
        $info[] = buttons([
            button(
                page_link_to(
                    'user_angeltypes',
                    ['action' => 'add', 'angeltype_id' => $angeltype['id']]
                ),
                __('Add'),
                'add'
            )
        ]);
    }
    $info[] = table($table_headers, $members_confirmed);

    if ($admin_user_angeltypes && $angeltype['restricted'] && count($members_unconfirmed) > 0) {
        $info[] = '<h3>' . __('Unconfirmed') . '</h3>';
        $info[] = buttons([
            button(
                page_link_to('user_angeltypes', ['action' => 'confirm_all', 'angeltype_id' => $angeltype['id']]),
                glyph('ok') . __('confirm all')
            ),
            button(
                page_link_to('user_angeltypes', ['action' => 'delete_all', 'angeltype_id' => $angeltype['id']]),
                glyph('remove') . __('deny all')
            )
        ]);
        $info[] = table($table_headers, $members_unconfirmed);
    }

    return join($info);
}

/**
 * Renders the contact info
 *
 * @param array $angeltype
 * @return string HTML
 */
function AngelTypes_render_contact_info($angeltype)
{
    return heading(__('Contact'), 3) . description([
            __('Name')   => $angeltype['contact_name'],
            __('DECT')   => $angeltype['contact_dect'],
            __('E-Mail') => $angeltype['contact_email']
        ]);
}

/**
 * Display the list of angeltypes.
 *
 * @param array $angeltypes
 * @param bool  $admin_angeltypes
 * @return string
 */
function AngelTypes_list_view($angeltypes, $admin_angeltypes)
{
    return page_with_title(angeltypes_title(), [
        msg(),
        buttons([
            $admin_angeltypes
                ? button(page_link_to('angeltypes', ['action' => 'edit']), __('New angeltype'), 'add')
                : '',
            button(page_link_to('angeltypes', ['action' => 'about']), __('Teams/Job description'))
        ]),
        table([
            'name'           => __('Name'),
            'restricted'     => glyph('lock') . __('Restricted'),
            'no_self_signup' => glyph('share') . __('Self Sign Up Allowed'),
            'membership'     => __('Membership'),
            'actions'        => ''
        ], $angeltypes)
    ]);
}

/**
 * Renders the about info for an angeltype.
 *
 * @param array $angeltype
 * @return string
 */
function AngelTypes_about_view_angeltype($angeltype)
{
    $parsedown = new Parsedown();

    $html = '<h2>' . $angeltype['name'] . '</h2>';

    if (AngelType_has_contact_info($angeltype)) {
        $html .= AngelTypes_render_contact_info($angeltype);
    }

    if (isset($angeltype['user_angeltype_id'])) {
        $buttons = [];
        if (!empty($angeltype['user_angeltype_id'])) {
            $buttons[] = button(
                page_link_to(
                    'user_angeltypes',
                    ['action' => 'delete', 'user_angeltype_id' => $angeltype['user_angeltype_id']]
                ),
                __('leave')
            );
        } else {
            $buttons[] = button(
                page_link_to('user_angeltypes', ['action' => 'add', 'angeltype_id' => $angeltype['id']]),
                __('join'),
                'add'
            );
        }
        $html .= buttons($buttons);
    }

    if ($angeltype['restricted']) {
        $html .= info(
            __('This angeltype is restricted by double-opt-in by a team supporter. Please show up at the according introduction meetings.'),
            true
        );
    }
    if ($angeltype['description'] != '') {
        $html .= '<div class="well">' . $parsedown->parse($angeltype['description']) . '</div>';
    }
    $html .= '<hr />';

    return $html;
}

/**
 * Renders a site that contains every angeltype and its description, basically as an overview of the needed help types.
 *
 * @param array[] $angeltypes
 * @param bool    $user_logged_in
 * @return string
 */
function AngelTypes_about_view($angeltypes, $user_logged_in)
{
    global $privileges;

    $buttons = [];

    if ($user_logged_in) {
        $buttons[] = button(page_link_to('angeltypes'), angeltypes_title(), 'back');
    } else {
        if (in_array('register', $privileges) && config('registration_enabled')) {
            $buttons[] = button(page_link_to('register'), register_title());
        }

        $buttons[] = button(page_link_to('login'), login_title());
    }

    $faqUrl = config('faq_url');
    if (!empty($faqUrl)) {
        $buttons[] = button($faqUrl, __('FAQ'), 'btn-primary');
    }

    $content = [
        buttons($buttons),
        '<p>' . __('Here is the list of teams and their tasks. If you have questions, read the FAQ.') . '</p>',
        '<hr />'
    ];
    foreach ($angeltypes as $angeltype) {
        $content[] = AngelTypes_about_view_angeltype($angeltype);
    }

    return page_with_title(__('Teams/Job description'), $content);
}
