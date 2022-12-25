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
        $scheduleXML = simplexml_load_string($xml);

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
     * According to https://github.com/voc/voctosched/blob/master/schema/basic.xsd
     */
    protected function parseXml(): void
    {
        $version = $this->getFirstXpathContent('version');
        $conference = new Conference(
            $this->getFirstXpathContent('conference/title'),
            $this->getFirstXpathContent('conference/acronym'),
            $this->getFirstXpathContent('conference/start'),
            $this->getFirstXpathContent('conference/end'),
            (int) $this->getFirstXpathContent('conference/days'),
            $this->getFirstXpathContent('conference/timeslot_duration'),
            $this->getFirstXpathContent('conference/base_url')
        );
        $days = [];

        foreach ($this->scheduleXML->xpath('day') as $day) {
            $rooms = [];

            foreach ($day->xpath('room') as $roomElement) {
                $room = new Room(
                    (string) $roomElement->attributes()['name']
                );

                $events = $this->parseEvents($roomElement->xpath('event'), $room);
                $room->setEvent($events);
                $rooms[] = $room;
            }

            $days[] = new Day(
                (string) $day->attributes()['date'],
                new Carbon($day->attributes()['start']),
                new Carbon($day->attributes()['end']),
                (int) $day->attributes()['index'],
                $rooms
            );
        }

        $this->schedule = new Schedule(
            $version,
            $conference,
            $days
        );
    }

    /**
     * @param SimpleXMLElement[] $eventElements
     */
    protected function parseEvents(array $eventElements, Room $room): array
    {
        $events = [];

        foreach ($eventElements as $event) {
            $persons = $this->getListFromSequence($event, 'persons', 'person', 'id');
            $links = $this->getListFromSequence($event, 'links', 'link', 'href');
            $attachments = $this->getListFromSequence($event, 'attachments', 'attachment', 'href');

            $recording = '';
            $recordingElement = $event->xpath('recording');
            if ($recordingElement && $this->getFirstXpathContent('optout', $recordingElement[0]) == 'false') {
                $recording = $this->getFirstXpathContent('license', $recordingElement[0]);
            }

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
                $this->getFirstXpathContent('track', $event),
                $this->getFirstXpathContent('logo', $event) ?: null,
                $persons,
                $this->getFirstXpathContent('language', $event) ?: null,
                $this->getFirstXpathContent('description', $event) ?: null,
                $recording,
                $links,
                $attachments,
                $this->getFirstXpathContent('url', $event) ?: null,
                $this->getFirstXpathContent('video_download_url', $event) ?: null
            );
        }

        return $events;
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
