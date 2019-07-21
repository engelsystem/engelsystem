<?php

namespace Engelsystem\Test\Unit\Http\Validation\Rules;

use Engelsystem\Http\Validation\Rules\In;
use Engelsystem\Test\Unit\TestCase;

class InTest extends TestCase
{
    /**
     * @covers \Engelsystem\Http\Validation\Rules\In::__construct
     */
    public function testConstruct()
    {
        $rule = new In('foo,bar');

        $this->assertEquals(['foo', 'bar'], $rule->haystack);
    }
}
