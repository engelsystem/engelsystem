<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Translation\Translator;
use Engelsystem\Renderer\Twig\Extensions\Translation;
use PHPUnit\Framework\MockObject\MockObject;

class TranslationTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Translation::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Translation::getFilters
     */
    public function testGeFilters(): void
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);

        $extension = new Translation($translator);
        $filters = $extension->getFilters();

        $this->assertExtensionExists('trans', [$translator, 'translate'], $filters);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Translation::getFunctions
     */
    public function testGetFunctions(): void
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);

        $extension = new Translation($translator);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('__', [$translator, 'translate'], $functions);
        $this->assertExtensionExists('_e', [$translator, 'translatePlural'], $functions);
    }
}
