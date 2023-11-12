<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Carbon\CarbonTimeZone;
use Engelsystem\Helpers\Authenticator;
use Engelsystem\Http\Request;
use Engelsystem\Http\Response;
use Engelsystem\Http\UrlGenerator;
use Engelsystem\Models\News;
use Engelsystem\Models\Shifts\ShiftEntry;
use Illuminate\Support\Collection;

class FeedController extends BaseController
{
    /** @var array<string, string> */
    protected array $permissions = [
        'atom'   => 'atom',
        'rss'    => 'atom',
        'ical'   => 'ical',
        'shifts' => 'shifts_json_export',
    ];

    public function __construct(
        protected Authenticator $auth,
        protected Request $request,
        protected Response $response,
        protected UrlGenerator $url,
    ) {
    }

    public function atom(): Response
    {
        $news = $this->getNews();

        return $this->withEtag($news)
            ->withHeader('content-type', 'application/atom+xml; charset=utf-8')
            ->withView('api/atom', ['news' => $news]);
    }

    public function rss(): Response
    {
        $news = $this->getNews();

        return $this->withEtag($news)
            ->withHeader('content-type', 'application/rss+xml; charset=utf-8')
            ->withView('api/rss', ['news' => $news]);
    }

    public function ical(): Response
    {
        $shifts = $this->getShifts();

        return $this->withEtag($shifts)
            ->withHeader('content-type', 'text/calendar; charset=utf-8')
            ->withHeader('content-disposition', 'attachment; filename=shifts.ics')
            ->withView('api/ical', ['shiftEntries' => $shifts]);
    }

    public function shifts(): Response
    {
        /** @var Collection|ShiftEntry[] $shiftEntries */
        $shiftEntries = $this->getShifts();
        $timeZone = CarbonTimeZone::create(config('timezone'));

        $response = [];
        foreach ($shiftEntries as $entry) {
            $shift = $entry->shift;
            // Data required for the Fahrplan app integration https://github.com/johnjohndoe/engelsystem
            // See engelsystem-base/src/main/kotlin/info/metadude/kotlin/library/engelsystem/models/Shift.kt
            // ! All attributes not defined in $data might change at any time !
            $data = [
                // Name of the shift (type)
                /** @deprecated, use shifttype_name instead */
                'name'           => (string) $shift->shiftType->name,
                // Shift / Talk title
                'title'          => (string) $shift->title,
                // Shift description, should be shown after shifttype_description, markdown formatted
                'description'    => (string) $shift->description,

                'link'           => (string) $this->url->to('/shifts', ['action' => 'view', 'shift_id' => $shift->id]),

                // Users comment, might be empty
                'Comment'        => (string) $entry->user_comment,

                // Shift id
                'SID'            => (int) $shift->id,

                // Shift type
                'shifttype_id'   => (int) $shift->shiftType->id,
                // General type of the task
                'shifttype_name' => (string) $shift->shiftType->name,
                // General description, markdown formatted, might be empty
                'shifttype_description' => (string) $shift->shiftType->description,

                // Talk URL, mostly empty
                'URL'            => (string) $shift->url,

                // Location (room) id
                'RID'            => (int) $shift->location->id,
                // Location (room) name
                'Name'           => (string) $shift->location->name,
                // Location map url, can be empty
                'map_url'        => (string) $shift->location->map_url,

                // Start timestamp
                /** @deprecated start_date should be used */
                'start'          => (int) $shift->start->timestamp,
                // Start date
                'start_date'     => (string) $shift->start->toRfc3339String(),
                // End timestamp
                /** @deprecated end_date should be used */
                'end'            => (int) $shift->end->timestamp,
                // End date
                'end_date'       => (string) $shift->end->toRfc3339String(),

                // Timezone offset like "+01:00"
                /** @deprecated should be retrieved from start_date or end_date */
                'timezone'       => (string) $timeZone->toOffsetName(),
                // The events timezone like "Europe/Berlin"
                'event_timezone' => (string) $timeZone->getName(),
            ];

            $response[] = [
                // Model data
                ...$entry->toArray(),

                // Fahrplan app required data
                ...$data,
            ];
        }

        return $this->withEtag($response)
            ->withAddedHeader('content-type', 'application/json; charset=utf-8')
            ->withContent(json_encode($response));
    }

    protected function withEtag(mixed $value): Response
    {
        $hash = md5(json_encode($value));

        return $this->response->setEtag($hash);
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
            ->with(['shift', 'shift.location', 'shift.shiftType'])
            ->get(['*', 'shift_entries.id']);
    }
}
