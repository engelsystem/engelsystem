<?php

use Engelsystem\Database\DB;
use Engelsystem\Models\User\User;

/**
 * @return string
 */
function admin_news()
{
    $user = auth()->user();
    $request = request();

    if (!$request->has('action')) {
        redirect(page_link_to('news'));
    }

    $html = '<div class="col-md-12"><h1>' . __('Edit news entry') . '</h1>' . msg();
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
            $user_source = User::find($news['UID']);
            if (
                !auth()->can('admin_news_html')
                && strip_tags($news['Text']) != $news['Text']
            ) {
                $html .= warning(
                    __('This message contains HTML. After saving the post some formatting will be lost!'),
                    true
                );
            }

            $html .= form(
                [
                    form_info(__('Date'), date('Y-m-d H:i', $news['Datum'])),
                    form_info(__('Author'), User_Nick_render($user_source)),
                    form_text('eBetreff', __('Subject'), $news['Betreff']),
                    form_textarea('eText', __('Message'), $news['Text']),
                    form_checkbox('eTreffen', __('Meeting'), $news['Treffen'] == 1, 1),
                    form_submit('submit', __('Save'))
                ],
                page_link_to('admin_news', ['action' => 'save', 'id' => $news_id])
            );

            $html .= '<a class="btn btn-danger" href="'
                . page_link_to('admin_news', ['action' => 'delete', 'id' => $news_id])
                . '">'
                . '<span class="glyphicon glyphicon-trash"></span> ' . __('Delete')
                . '</a>';
            break;

        case 'save':
            $text = $request->postData('eText');
            if (!auth()->can('admin_news_html')) {
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
                    $user->id,
                    $request->has('eTreffen') ? 1 : 0,
                    $news_id
                ]
            );

            engelsystem_log('News updated: ' . $request->postData('eBetreff'));
            success(__('News entry updated.'));
            redirect(page_link_to('news'));
            break;

        case 'delete':
            DB::delete('DELETE FROM `News` WHERE `ID`=? LIMIT 1', [$news_id]);
            engelsystem_log('News deleted: ' . $news['Betreff']);
            success(__('News entry deleted.'));
            redirect(page_link_to('news'));
            break;
        default:
            redirect(page_link_to('news'));
    }
    return $html . '</div>';
}
