<?php

namespace Engelsystem\Test\Unit\Http;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Engelsystem\Http\Exceptions\HttpRedirect;
use PHPUnit\Framework\TestCase;

class HttpRedirectTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * @covers \Engelsystem\Http\Exceptions\HttpRedirect::__construct
     */
    public function testConstruct()
    {
        $exception = new HttpRedirect('https://lorem.ipsum/foo/bar');
        $this->assertEquals(302, $exception->getStatusCode());
        $this->assertArraySubset(['Location' => 'https://lorem.ipsum/foo/bar'], $exception->getHeaders());

        $exception = new HttpRedirect('/test', 301, ['lorem' => 'ipsum']);
        $this->assertEquals(301, $exception->getStatusCode());
        $this->assertArraySubset(['lorem' => 'ipsum'], $exception->getHeaders());
    }
}
