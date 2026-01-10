<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\In;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(In::class, '__construct')]
class InTest extends TestCase
{
    public function testConstruct(): void
    {
        $rule = new In('foo,bar');

        $this->assertTrue($rule->validate('foo'));
        $this->assertTrue($rule->validate('bar'));

        $this->assertFalse($rule->validate('baz'));
        $this->assertFalse($rule->validate('foo,bar'));
    }
}
