<?php

use Engelsystem\UserHintsRenderer;

/**
 * @param string $page
 * @param array  $parameters get parameters
 * @return string
 */
function page_link_to($page = '', $parameters = [])
{
    $page = str_replace('_', '-', $page);
    return url($page, $parameters);
}

/**
 * Render the user hints
 *
 * @return string
 */
function header_render_hints()
{
    $user = auth()->user();

    $hints_renderer = new UserHintsRenderer();

    if ($user) {
        $hints_renderer->addHint(admin_new_questions());
        $hints_renderer->addHint(user_angeltypes_unconfirmed_hint());
        $hints_renderer->addHint(render_user_departure_date_hint());
        $hints_renderer->addHint(user_driver_license_required_hint());

        // Important hints:
        $hints_renderer->addHint(render_user_freeloader_hint(), true);
        $hints_renderer->addHint(render_user_arrived_hint(), true);
        $hints_renderer->addHint(render_user_tshirt_hint(), true);
        $hints_renderer->addHint(render_user_dect_hint(), true);
    }

    return $hints_renderer->render();
}

/**
 * @return array
 */
function make_user_submenu()
{
    global $page;

    $user_submenu = make_language_select();

    if (auth()->can('user_settings') || auth()->can('logout')) {
        $user_submenu[] = toolbar_item_divider();
    }

    if (auth()->can('user_settings')) {
        $user_submenu[] = toolbar_item_link(
            page_link_to('user_settings'),
            'list-alt',
            __('Settings'),
            $page == 'user_settings'
        );
    }

    if (auth()->can('logout')) {
        $user_submenu[] = toolbar_item_link(
            page_link_to('logout'),
            'log-out',
            __('Logout'),
            $page == 'logout'
        );
    }

    return $user_submenu;
}

/**
 * @return string
 */
function make_navigation()
{
    global $page;

    $menu = [];
    $pages = [
        'news'           => __('News'),
        'user_meetings'  => __('Meetings'),
        'user_shifts'    => __('Shifts'),
        'angeltypes'     => __('Angeltypes'),
        'user_questions' => __('Ask the Heaven'),
    ];

    foreach ($pages as $menu_page => $title) {
        if (auth()->can($menu_page)) {
            $menu[] = toolbar_item_link(page_link_to($menu_page), '', $title, $menu_page == $page);
        }
    }

    $menu = make_room_navigation($menu);

    $admin_menu = [];
    $admin_pages = [
        'admin_arrive'       => __('Arrived angels'),
        'admin_active'       => __('Active angels'),
        'admin_user'         => __('All Angels'),
        'admin_free'         => __('Free angels'),
        'admin_questions'    => __('Answer questions'),
        'shifttypes'         => __('Shifttypes'),
        'admin_shifts'       => __('Create shifts'),
        'admin_rooms'        => __('Rooms'),
        'admin_groups'       => __('Grouprights'),
        'admin_import'       => __('Frab import'),
        'admin_log'          => __('Log'),
        'admin_event_config' => __('Event config'),
    ];

    if (config('autoarrive')) {
        unset($admin_pages['admin_arrive']);
    }

    foreach ($admin_pages as $menu_page => $title) {
        if (auth()->can($menu_page)) {
            $admin_menu[] = toolbar_item_link(
                page_link_to($menu_page),
                '',
                $title,
                $menu_page == $page
            );
        }
    }

    if (count($admin_menu) > 0) {
        $menu[] = toolbar_dropdown('', __('Admin'), $admin_menu);
    }

    return '<ul class="nav navbar-nav">' . join("\n", $menu) . '</ul>';
}

/**
 * Adds room navigation to the given menu.
 *
 * @param string[] $menu Rendered menu
 * @return string[]
 */
function make_room_navigation($menu)
{
    if (!auth()->can('view_rooms')) {
        return $menu;
    }

    // Get a list of all rooms
    $rooms = Rooms();
    $room_menu = [];
    if (auth()->can('admin_rooms')) {
        $room_menu[] = toolbar_item_link(page_link_to('admin_rooms'), 'list', __('Manage rooms'));
    }
    if (count($room_menu) > 0) {
        $room_menu[] = toolbar_item_divider();
    }
    foreach ($rooms as $room) {
        $room_menu[] = toolbar_item_link(room_link($room), 'map-marker', $room['Name']);
    }
    if (count($room_menu) > 0) {
        $menu[] = toolbar_dropdown('map-marker', __('Rooms'), $room_menu);
    }
    return $menu;
}

/**
 * Renders language selection.
 *
 * @return array
 */
function make_language_select()
{
    $request = app('request');
    $activeLocale = session()->get('locale');

    $items = [];
    foreach (config('locales') as $locale => $name) {
        $url = url($request->getPathInfo(), ['set-locale' => $locale]);

        $items[] = toolbar_item_link(
            htmlspecialchars($url),
            '',
            $name,
            $locale == $activeLocale
        );
    }
    return $items;
}
