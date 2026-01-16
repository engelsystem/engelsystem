<?php

use Engelsystem\Models\Question;
use Engelsystem\UserHintsRenderer;

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

function make_navigation(): array
{
    $menu = [];
    $pages = [
        // path          => name,
        // path          => [name, permission],
        'news'           => 'news.title',
        'meetings'       => ['news.title.meetings', 'user_meetings'],
        'user_shifts'    => 'general.shifts',
        'angeltypes'     => 'angeltypes.angeltypes',
        'locations'      => ['location.locations', 'locations.view'],
        'questions'      => ['question.menu', 'question.add'],
    ];

    foreach ($pages as $menu_page => $options) {
        $options = (array) $options;
        $menu[$options[0]] = [
            url(str_replace('_', '-', $menu_page)),
            $options[1] ?? $menu_page,
        ];
    }

    foreach (config('header_items', []) as $title => $options) {
        $menu[$title] = $options;
    }

    $admin_pages = [
        // path              => name,
        // path              => [name, permission],
        'admin_arrive'       => [admin_arrive_title(), 'users.arrive.list'],
        'admin_active'       => 'Active angels',
        'users'              => ['All Angels', 'admin_user'],
        'admin_free'         => 'Free angels',
        'admin/questions'    => ['Answer questions', 'question.edit'],
        'admin/shifttypes'   => ['shifttype.shifttypes', 'shifttypes.view'],
        'admin_shifts'       => 'Create shifts',
        'admin_groups'       => 'Group rights',
        'admin/schedule'     => ['schedule.import', 'schedule.import'],
        'admin/tags'         => ['tag.tags', 'tag.edit'],
        'admin/logs'         => ['log.log', 'admin_log'],
        'admin/minors'       => ['minors.management', 'admin_arrive'],
        'admin/config'       => ['config.config', 'config.edit'],
    ];

    if (config('autoarrive')) {
        unset($admin_pages['admin_arrive']);
    }

    $admin_menu = [];
    foreach ($admin_pages as $menu_page => $options) {
        $options = (array) $options;
        if (!auth()->can($options[1] ?? $menu_page)) {
            continue;
        }

        $admin_menu[$options[0]] = [
            url(str_replace('_', '-', $menu_page)),
            $options[1] ?? $menu_page,
        ];
    }

    $menu['Admin'] = [$admin_menu, $admin_menu ? null : 'hide', true];

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
    foreach (config('locales') as $locale) {
        $url = url($request->getPathInfo(), [...$request->getQueryParams(), 'set-locale' => $locale]);
        $name = __('language.' . $locale);

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
    $currentPage = request()->query->get('p') ?: str_replace('-', '_', request()->path());
    if (!auth()->can('question.edit') || $currentPage == 'admin/questions') {
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
