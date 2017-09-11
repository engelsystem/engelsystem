<?php

use Engelsystem\Database\DB;

/**
 * @return string
 */
function admin_news()
{
    global $user, $privileges;
    $request = request();

    if (!$request->has('action')) {
        redirect(page_link_to('news'));
    }

    $html = '<div class="col-md-12"><h1>' . _('Edit news entry') . '</h1>' . msg();
    if ($request->has('id') && preg_match('/^\d{1,11}$/', $request->input('id'))) {
        $news_id = $request->input('id');
    } else {
        return error('Incomplete call, missing News ID.', true);
    }

    $news = DB::selectOne('SELECT * FROM `News` WHERE `ID`=? LIMIT 1', [$news_id]);
    if (empty($news)) {
        return error('No News found.', true);
    }

    switch ($request->input('action')) {
        case 'edit':
            $user_source = User($news['UID']);

            $html .= form(
                [
                    form_info(_('Date'), date('Y-m-d H:i', $news['Datum'])),
                    form_info(_('Author'), User_Nick_render($user_source)),
                    form_text('eBetreff', _('Subject'), $news['Betreff']),
                    form_textarea('eText', _('Message'), $news['Text']),
                    form_checkbox('eTreffen', _('Meeting'), $news['Treffen'] == 1, 1),
                    form_submit('submit', _('Save'))
                ],
                page_link_to('admin_news', ['action' => 'save', 'id' => $news_id])
            );

            $html .= '<a class="btn btn-danger" href="'
                . page_link_to('admin_news', ['action' => 'delete', 'id' => $news_id])
                . '">'
                . '<span class="glyphicon glyphicon-trash"></span> ' . _('Delete')
                . '</a>';
            break;

        case 'save':
            $text = $request->postData('eText');
            if (!in_array('admin_news_html', $privileges)) {
                $text = strip_tags($text);
            }

            DB::update('
                UPDATE `News` SET
                    `Datum`=?,
                    `Betreff`=?,
                    `Text`=?,
                    `UID`=?,
                    `Treffen`=?
                WHERE `ID`=?
                ',
                [
                    time(),
                    strip_tags($request->postData('eBetreff')),
                    $text,
                    $user['UID'],
                    $request->has('eTreffen') ? 1 : 0,
                    $news_id
                ]
            );

            engelsystem_log('News updated: ' . $request->postData('eBetreff'));
            success(_('News entry updated.'));
            redirect(page_link_to('news'));
            break;

        case 'delete':
            DB::delete('DELETE FROM `News` WHERE `ID`=? LIMIT 1', [$news_id]);
            engelsystem_log('News deleted: ' . $news['Betreff']);
            success(_('News entry deleted.'));
            redirect(page_link_to('news'));
            break;
        default:
            redirect(page_link_to('news'));
    }
    return $html . '</div>';
}
