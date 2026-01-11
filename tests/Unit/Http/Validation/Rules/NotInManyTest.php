<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\NotInMany;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(NotInMany::class, 'validate')]
class NotInManyTest extends TestCase
{
    public function testValidate(): void
    {
        $rule = new NotInMany('foo,bar');

        $this->assertTrue($rule->validate(['lorem']));
        $this->assertTrue($rule->validate(['foo,bar']));
        $this->assertFalse($rule->validate(['foo']));
        $this->assertFalse($rule->validate(['bar']));
        $this->assertFalse($rule->validate(['test', 'foo']));
    }
}
