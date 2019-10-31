<?php

use Engelsystem\Database\DB;
use Engelsystem\Models\News\News;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function user_news_comments_title()
{
    return __('News comments');
}

/**
 * @return string
 */
function news_title()
{
    return __('News');
}

/**
 * @return string
 */
function meetings_title()
{
    return __('Meetings');
}

/**
 * @return string
 */
function user_meetings()
{
    $display_news = config('display_news');
    $html = '<div class="col-md-12"><h1>' . meetings_title() . '</h1>' . msg();
    $request = request();

    if (preg_match('/^\d{1,}$/', $request->input('page', 0))) {
        $page = $request->input('page', 0);
    } else {
        $page = 0;
    }

    $news = News::where('is_meeting', true)
        ->orderBy('created_at', 'DESC')
        ->limit($display_news)
        ->offset($page * $display_news)
        ->get();

    foreach ($news as $entry) {
        $html .= display_news($entry);
    }

    $dis_rows = ceil(News::where('is_meeting', true)->count() / $display_news);
    $html .= '<div class="text-center">' . '<ul class="pagination">';
    for ($i = 0; $i < $dis_rows; $i++) {
        if ($request->has('page') && $i == $request->input('page', 0)) {
            $html .= '<li class="active">';
        } elseif (!$request->has('page') && $i == 0) {
            $html .= '<li class="active">';
        } else {
            $html .= '<li>';
        }
        $html .= '<a href="' . page_link_to('user_meetings', ['page' => $i]) . '">' . ($i + 1) . '</a></li>';
    }
    $html .= '</ul></div></div>';

    return $html;
}

/**
 * Renders the text content of a news entry
 *
 * @param News $news
 * @return string HTML
 */
function news_text(News $news): string
{
    $text = ReplaceSmilies($news->text);
    $text = preg_replace("/\r\n\r\n/m", '<br><br>', $text);
    return $text;
}

/**
 * @param News $news
 * @return string
 */
function display_news(News $news): string
{
    global $page;

    $html = '';
    $html .= '<div class="panel' . ($news->is_meeting ? ' panel-info' : ' panel-default') . '">';
    $html .= '<div class="panel-heading">';
    $html .= '<h3 class="panel-title">' . ($news->is_meeting ? '[Meeting] ' : '') . ReplaceSmilies($news->title) . '</h3>';
    $html .= '</div>';
    $html .= '<div class="panel-body">' . news_text($news) . '</div>';

    $html .= '<div class="panel-footer text-muted">';
    if (auth()->can('admin_news')) {
        $html .= '<div class="pull-right">'
            . button_glyph(
                page_link_to('admin_news', ['action' => 'edit', 'id' => $news->id]),
                'edit',
                'btn-xs'
            )
            . '</div>';
    }
    $html .= '<span class="glyphicon glyphicon-time"></span> ' . $news->created_at->format('Y-m-d H:i') . '&emsp;';

    $html .= User_Nick_render(User::find($news->user_id));
    if ($page != 'news_comments') {
        $html .= '&emsp;<a href="' . page_link_to('news_comments', ['nid' => $news->id]) . '">'
            . '<span class="glyphicon glyphicon-comment"></span> '
            . __('Comments') . ' &raquo;</a> '
            . '<span class="badge">'
            . count(DB::select('SELECT `ID` FROM `NewsComments` WHERE `Refid`=?', [$news->id]))
            . '</span>';
    }
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * @return string
 */
function user_news_comments()
{
    $user = auth()->user();
    $request = request();

    $html = '<div class="col-md-12"><h1>' . user_news_comments_title() . '</h1>';
    $nid = $request->input('nid');
    if (
        $request->has('nid')
        && preg_match('/^\d{1,}$/', $nid)
        && News::where('id', $request->input('nid'))->count() > 0
    ) {
        $news = News::find('id');
        if ($request->hasPostData('submit') && $request->has('text')) {
            $text = $request->input('text');
            DB::insert('
                    INSERT INTO `NewsComments` (`Refid`, `Datum`, `Text`, `UID`)
                    VALUES (?, ?, ?, ?)
                ',
                [
                    $nid,
                    date('Y-m-d H:i:s'),
                    $text,
                    $user->id,
                ]
            );

            engelsystem_log('Created news_comment: ' . $text);
            $html .= success(__('Entry saved.'), true);
        }

        $html .= display_news($news);

        $comments = DB::select(
            'SELECT * FROM `NewsComments` WHERE `Refid`=? ORDER BY \'ID\'',
            [$nid]
        );
        foreach ($comments as $comment) {
            $user_source = User::find($comment['UID']);

            $html .= '<div class="panel panel-default">';
            $html .= '<div class="panel-body">' . nl2br(htmlspecialchars($comment['Text'])) . '</div>';
            $html .= '<div class="panel-footer text-muted">';
            $html .= '<span class="glyphicon glyphicon-time"></span> ' . $comment['Datum'] . '&emsp;';
            $html .= User_Nick_render($user_source);
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '<hr /><h2>' . __('New Comment:') . '</h2>';
        $html .= form([
            form_textarea('text', __('Message'), ''),
            form_submit('submit', __('Save'))
        ], page_link_to('news_comments', ['nid' => $news['ID']]));
    } else {
        $html .= __('Invalid request.');
    }

    return $html . '</div>';
}

/**
 * @return string
 */
function user_news()
{
    $user = auth()->user();
    $display_news = config('display_news');
    $request = request();

    $html = '<div class="col-md-12"><h1>' . news_title() . '</h1>' . msg();

    $isMeeting = $request->postData('treffen');
    if ($request->has('text') && $request->has('betreff') && auth()->can('admin_news')) {
        if (!$request->has('treffen')) {
            $isMeeting = 0;
        }

        $text = $request->postData('text');
        if (!auth()->can('admin_news_html')) {
            $text = strip_tags($text);
        }

        News::create([
            'title'      => strip_tags($request->postData('betreff')),
            'text'       => $text,
            'user_id'    => $user->id,
            'is_meeting' => !!$isMeeting,
        ]);

        engelsystem_log('Created news: ' . $request->postData('betreff') . ', treffen: ' . $isMeeting);
        success(__('Entry saved.'));
        redirect(page_link_to('news'));
    }

    if (preg_match('/^\d{1,}$/', $request->input('page', 0))) {
        $page = $request->input('page', 0);
    } else {
        $page = 0;
    }

    $news = News::query()
        ->orderBy('created_at', 'DESC')
        ->limit($display_news)
        ->offset($page * $display_news)
        ->get();

    foreach ($news as $entry) {
        $html .= display_news($entry);
    }

    $dis_rows = ceil(News::query()->count() / $display_news);
    $html .= '<div class="text-center">' . '<ul class="pagination">';
    for ($i = 0; $i < $dis_rows; $i++) {
        if ($request->has('page') && $i == $request->input('page', 0)) {
            $html .= '<li class="active">';
        } elseif (!$request->has('page') && $i == 0) {
            $html .= '<li class="active">';
        } else {
            $html .= '<li>';
        }
        $html .= '<a href="' . page_link_to('news', ['page' => $i]) . '">' . ($i + 1) . '</a></li>';
    }
    $html .= '</ul></div>';

    if (auth()->can('admin_news')) {
        $html .= '<hr />';
        $html .= '<h2>' . __('Create news:') . '</h2>';

        $html .= form([
            form_text('betreff', __('Subject'), ''),
            form_textarea('text', __('Message'), ''),
            form_checkbox('treffen', __('Meeting'), false, 1),
            form_submit('submit', __('Save'))
        ]);
    }
    return $html . '</div>';
}
