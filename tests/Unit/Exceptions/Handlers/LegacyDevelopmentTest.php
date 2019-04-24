<?php

namespace Engelsystem\Test\Unit\Exceptions\handlers;

use Engelsystem\Exceptions\Handlers\LegacyDevelopment;
use Engelsystem\Http\Request;
use ErrorException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LegacyDevelopmentTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\Handlers\LegacyDevelopment::formatStackTrace()
     * @covers \Engelsystem\Exceptions\Handlers\LegacyDevelopment::render()
     */
    public function testRender()
    {
        $handler = new LegacyDevelopment();
        /** @var Request|MockObject $request */
        $request = $this->createMock(Request::class);
        $exception = new ErrorException('Lorem Ipsum', 4242, 1, 'foo.php', 9999);

        $regex = sprintf(
            '%%<pre.*>.*ErrorException.*4242.*Lorem Ipsum.*%s.*%s.*%s.*</pre>%%is',
            'foo.php',
            9999,
            __FUNCTION__
        );
        $this->expectOutputRegex($regex);

        $handler->render($request, $exception);
    }
}
