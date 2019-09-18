<?php

use Engelsystem\Database\DB;
use Engelsystem\Http\Exceptions\HttpForbidden;

/**
 * Publically available page to feed the news to feed readers
 */
function user_atom()
{
    $request = request();
    $user = auth()->apiUser('key');

    if (
        !$request->has('key')
        || !preg_match('/^[\da-f]{32}$/', $request->input('key'))
        || empty($user)
    ) {
        throw new HttpForbidden('Missing or invalid key', ['content-type' => 'text/text']);
    }

    if (!auth()->can('atom')) {
        throw new HttpForbidden('Not allowed', ['content-type' => 'text/text']);
    }

    $news = DB::select('
        SELECT *
        FROM `News`
        ' . (!$request->has('meetings') ? '' : 'WHERE `Treffen` = 1 ') . '
        ORDER BY `ID`
        DESC LIMIT ' . (int)config('display_news')
    );

    $output = make_atom_entries_from_news($news);

    header('Content-Type: application/atom+xml; charset=utf-8');
    header('Content-Length: ' . strlen($output));
    raw_output($output);
}

/**
 * @param array[] $news_entries
 * @return string
 */
function make_atom_entries_from_news($news_entries)
{
    $request = app('request');
    $html = '<?xml version="1.0" encoding="utf-8"?>
  <feed xmlns="http://www.w3.org/2005/Atom">
  <title>' . config('app_name') . '</title>
  <id>' . $request->getHttpHost()
        . htmlspecialchars(preg_replace(
            '#[&?]key=[a-f\d]{32}#',
            '',
            $request->getRequestUri()
        ))
        . '</id>
  <updated>' . date('Y-m-d\TH:i:sP', $news_entries[0]['Datum']) . '</updated>' . "\n";
    foreach ($news_entries as $news_entry) {
        $html .= make_atom_entry_from_news($news_entry);
    }
    $html .= '</feed>';
    return $html;
}

/**
 * @param array $news_entry
 * @return string
 */
function make_atom_entry_from_news($news_entry)
{
    return '
  <entry>
    <title>' . htmlspecialchars($news_entry['Betreff']) . '</title>
    <link href="' . page_link_to('news_comments', ['nid' => $news_entry['ID']]) . '"/>
    <id>' . preg_replace(
            '#^https?://#',
            '',
            page_link_to('news_comments', ['nid' => $news_entry['ID']])
        ) . '</id>
    <updated>' . date('Y-m-d\TH:i:sP', $news_entry['Datum']) . '</updated>
    <summary type="html">' . htmlspecialchars($news_entry['Text']) . '</summary>
  </entry>' . "\n";
}
