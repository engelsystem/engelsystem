<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\InMany;
use Engelsystem\Test\Unit\TestCase;

class InManyTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Rules\InMany::validate
     */
    public function testValidate(): void
    {
        $rule = new InMany('foo,bar');

        $this->assertTrue($rule->validate(['foo']));
        $this->assertTrue($rule->validate(['bar']));
        $this->assertTrue($rule->validate(['foo', 'bar']));

        $this->assertFalse($rule->validate(['baz']));
        $this->assertFalse($rule->validate(['foo,bar']));
        $this->assertFalse($rule->validate(['foo', 'bar', 'baz']));
    }
}
