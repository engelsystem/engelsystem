<?php

use Engelsystem\Database\DB;
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

    $news = DB::select(sprintf('
        SELECT *
        FROM `News`
        WHERE `Treffen`=1
        ORDER BY `Datum`DESC
        LIMIT %u, %u',
        $page * $display_news,
        $display_news
    ));
    foreach ($news as $entry) {
        $html .= display_news($entry);
    }

    $dis_rows = ceil(count(DB::select('SELECT `ID` FROM `News`')) / $display_news);
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
 * @param array $news
 * @return string HTML
 */
function news_text($news)
{
    $text = ReplaceSmilies($news['Text']);
    $text = preg_replace("/\r\n\r\n/m", '<br><br>', $text);
    return $text;
}

/**
 * @param array $news
 * @return string
 */
function display_news($news)
{
    global $privileges, $page;

    $html = '';
    $html .= '<div class="panel' . ($news['Treffen'] == 1 ? ' panel-info' : ' panel-default') . '">';
    $html .= '<div class="panel-heading">';
    $html .= '<h3 class="panel-title">' . ($news['Treffen'] == 1 ? '[Meeting] ' : '') . ReplaceSmilies($news['Betreff']) . '</h3>';
    $html .= '</div>';
    $html .= '<div class="panel-body">' . news_text($news) . '</div>';

    $html .= '<div class="panel-footer text-muted">';
    if (in_array('admin_news', $privileges)) {
        $html .= '<div class="pull-right">'
            . button_glyph(
                page_link_to('admin_news', ['action' => 'edit', 'id' => $news['ID']]),
                'edit',
                'btn-xs'
            )
            . '</div>';
    }
    $html .= '<span class="glyphicon glyphicon-time"></span> ' . date('Y-m-d H:i', $news['Datum']) . '&emsp;';

    $html .= User_Nick_render(User::find($news['UID']));
    if ($page != 'news_comments') {
        $html .= '&emsp;<a href="' . page_link_to('news_comments', ['nid' => $news['ID']]) . '">'
            . '<span class="glyphicon glyphicon-comment"></span> '
            . __('Comments') . ' &raquo;</a> '
            . '<span class="badge">'
            . count(DB::select('SELECT `ID` FROM `NewsComments` WHERE `Refid`=?', [$news['ID']]))
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
    if (
        $request->has('nid')
        && preg_match('/^\d{1,}$/', $request->input('nid'))
        && count(DB::select('SELECT `ID` FROM `News` WHERE `ID`=? LIMIT 1', [$request->input('nid')])) > 0
    ) {
        $nid = $request->input('nid');
        $news = DB::selectOne('SELECT * FROM `News` WHERE `ID`=? LIMIT 1', [$nid]);
        if ($request->hasPostData('submit') && $request->has('text')) {
            $text = preg_replace(
                "/([^\p{L}\p{P}\p{Z}\p{N}\n]{1,})/ui",
                '',
                strip_tags($request->input('text'))
            );
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
    global $privileges;
    $user = auth()->user();
    $display_news = config('display_news');
    $request = request();

    $html = '<div class="col-md-12"><h1>' . news_title() . '</h1>' . msg();

    $isMeeting = $request->postData('treffen');
    if ($request->has('text') && $request->has('betreff') && in_array('admin_news', $privileges)) {
        if (!$request->has('treffen')) {
            $isMeeting = 0;
        }

        $text = $request->postData('text');
        if (!in_array('admin_news_html', $privileges)) {
            $text = strip_tags($text);
        }

        DB::insert('
            INSERT INTO `News` (`Datum`, `Betreff`, `Text`, `UID`, `Treffen`)
            VALUES (?, ?, ?, ?, ?)
            ',
            [
                time(),
                strip_tags($request->postData('betreff')),
                $text,
                $user->id,
                $isMeeting,
            ]
        );
        engelsystem_log('Created news: ' . $request->postData('betreff') . ', treffen: ' . $isMeeting);
        success(__('Entry saved.'));
        redirect(page_link_to('news'));
    }

    if (preg_match('/^\d{1,}$/', $request->input('page', 0))) {
        $page = $request->input('page', 0);
    } else {
        $page = 0;
    }

    $news = DB::select(sprintf('
            SELECT *
            FROM `News`
            ORDER BY `Datum`
            DESC LIMIT %u, %u
        ',
        $page * $display_news,
        $display_news
    ));
    foreach ($news as $entry) {
        $html .= display_news($entry);
    }

    $dis_rows = ceil(count(DB::select('SELECT `ID` FROM `News`')) / $display_news);
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

    if (in_array('admin_news', $privileges)) {
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
