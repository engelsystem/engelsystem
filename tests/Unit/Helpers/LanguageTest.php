<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\Language;
use Engelsystem\Test\Unit\TestCase;

class LanguageTest extends TestCase
{
    /**
     * @covers \Engelsystem\Helpers\Language::isValid
     */
    public function testIsValidIso639(): void
    {
        // Valid ISO 639-1 codes
        $this->assertTrue(Language::isValid('en'));
        $this->assertTrue(Language::isValid('de'));
        $this->assertTrue(Language::isValid('fr'));
        $this->assertTrue(Language::isValid('zh'));

        // Invalid codes
        $this->assertFalse(Language::isValid(''));
        $this->assertFalse(Language::isValid('xxx'));
        $this->assertFalse(Language::isValid('english'));
    }

    /**
     * @covers \Engelsystem\Helpers\Language::isValid
     */
    public function testIsValidBcp47(): void
    {
        // Valid BCP47 tags
        $this->assertTrue(Language::isValid('en-US'));
        $this->assertTrue(Language::isValid('pt-BR'));
        $this->assertTrue(Language::isValid('zh-Hans'));
        $this->assertTrue(Language::isValid('zh-TW'));
        $this->assertTrue(Language::isValid('de-AT'));

        // Case insensitive
        $this->assertTrue(Language::isValid('EN-us'));
        $this->assertTrue(Language::isValid('PT-br'));
    }

    /**
     * @covers \Engelsystem\Helpers\Language::isValid
     */
    public function testIsValidCommonTags(): void
    {
        // Tags from COMMON_TAGS constant
        $this->assertTrue(Language::isValid('en-GB'));
        $this->assertTrue(Language::isValid('de-CH'));
        $this->assertTrue(Language::isValid('zh-CN'));
        $this->assertTrue(Language::isValid('es-419'));
    }

    /**
     * @covers \Engelsystem\Helpers\Language::getName
     */
    public function testGetNameIso639(): void
    {
        $this->assertEquals('English', Language::getName('en'));
        $this->assertEquals('German', Language::getName('de'));
        $this->assertEquals('French', Language::getName('fr'));
        $this->assertEquals('Japanese', Language::getName('ja'));
    }

    /**
     * @covers \Engelsystem\Helpers\Language::getName
     */
    public function testGetNameBcp47(): void
    {
        $this->assertEquals('English (UK)', Language::getName('en-GB'));
        $this->assertEquals('Portuguese (Brazil)', Language::getName('pt-BR'));
        $this->assertEquals('German (Switzerland)', Language::getName('de-CH'));
        $this->assertEquals('Chinese (Simplified)', Language::getName('zh-Hans'));
    }

    /**
     * @covers \Engelsystem\Helpers\Language::getName
     */
    public function testGetNameUnknownRegion(): void
    {
        // Valid base code with unknown region should still work
        $this->assertEquals('English (XX)', Language::getName('en-XX'));
    }

    /**
     * @covers \Engelsystem\Helpers\Language::getName
     */
    public function testGetNameInvalid(): void
    {
        // Invalid codes return themselves
        $this->assertEquals('xxx', Language::getName('xxx'));
        $this->assertEquals('', Language::getName(''));
    }

    /**
     * @covers \Engelsystem\Helpers\Language::normalize
     */
    public function testNormalize(): void
    {
        // Lowercase base code
        $this->assertEquals('en', Language::normalize('EN'));
        $this->assertEquals('de', Language::normalize('DE'));

        // Uppercase region
        $this->assertEquals('en-US', Language::normalize('en-us'));
        $this->assertEquals('pt-BR', Language::normalize('PT-br'));

        // Title case script
        $this->assertEquals('zh-Hans', Language::normalize('zh-hans'));
        $this->assertEquals('zh-Hant', Language::normalize('ZH-HANT'));

        // Common tags preserve their format
        $this->assertEquals('en-GB', Language::normalize('EN-gb'));
    }

    /**
     * @covers \Engelsystem\Helpers\Language::getAllOptions
     */
    public function testGetAllOptions(): void
    {
        $options = Language::getAllOptions();

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        // Check structure
        $firstOption = $options[0];
        $this->assertArrayHasKey('code', $firstOption);
        $this->assertArrayHasKey('name', $firstOption);

        // Should include common tags and ISO codes
        $codes = array_column($options, 'code');
        $this->assertContains('en-GB', $codes);
        $this->assertContains('de', $codes);
        $this->assertContains('fr', $codes);
    }
}
