<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

use Carbon\Carbon;

class Event
{
    use CalculatesTime;

    /** @var Carbon Calculated */
    protected Carbon $endDate;

    /**
     * @param string      $guid globally unique
     * @param int         $id globally unique
     * @param string      $start time (hh:mm:ss || hh:mm)
     * @param string      $duration (h?h:mm:ss || h?h:mm)
     * @param string      $slug globally unique
     * @param string[]    $persons id => name
     * @param string|null $language two letter code
     * @param string|null $recording license (and opt out in XML, null if not recorded, empty if no license defined)/
     * @param array       $links href => title
     * @param array       $attachments href => title
     */
    public function __construct(
        protected string $guid,
        protected int $id,
        protected Room $room,
        protected string $title,
        protected string $subtitle,
        protected string $type,
        protected Carbon $date,
        protected string $start,
        protected string $duration,
        protected string $abstract,
        protected string $slug,
        protected string $track,
        protected ?string $logo = null,
        protected array $persons = [],
        protected ?string $language = null,
        protected ?string $description = null,
        protected string $recording = '',
        protected array $links = [],
        protected array $attachments = [],
        protected ?string $url = null,
        protected ?string $videoDownloadUrl = null
    ) {
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

    public function getLinks(): array
    {
        return $this->links;
    }

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
