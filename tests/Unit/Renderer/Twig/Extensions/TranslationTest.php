<?php

namespace Engelsystem\Test\Unit\Renderer\Twig\Extensions;

use Engelsystem\Helpers\Translator;
use Engelsystem\Renderer\Twig\Extensions\Translation;
use PHPUnit\Framework\MockObject\MockObject;
use Twig_Extensions_TokenParser_Trans as TranslationTokenParser;

class TranslationTest extends ExtensionTest
{
    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Translation::__construct
     * @covers \Engelsystem\Renderer\Twig\Extensions\Translation::getFilters
     */
    public function testGeFilters()
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);
        /** @var TranslationTokenParser|MockObject $parser */
        $parser = $this->createMock(TranslationTokenParser::class);

        $extension = new Translation($translator, $parser);
        $filters = $extension->getFilters();

        $this->assertExtensionExists('trans', [$translator, 'translate'], $filters);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Translation::getFunctions
     */
    public function testGetFunctions()
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);
        /** @var TranslationTokenParser|MockObject $parser */
        $parser = $this->createMock(TranslationTokenParser::class);

        $extension = new Translation($translator, $parser);
        $functions = $extension->getFunctions();

        $this->assertExtensionExists('__', [$translator, 'translate'], $functions);
        $this->assertExtensionExists('_e', [$translator, 'translatePlural'], $functions);
    }

    /**
     * @covers \Engelsystem\Renderer\Twig\Extensions\Translation::getTokenParsers
     */
    public function testGetTokenParsers()
    {
        /** @var Translator|MockObject $translator */
        $translator = $this->createMock(Translator::class);
        /** @var TranslationTokenParser|MockObject $parser */
        $parser = $this->createMock(TranslationTokenParser::class);

        $extension = new Translation($translator, $parser);
        $tokenParsers = $extension->getTokenParsers();

        $this->assertTokenParserExists($parser, $tokenParsers);
    }
}
