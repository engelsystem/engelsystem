<?php

namespace Engelsystem\Controllers;

use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Models\News;
use Illuminate\Support\Collection;

class FeedController extends BaseController
{
    /** @var array<string, string> */
    protected array $permissions = [
        'atom' => 'atom',
        'rss'  => 'atom',
        'ical' => 'ical',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected Request $request,
        protected Response $response,
    ) {
    }

    public function atom(): Response
    {
        $news = $this->getNews();

        return $this->response
            ->withHeader('content-type', 'application/atom+xml; charset=utf-8')
            ->withView('api/atom', ['news' => $news]);
    }

    public function rss(): Response
    {
        $news = $this->getNews();

        return $this->response
            ->withHeader('content-type', 'application/rss+xml; charset=utf-8')
            ->withView('api/rss', ['news' => $news]);
    }

    public function ical(): Response
    {
        $shifts = $this->getShifts();

        return $this->response
            ->withHeader('content-type', 'text/calendar; charset=utf-8')
            ->withHeader('content-disposition', 'attachment; filename=shifts.ics')
            ->withView('api/ical', ['shiftEntries' => $shifts]);
    }

    protected function getNews(): Collection
    {
        $news = $this->request->has('meetings')
            ? News::whereIsMeeting((bool) $this->request->get('meetings', false))
            : News::query();
        $news
            ->limit((int) config('display_news'))
            ->orderByDesc('updated_at');

        return $news->get();
    }

    protected function getShifts(): Collection
    {
        return $this->auth->userFromApi()
            ->shiftEntries()
            ->leftJoin('shifts', 'shifts.id', 'shift_entries.shift_id')
            ->orderBy('shifts.start')
            ->with(['shift', 'shift.room', 'shift.shiftType'])
            ->get();
    }
}
