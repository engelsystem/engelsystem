<?php

use Engelsystem\Http\Exceptions\HttpForbidden;
use Engelsystem\Models\News;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

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

    $news = $request->has('meetings') ? News::whereIsMeeting((bool)$request->get('meetings', false)) : News::query();
    $news
        ->limit((int)config('display_news'))
        ->orderByDesc('updated_at');
    $output = make_atom_entries_from_news($news->get());

    header('Content-Type: application/atom+xml; charset=utf-8');
    header('Content-Length: ' . strlen($output));
    raw_output($output);
}

/**
 * @param News[]|Collection|SupportCollection $news_entries
 * @return string
 */
function make_atom_entries_from_news($news_entries)
{
    $request = app('request');
    $updatedAt = isset($news_entries[0]) ? $news_entries[0]->updated_at->format('Y-m-d\TH:i:sP') : '0000:00:00T00:00:00+00:00';

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
<updated>' . $updatedAt . '</updated>' . "\n";
    foreach ($news_entries as $news_entry) {
        $html .= make_atom_entry_from_news($news_entry);
    }
    $html .= '</feed>';
    return $html;
}

/**
 * @param News $news
 * @return string
 */
function make_atom_entry_from_news(News $news)
{
    return '
<entry>
    <title>' . htmlspecialchars($news->title) . '</title>
    <link href="' . page_link_to('news/' . $news->id) . '"/>
    <id>' . preg_replace(
            '#^https?://#',
            '',
            page_link_to('news/' . $news->id)
        ) . '</id>
    <updated>' . $news->updated_at->format('Y-m-d\TH:i:sP') . '</updated>
    <summary type="html">' . htmlspecialchars($news->text) . '</summary>
</entry>' . "\n";
}
