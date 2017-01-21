<?php

use Engelsystem\Database\DB;

/**
 * @return string
 */
function admin_news()
{
    global $user;

    if (!isset($_GET['action'])) {
        redirect(page_link_to('news'));
    }

    $html = '<div class="col-md-12"><h1>' . _("Edit news entry") . '</h1>' . msg();
    if (isset($_REQUEST['id']) && preg_match("/^[0-9]{1,11}$/", $_REQUEST['id'])) {
        $news_id = $_REQUEST['id'];
    } else {
        return error('Incomplete call, missing News ID.', true);
    }

    $news = DB::select('SELECT * FROM `News` WHERE `ID`=? LIMIT 1', [$news_id]);
    if (empty($news)) {
        return error('No News found.', true);
    }

    switch ($_REQUEST['action']) {
        case 'edit':
            $news = array_shift($news);
            $user_source = User($news['UID']);

            $html .= form([
                form_info(_('Date'), date('Y-m-d H:i', $news['Datum'])),
                form_info(_('Author'), User_Nick_render($user_source)),
                form_text('eBetreff', _('Subject'), $news['Betreff']),
                form_textarea('eText', _('Message'), $news['Text']),
                form_checkbox('eTreffen', _('Meeting'), $news['Treffen'] == 1, 1),
                form_submit('submit', _('Save'))
            ], page_link_to('admin_news&action=save&id=' . $news_id));

            $html .= '<a class="btn btn-danger" href="' . page_link_to('admin_news&action=delete&id=' . $news_id) . '">'
                . '<span class="glyphicon glyphicon-trash"></span> ' . _("Delete")
                . '</a>';
            break;

        case 'save':
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
                    $_POST["eBetreff"],
                    $_POST["eText"],
                    $user['UID'],
                    isset($_POST["eTreffen"]) ? 1 : 0,
                    $news_id
                ]
            );
            engelsystem_log('News updated: ' . $_POST['eBetreff']);
            success(_('News entry updated.'));
            redirect(page_link_to('news'));
            break;

        case 'delete':
            $news = array_shift($news);
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
