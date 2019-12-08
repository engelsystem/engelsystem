<?php

use Engelsystem\Models\News;

/**
 * @return string
 */
function admin_news()
{
    $request = request();

    if (!$request->has('action')) {
        throw_redirect(page_link_to('news'));
    }

    $html = '<div class="col-md-12"><h1>' . __('Edit news entry') . '</h1>' . msg();
    if ($request->has('id') && preg_match('/^\d{1,11}$/', $request->input('id'))) {
        $news_id = $request->input('id');
    } else {
        return error('Incomplete call, missing News ID.', true);
    }

    $news = News::find($news_id);
    if (empty($news)) {
        return error('No News found.', true);
    }

    switch ($request->input('action')) {
        case 'edit':
            $user_source = $news->user;
            if (
                !auth()->can('admin_news_html')
                && strip_tags($news->text) != $news->text
            ) {
                $html .= warning(
                    __('This message contains HTML. After saving the post some formatting will be lost!'),
                    true
                );
            }

            $html .= form(
                [
                    form_info(__('Date'), $news->created_at->format('Y-m-d H:i')),
                    form_info(__('Author'), User_Nick_render($user_source)),
                    form_text('eBetreff', __('Subject'), $news->title),
                    form_textarea('eText', __('Message'), $news->text),
                    form_checkbox('eTreffen', __('Meeting'), $news->is_meeting, 1),
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

            $news->title = strip_tags($request->postData('eBetreff'));
            $news->text = $text;
            $news->is_meeting = $request->has('eTreffen');
            $news->save();

            engelsystem_log('News updated: ' . $request->postData('eBetreff'));
            success(__('News entry updated.'));
            throw_redirect(page_link_to('news'));
            break;

        case 'delete':
            $news->delete();
            engelsystem_log('News deleted: ' . $news->title);
            success(__('News entry deleted.'));
            throw_redirect(page_link_to('news'));
            break;
        default:
            throw_redirect(page_link_to('news'));
    }
    return $html . '</div>';
}
