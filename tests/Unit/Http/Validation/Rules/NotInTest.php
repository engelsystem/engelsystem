<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\NotIn;
use Engelsystem\Test\Unit\TestCase;

class NotInTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Rules\NotIn::validate
     */
    public function testConstruct(): void
    {
        $rule = new NotIn('foo,bar');

        $this->assertTrue($rule->validate('lorem'));
        $this->assertFalse($rule->validate('foo'));
    }
}
