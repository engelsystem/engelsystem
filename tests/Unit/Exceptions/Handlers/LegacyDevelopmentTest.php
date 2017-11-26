<?php

namespace Engelsystem\Test\Unit\Exceptions\handlers;


use Engelsystem\Exceptions\Handlers\LegacyDevelopment;
use Engelsystem\Http\Request;
use ErrorException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class LegacyDevelopmentTest extends TestCase
{
    /**
     * @covers \Engelsystem\Exceptions\Handlers\LegacyDevelopment::render()
     * @covers \Engelsystem\Exceptions\Handlers\LegacyDevelopment::formatStackTrace()
     */
    public function testRender()
    {
        $handler = new LegacyDevelopment();
        /** @var Request|Mock $request */
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
