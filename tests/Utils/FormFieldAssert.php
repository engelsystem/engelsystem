<?php

declare(strict_types=1);

namespace Engelsystem\Test\Utils;

use PHPUnit\Framework\Assert;

/**
 * Class that provides some form field assertions.
 */
final class FormFieldAssert
{
    /**
     * Asserts that the HTML does contain an INPUT field with the given name.
     */
    public static function assertContainsInputField(string $name, string $html): void
    {
        Assert::assertMatchesRegularExpression(self::makeInputPattern('input', $name), $html);
    }

    /**
     * Asserts that the HTML does not contain an INPUT field with the given name.
     */
    public static function assertNotContainsInputField(string $name, string $html): void
    {
        Assert::assertDoesNotMatchRegularExpression(self::makeInputPattern('input', $name), $html);
    }

    /**
     * Asserts that the HTML does contain a SELECT field with the given name.
     */
    public static function assertContainsSelectField(string $name, string $html): void
    {
        Assert::assertMatchesRegularExpression(self::makeInputPattern('select', $name), $html);
    }

    /**
     * Asserts that the HTML does not contain a SELECT field with the given name.
     */
    public static function assertNotContainsSelectField(string $name, string $html): void
    {
        Assert::assertDoesNotMatchRegularExpression(self::makeInputPattern('select', $name), $html);
    }

    /**
     * Asserts that the HTML does contain an INPUT field of the type "checkbox" with the give name and
     * with the "checked" attribute.
     */
    public static function assertContainsCheckedCheckbox(string $name, string $html): void
    {
        Assert::assertMatchesRegularExpression(self::makeCheckedCheckboxPattern($name), $html);
    }

    /**
     * Asserts that the HTML does contain an INPUT field of the type "checkbox" with the given name and
     * without the "checked" attribute.
     */
    public static function assertContainsUncheckedCheckbox(string $name, string $html): void
    {
        self::assertContainsInputField($name, $html);
        Assert::assertDoesNotMatchRegularExpression(self::makeCheckedCheckboxPattern($name), $html);
    }

    private static function makeInputPattern(string $tag, string $name): string
    {
        return strtr('/<$TAG[^>]*name="$NAME"/s', [
            '$TAG' => $tag,
            '$NAME' => $name,
        ]);
    }

    private static function makeCheckedCheckboxPattern(string $name): string
    {
        return strtr('/<input[^>]*type="checkbox"[^>]*name="$NAME"[^>]*checked.*?/s', [
            '$NAME' => $name,
        ]);
    }
}
