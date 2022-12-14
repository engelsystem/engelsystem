<?php

namespace Engelsystem\Test\Unit\Config;

use FastRoute\RouteCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RoutesFileTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testLoadRoutes(): void
    {
        /** @var RouteCollector|MockObject $route */
        $route = $this->getMockBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addRoute'])
            ->getMock();

        $this->doesNotPerformAssertions();
        /** @see RouteCollector::addRoute */
        $route->expects($this->any())
            ->method('addRoute')
            ->willReturnCallback(function ($httpMethod, $route, $handler): void {
                /**
                 * @param string|string[] $httpMethod
                 * @param string          $route
                 * @param mixed           $handler
                 */
                if (is_string($handler) || (is_array($handler) && is_string($handler[0]))) {
                    return;
                }

                $this->fail(
                    sprintf('The route "%s %s" is not cacheable', implode(',', (array)$httpMethod), $route)
                );
            });

        require __DIR__ . '/../../../config/routes.php';
    }
}
