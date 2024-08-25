<?php

use Engelsystem\Models\Question;
use Engelsystem\UserHintsRenderer;
use Illuminate\Support\Str;

/**
 * Render the user hints
 *
 * @return string
 */
function header_render_hints()
{
    $user = auth()->user();

    if ($user) {
        $hints_renderer = new UserHintsRenderer();

        $hints_renderer->addHint(admin_new_questions());
        $hints_renderer->addHint(user_angeltypes_unconfirmed_hint());
        $hints_renderer->addHint(render_user_departure_date_hint());
        $hints_renderer->addHint(user_driver_license_required_hint());
        $hints_renderer->addHint(user_ifsg_certificate_required_hint());

        // Important hints:
        $hints_renderer->addHint(render_user_freeloader_hint(), true);
        $hints_renderer->addHint(render_user_arrived_hint(true), true);
        $hints_renderer->addHint(render_user_pronoun_hint(), true);
        $hints_renderer->addHint(render_user_firstname_hint(), true);
        $hints_renderer->addHint(render_user_lastname_hint(), true);
        $hints_renderer->addHint(render_user_goodie_hint(), true);
        $hints_renderer->addHint(render_user_dect_hint(), true);
        $hints_renderer->addHint(render_user_mobile_hint(), true);

        return $hints_renderer->render();
    }

    return '';
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
 * @return string
 */
function make_navigation()
{
    $page = current_page();
    $menu = [];
    $pages = [
        'news'           => __('news.title'),
        'meetings'       => [__('news.title.meetings'), 'user_meetings'],
        'user_shifts'    => __('general.shifts'),
        'angeltypes'     => __('angeltypes.angeltypes'),
        'locations'      => [__('location.locations'), 'locations.view'],
        'questions'      => [__('Ask the Heaven'), 'question.add'],
    ];

    foreach ($pages as $menu_page => $options) {
        if (!menu_is_allowed($menu_page, $options)) {
            continue;
        }

        $title = ((array) $options)[0];
        $menu[] = toolbar_item_link(
            url(str_replace('_', '-', $menu_page)),
            '',
            $title,
            $menu_page == $page
        );
    }

    $admin_menu = [];
    $admin_pages = [
        // Examples:
        // path              => name,
        // path              => [name, permission],

        'admin_arrive'       => [admin_arrive_title(), 'users.arrive.list'],
        'admin_active'       => 'Active angels',
        'users'              => ['All Angels', 'admin_user'],
        'admin_free'         => 'Free angels',
        'admin/questions'    => ['Answer questions', 'question.edit'],
        'admin/shifttypes'   => ['shifttype.shifttypes', 'shifttypes.view'],
        'admin_shifts'       => 'Create shifts',
        'admin_groups'       => 'Grouprights',
        'admin/schedule'     => ['schedule.import', 'schedule.import'],
        'admin/logs'         => ['log.log', 'admin_log'],
        'admin/config'       => ['config.config', 'config.edit'],
    ];

    if (config('autoarrive')) {
        unset($admin_pages['admin_arrive']);
    }

    foreach ($admin_pages as $menu_page => $options) {
        if (!menu_is_allowed($menu_page, $options)) {
            continue;
        }

        $title = ((array) $options)[0];
        $admin_menu[] = toolbar_dropdown_item(
            url(str_replace('_', '-', $menu_page)),
            htmlspecialchars(__($title)),
            $menu_page == $page || Str::startsWith($page, $menu_page . '/')
        );
    }

    if (count($admin_menu) > 0) {
        $menu[] = toolbar_dropdown(__('Admin'), $admin_menu);
    }

    return join("\n", $menu);
}

/**
 * @param string          $page
 * @param string|string[] $options
 *
 * @return bool
 */
function menu_is_allowed(string $page, $options)
{
    $options = (array) $options;
    $permissions = $page;

    if (isset($options[1])) {
        $permissions = $options[1];
    }

    return auth()->can($permissions);
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
        $url = url($request->getPathInfo(), [...$request->getQueryParams(), 'set-locale' => $locale]);

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

    return '<a href="' . url('/admin/questions') . '">'
        . __('There are unanswered questions!')
        . '</a>';
}
