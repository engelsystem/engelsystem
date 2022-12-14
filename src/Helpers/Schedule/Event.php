<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

use Carbon\Carbon;

class Event
{
    use CalculatesTime;

    /** @var string required globally unique */
    protected $guid;

    /** @var int required globally unique */
    protected $id;

    /** @var Room required, string in XML */
    protected $room;

    /** @var string required */
    protected $title;

    /** @var string required */
    protected $subtitle;

    /** @var string required */
    protected $type;

    /** @var Carbon required */
    protected $date;

    /** @var string required time (hh:mm:ss || hh:mm) */
    protected $start;

    /** @var string required (h?h:mm:ss || h?h:mm) */
    protected $duration;

    /** @var string required */
    protected $abstract;

    /** @var string required globally unique */
    protected $slug;

    /** @var string required */
    protected $track;

    /** @var string|null */
    protected $logo;

    /** @var string[] id => name */
    protected $persons;

    /** @var string|null two letter code */
    protected $language;

    /** @var string|null */
    protected $description;

    /** @var string|null license (and opt out in XML, null if not recorded, empty if no license defined) */
    protected $recording;

    /** @var array href => title */
    protected $links;

    /** @var array href => name */
    protected $attachments;

    /** @var string|null */
    protected $url;

    /** @var string|null */
    protected $videoDownloadUrl;

    /** @var Carbon Calculated */
    protected $endDate;

    /**
     * Event constructor.
     *
     * @param string[]    $persons
     * @param string|null $recording license
     * @param array       $links
     * @param array       $attachments
     */
    public function __construct(
        string $guid,
        int $id,
        Room $room,
        string $title,
        string $subtitle,
        string $type,
        Carbon $date,
        string $start,
        string $duration,
        string $abstract,
        string $slug,
        string $track,
        ?string $logo = null,
        array $persons = [],
        ?string $language = null,
        ?string $description = null,
        string $recording = '',
        array $links = [],
        array $attachments = [],
        ?string $url = null,
        ?string $videoDownloadUrl = null
    ) {
        $this->guid = $guid;
        $this->id = $id;
        $this->room = $room;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->type = $type;
        $this->date = $date;
        $this->start = $start;
        $this->duration = $duration;
        $this->abstract = $abstract;
        $this->slug = $slug;
        $this->track = $track;
        $this->logo = $logo;
        $this->persons = $persons;
        $this->language = $language;
        $this->description = $description;
        $this->recording = $recording;
        $this->links = $links;
        $this->attachments = $attachments;
        $this->url = $url;
        $this->videoDownloadUrl = $videoDownloadUrl;

        $this->endDate = $this->date
            ->copy()
            ->addSeconds($this->getDurationSeconds());
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDate(): Carbon
    {
        return $this->date;
    }

    public function getStart(): string
    {
        return $this->start;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function getDurationSeconds(): int
    {
        return $this->secondsFromTime($this->duration);
    }

    public function getAbstract(): string
    {
        return $this->abstract;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTrack(): string
    {
        return $this->track;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * @return string[]
     */
    public function getPersons(): array
    {
        return $this->persons;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getRecording(): ?string
    {
        return $this->recording;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return array
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getVideoDownloadUrl(): ?string
    {
        return $this->videoDownloadUrl;
    }

    public function getEndDate(): Carbon
    {
        return $this->endDate;
    }
}
