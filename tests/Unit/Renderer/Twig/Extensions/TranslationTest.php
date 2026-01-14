<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Renderer\Twig\Extensions\Translation;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Translation::class, '__construct')]
#[CoversMethod(Translation::class, 'getFilters')]
#[CoversMethod(Translation::class, 'getFunctions')]
class TranslationTest extends ExtensionTestCase
{
    public function testGetFilters(): void
    {
        $translator = $this->createStub(Translator::class);

        $extension = new Translation($translator);
        $filters = $extension->getFilters();

        $this->assertFilterExists('trans', [$translator, 'translate'], $filters);
    }

    public function testGetFunctions(): void
    {
        $translator = $this->createStub(Translator::class);

        $extension = new Translation($translator);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('__', [$translator, 'translate'], $functions);
        $this->assertExtensionExists('_e', [$translator, 'translatePlural'], $functions);
    }
}
