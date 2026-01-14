<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Renderer\Twig\Extensions\StringExtension;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(StringExtension::class, 'getFilters')]
class StringExtensionTest extends ExtensionTestCase
{
    public function testGetFilters(): void
    {
        $extension = new StringExtension();
        $filters = $extension->getFilters();

        $this->assertFilterExists('slug', null, $filters);
    }

    public function testGetFiltersSlug(): void
    {
        $extension = new StringExtension();
        $filters = $extension->getFilters();

        foreach ($filters as $filter) {
            if ($filter->getName() != 'slug') {
                continue;
            }

            $this->assertEquals('foo-at-bar', $filter->getCallable()(' Foo @Bar!'));
            return;
        }

        $this->fail(sprintf('Filter %s not found', 'slug'));
    }
}
