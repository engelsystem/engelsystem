<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Config;

use FastRoute\RouteCollector;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class RoutesFileTest extends TestCase
{
    #[DoesNotPerformAssertions]
    public function testLoadRoutes(): void
    {
        $route = $this->getStubBuilder(RouteCollector::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addRoute'])
            ->getStub();

        /** @see RouteCollector::addRoute */
        $route->method('addRoute')
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
                    sprintf('The route "%s %s" is not cacheable', implode(',', (array) $httpMethod), $route)
                );
            });

        require __DIR__ . '/../../../config/routes.php';
    }
}
