<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Test\Unit\TestCase;

/**
 * Tests for case-insensitive multibyte string search behavior.
 *
 * @see https://github.com/engelsystem/engelsystem/issues/1317
 * @see https://github.com/engelsystem/engelsystem/issues/606
 */
class StringSearchTest extends TestCase
{
    /**
     * @dataProvider provideUmlautSearchCases
     * @coversNothing
     */
    public function testMbStriposFindsUmlautsCaseInsensitively(
        string $haystack,
        string $needle,
        bool $expectedFound
    ): void {
        $found = mb_stripos($haystack, $needle) !== false;
        $this->assertSame($expectedFound, $found);
    }

    public function provideUmlautSearchCases(): array
    {
        return [
            'lowercase ä in uppercase Ä' => ['KRÄMER', 'ä', true],
            'uppercase Ä in lowercase ä' => ['krämer', 'Ä', true],
            'lowercase ö in uppercase Ö' => ['KÖNIG', 'ö', true],
            'uppercase Ö in lowercase ö' => ['könig', 'Ö', true],
            'lowercase ü in uppercase Ü' => ['MÜLLER', 'ü', true],
            'uppercase Ü in lowercase ü' => ['müller', 'Ü', true],
            'lowercase ß in uppercase SS' => ['GROẞE', 'ß', true],
            'exact match' => ['Müller', 'Müller', true],
            'partial match' => ['Müller', 'üll', true],
            'no match' => ['Schmidt', 'ü', false],
        ];
    }

    /**
     * @dataProvider provideAsciiFoldingCases
     * @covers ::normalize_for_search
     */
    public function testAsciiFoldingSearch(
        string $haystack,
        string $needle,
        bool $expectedFound
    ): void {
        $found = mb_stripos(normalize_for_search($haystack), normalize_for_search($needle)) !== false;
        $this->assertSame($expectedFound, $found);
    }

    public function provideAsciiFoldingCases(): array
    {
        return [
            'ASCII finds umlaut ü' => ['Müller', 'muller', true],
            'ASCII finds umlaut ä' => ['Krämer', 'kramer', true],
            'ASCII finds accented é' => ['céline', 'celine', true],
            'ASCII finds Turkish ı' => ['yıldırım', 'yildirim', true],
            'ASCII finds German ß' => ['Großmann', 'grossmann', true],
            'partial ASCII match' => ['Tannhäuser', 'hauser', true],
            'no match' => ['Schmidt', 'muller', false],
        ];
    }
}
