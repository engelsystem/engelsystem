<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Test\Unit\TestCase;

/**
 * Tests for case-insensitive multibyte string search behavior.
 *
 * @see https://github.com/engelsystem/engelsystem/issues/1317
 * @see https://github.com/engelsystem/engelsystem/issues/606
 * @coversNothing
 */
class StringSearchTest extends TestCase
{
    /**
     * @dataProvider provideUmlautSearchCases
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
     */
    public function testAsciiFoldingSearch(
        string $haystack,
        string $needle,
        bool $expectedFound
    ): void {
        $normalize = fn($s) => transliterator_transliterate('Any-Latin; Latin-ASCII; Lower', $s) ?: mb_strtolower($s);
        $found = mb_stripos($normalize($haystack), $normalize($needle)) !== false;
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
