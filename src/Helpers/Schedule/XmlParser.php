<?php

declare(strict_types=1);

namespace Engelsystem\Helpers\Schedule;

use Carbon\Carbon;
use SimpleXMLElement;

class XmlParser
{
    protected SimpleXMLElement $scheduleXML;

    protected Schedule $schedule;

    public function load(string $xml): bool
    {
        $scheduleXML = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOWARNING | LIBXML_NOERROR);
        if (!$scheduleXML) {
            return false;
        }

        $this->scheduleXML = $scheduleXML;
        $this->parseXml();
        return true;
    }

    /**
     * Parse the predefined XML content
     *
     * See also https://c3voc.de/wiki/schedule
     *
     * According to https://github.com/voc/schedule/blob/master/validator/xsd/schedule.xml.xsd
     */
    protected function parseXml(): void
    {
        $version = $this->getFirstXpathContent('version');
        $generator = $this->parseGenerator($this->scheduleXML);
        $color = $this->parseConferenceColor($this->scheduleXML);
        $tracks = $this->parseTracks($this->scheduleXML);

        $conference = new Conference(
            $this->getFirstXpathContent('conference/title'),
            $this->getFirstXpathContent('conference/acronym'),
            $this->getFirstXpathContent('conference/start'),
            $this->getFirstXpathContent('conference/end'),
            (int) $this->getFirstXpathContent('conference/days'),
            $this->getFirstXpathContent('conference/timeslot_duration'),
            $this->getFirstXpathContent('conference/base_url'),
            $this->getFirstXpathContent('conference/logo'),
            $this->getFirstXpathContent('conference/url'),
            $this->getFirstXpathContent('conference/time_zone_name'),
            $color,
            $tracks,
        );

        $days = [];
        foreach ($this->scheduleXML->xpath('day') as $day) {
            $rooms = [];

            foreach ($day->xpath('room') as $roomElement) {
                $guid = (string) $roomElement->attributes()['guid'];
                $room = new Room(
                    (string) $roomElement->attributes()['name'],
                    !empty($guid) ? $guid : null
                );

                $events = $this->parseEvents($roomElement->xpath('event'), $room, $tracks);
                $room->setEvents($events);
                $rooms[] = $room;
            }

            $data = $day->attributes();
            $days[] = new Day(
                (string) $data['date'],
                new Carbon((string) $data['start']),
                new Carbon((string) $data['end']),
                (int) $data['index'],
                $rooms
            );
        }

        $this->schedule = new Schedule(
            $version,
            $conference,
            $days,
            $generator
        );
    }

    protected function parseGenerator(SimpleXMLElement $scheduleXML): ?ScheduleGenerator
    {
        $generatorData = $scheduleXML->xpath('generator');
        if (!isset($generatorData[0])) {
            return null;
        }

        $data = $generatorData[0]->attributes();
        return new ScheduleGenerator(
            (string) $data['name'] ?? null,
            (string) $data['version'] ?? null,
        );
    }

    protected function parseConferenceColor(SimpleXMLElement $scheduleXML): ?ConferenceColor
    {
        $conferenceColorData = $scheduleXML->xpath('conference/color');
        if (!isset($conferenceColorData[0])) {
            return null;
        }

        $data = collect($conferenceColorData[0]->attributes())->map(fn($value) => (string) $value);
        $additionalData = $data->collect()->forget(['primary', 'background']);
        return new ConferenceColor(
            $data['primary'] ?? null,
            $data['background'] ?? null,
            $additionalData->isNotEmpty() ? $additionalData->toArray() : []
        );
    }

    /**
     * @return ConferenceTrack[]
     */
    protected function parseTracks(SimpleXMLElement $scheduleXML): array
    {
        $tracksData = $scheduleXML->xpath('conference/track');
        if (!isset($tracksData[0])) {
            return [];
        }

        $tracks = [];
        foreach ($tracksData as $trackData) {
            $data = $trackData->attributes();
            $tracks[] = new ConferenceTrack(
                (string) $data['name'],
                (string) $data['color'] ?? null,
                (string) $data['slug'] ?? null,
            );
        }
        return $tracks;
    }

    /**
     * @param SimpleXMLElement[] $eventElements
     * @param ConferenceTrack[] $tracks
     */
    protected function parseEvents(array $eventElements, Room $room, array $tracks): array
    {
        $events = [];

        foreach ($eventElements as $event) {
            $persons = $this->getListFromSequence($event, 'persons', 'person', 'id');
            $links = $this->getListFromSequence($event, 'links', 'link', 'href');
            $attachments = $this->getListFromSequence($event, 'attachments', 'attachment', 'href');

            $recording = $this->parseRecording($event);
            $trackName = $this->getFirstXpathContent('track', $event);
            $track = collect($tracks)->where('name', $trackName)->first() ?: new ConferenceTrack($trackName);

            $events[] = new Event(
                (string) $event->attributes()['guid'],
                (int) $event->attributes()['id'],
                $room,
                $this->getFirstXpathContent('title', $event),
                $this->getFirstXpathContent('subtitle', $event),
                $this->getFirstXpathContent('type', $event),
                new Carbon($this->getFirstXpathContent('date', $event)),
                $this->getFirstXpathContent('start', $event),
                $this->getFirstXpathContent('duration', $event),
                $this->getFirstXpathContent('abstract', $event),
                $this->getFirstXpathContent('slug', $event),
                $track,
                $this->getFirstXpathContent('logo', $event) ?: null,
                $persons,
                $this->getFirstXpathContent('language', $event) ?: null,
                $this->getFirstXpathContent('description', $event) ?: null,
                $recording,
                $links,
                $attachments,
                $this->getFirstXpathContent('url', $event) ?: null,
                $this->getFirstXpathContent('video_download_url', $event) ?: null,
                $this->getFirstXpathContent('feedback_url', $event) ?: null,
                $this->getFirstXpathContent('origin_url', $event) ?: null,
            );
        }

        return $events;
    }

    protected function parseRecording(SimpleXMLElement $event): ?EventRecording
    {
        $recordingElement = $event->xpath('recording');
        if (!isset($recordingElement[0])) {
            return null;
        }

        $element = $recordingElement[0];
        return new EventRecording(
            $this->getFirstXpathContent('license', $element) ?: '',
            $this->getFirstXpathContent('optout', $element) != 'false',
            $this->getFirstXpathContent('url', $element) ?: null,
            $this->getFirstXpathContent('link', $element) ?: null,
        );
    }

    protected function getFirstXpathContent(string $path, ?SimpleXMLElement $xml = null): string
    {
        $element = ($xml ?: $this->scheduleXML)->xpath($path);

        return $element ? (string) $element[0] : '';
    }

    /**
     * Resolves a list from a sequence of elements
     */
    protected function getListFromSequence(
        SimpleXMLElement $element,
        string $firstElement,
        string $secondElement,
        string $idAttribute
    ): array {
        $items = [];

        foreach ($element->xpath($firstElement) as $element) {
            foreach ($element->xpath($secondElement) as $item) {
                $items[(string) $item->attributes()[$idAttribute]] = (string) $item;
            }
        }

        return $items;
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }
}
