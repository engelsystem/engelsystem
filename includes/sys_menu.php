<?php

use Engelsystem\Models\Question;
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
 * Returns the path of the current path with underscores instead of hyphens
 *
 * @return string
 */
function current_page()
{
    return request()->query->get('p') ?: str_replace('-', '_', request()->path());
}

/**
 * @return array
 */
function make_user_submenu()
{
    $page = current_page();
    $user_submenu = make_language_select();

    if (auth()->can('user_settings') || auth()->can('logout')) {
        $user_submenu[] = toolbar_dropdown_item_divider();
    }

    if (auth()->can('user_settings')) {
        $user_submenu[] = toolbar_dropdown_item(
            page_link_to('user_settings'),
            __('Settings'),
            $page == 'user_settings',
            'bi-gear'
        );
    }

    if (auth()->can('logout')) {
        $user_submenu[] = toolbar_dropdown_item(
            page_link_to('logout'),
            __('Logout'),
            $page == 'logout',
            'bi-box-arrow-left',
        );
    }

    return $user_submenu;
}

/**
 * @return string
 */
function make_navigation()
{
    $page = current_page();
    $menu = [];
    $pages = [
        'news'           => __('News'),
        'meetings'       => [__('Meetings'), 'user_meetings'],
        'user_shifts'    => __('Shifts'),
        'angeltypes'     => __('Angeltypes'),
        'questions'      => [__('Ask the Heaven'), 'question.add'],
    ];

    foreach ($pages as $menu_page => $options) {
        if (!menu_is_allowed($menu_page, $options)) {
            continue;
        }

        $title = ((array)$options)[0];
        $menu[] = toolbar_item_link(page_link_to($menu_page), '', $title, $menu_page == $page);
    }

    $menu = make_room_navigation($menu);

    $admin_menu = [];
    $admin_pages = [
        // path              => name
        // path              => [name, permission]
        'admin_arrive'       => 'Arrive angels',
        'admin_active'       => 'Active angels',
        'admin_user'         => 'All Angels',
        'admin_free'         => 'Free angels',
        'admin/questions'    => ['Answer questions', 'question.edit'],
        'shifttypes'         => 'Shifttypes',
        'admin_shifts'       => 'Create shifts',
        'admin_rooms'        => 'Rooms',
        'admin_groups'       => 'Grouprights',
        'admin/schedule'     => ['schedule.import', 'schedule.import'],
        'admin/logs'         => ['log.log', 'admin_log'],
        'admin_event_config' => 'Event config',
    ];

    if (config('autoarrive')) {
        unset($admin_pages['admin_arrive']);
    }

    foreach ($admin_pages as $menu_page => $options) {
        if (!menu_is_allowed($menu_page, $options)) {
            continue;
        }

        $title = ((array)$options)[0];
        $admin_menu[] = toolbar_dropdown_item(
            page_link_to($menu_page),
            __($title),
            $menu_page == $page
        );
    }

    if (count($admin_menu) > 0) {
        $menu[] = toolbar_dropdown('', __('Admin'), $admin_menu);
    }

    return '<ul class="navbar-nav mb-2 mb-lg-0">' . join("\n", $menu) . '</ul>';
}

/**
 * @param string          $page
 * @param string|string[] $options
 *
 * @return bool
 */
function menu_is_allowed(string $page, $options)
{
    $options = (array)$options;
    $permissions = $page;

    if (isset($options[1])) {
        $permissions = $options[1];
    }

    return auth()->can($permissions);
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
        $room_menu[] = toolbar_dropdown_item(page_link_to('admin_rooms'), __('Manage rooms'), false, 'list');
    }
    if (count($room_menu) > 0) {
        $room_menu[] = toolbar_dropdown_item_divider();
    }
    foreach ($rooms as $room) {
        $room_menu[] = toolbar_dropdown_item(room_link($room), $room->name, false, 'map-marker');
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

        $items[] = toolbar_dropdown_item(
            htmlspecialchars($url),
            $name,
            $locale == $activeLocale
        );
    }
    return $items;
}

/**
 * Renders a hint for new questions to answer.
 *
 * @return string|null
 */
function admin_new_questions()
{
    if (!auth()->can('question.edit') || current_page() == 'admin/questions') {
        return null;
    }

    $unanswered_questions = Question::unanswered()->count();
    if (!$unanswered_questions) {
        return null;
    }

    return '<a href="' . page_link_to('/admin/questions') . '">'
        . __('There are unanswered questions!')
        . '</a>';
}
