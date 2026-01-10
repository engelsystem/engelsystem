<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Exceptions\Handlers;

use Engelsystem\Exceptions\Handlers\LegacyDevelopment;
use Engelsystem\Http\Request;
use ErrorException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(LegacyDevelopment::class, 'formatStackTrace')]
#[CoversMethod(LegacyDevelopment::class, 'render')]
#[CoversMethod(LegacyDevelopment::class, 'getDisplayNameOfValue')]
class LegacyDevelopmentTest extends TestCase
{
    public function testRender(): void
    {
        $handler = new LegacyDevelopment();
        $request = $this->createStub(Request::class);
        $exception = new ErrorException('Lorem <b>Ipsum</b>', 4242, 1, 'foo.php', 9999);

        $regex = sprintf(
            '%%<pre.*>.*ErrorException.*4242.*Lorem &lt;b&gt;Ipsum&lt;/b&gt;.*%s.*%s.*%s.*</pre>%%is',
            'foo.php',
            9999,
            __FUNCTION__
        );
        $this->expectOutputRegex($regex);

        $handler->render($request, $exception);
    }
}
