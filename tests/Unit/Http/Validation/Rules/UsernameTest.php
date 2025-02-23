<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Config\Config;
use Engelsystem\Http\Validation\Rules\Username;
use Engelsystem\Test\Unit\ServiceProviderTest;
use RuntimeException;

class UsernameTest extends ServiceProviderTest
{
    private Username $subject;

    private Config $config;

    public function setUp(): void
    {
        $this->subject = new Username();

        $app = $this->createAndSetUpAppWithConfig([]);
        $this->config = $app->get('config');

        // load "username_regex" from the default config
        $defaultConfig = include __DIR__ . '/../../../../../config/config.default.php';
        $this->config->set('username_regex', $defaultConfig['username_regex']);
    }

    /**
     * @return array<string,array{string,bool}>
     */
    public function provideIsValidWithDefaultConfigTestData(): array
    {
        return [
            'empty string' => ['', false],
            'max length exceeded' => [str_repeat('1', 25), false],
            'invalid char !' => ['abc!!!', false],
            'space' => ['ab c', false],
            'valid Greek letters' => ['λουκάνικο', true],
            'min length valid' => ['a', true],
            'valid with all chars' => ['abc123_-.jkl', true],
            'valid with accents' => ['café', true],
            'max length valid' => [str_repeat('a', 24), true],
        ];
    }

    /**
     * @covers \Engelsystem\Http\Validation\Rules\Username::isValid
     * @dataProvider provideIsValidWithDefaultConfigTestData
     */
    public function testIsValidWithDefaultConfig(mixed $value, bool $expectedValid): void
    {
        self::assertSame($expectedValid, $this->subject->isValid($value));
    }

    /**
     * @covers \Engelsystem\Http\Validation\Rules\Username::isValid
     */
    public function testIsValidMissingConfigRaisesException(): void
    {
        $this->config->set('username_regex', null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('username_regex not set in config');
        $this->subject->isValid('test');
    }
}
