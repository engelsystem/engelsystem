<?php

declare(strict_types=1);

namespace Engelsystem\Renderer;

use Illuminate\Support\Str;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader as FilesystemLoader;
use Twig\Node\EmptyNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class ExtendsTokenParser extends AbstractTokenParser
{
    public function __construct(protected FilesystemLoader $loader, protected string $basePath)
    {
    }

    /**
     * Allows multi-inheritance by searching for the file to be extended in views and views of all plugins
     */
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        if ($this->parser->peekBlockStack() || !$this->parser->isMainScope()) {
            throw new SyntaxError(
                'Cannot use "extend" in a block or macro.',
                $token->getLine(),
                $stream->getSourceContext(),
            );
        }

        $parent = $this->parser->parseExpression();
        $templateFile = $stream->getSourceContext()->getPath();
        $extendsFile = $parent->getAttribute('value');

        $extendsFile = $this->getNextParentFile($templateFile, $extendsFile);

        $parent->setAttribute('value', $extendsFile);

        $this->parser->setParent($parent);

        $stream->expect(Token::BLOCK_END_TYPE);

        return new EmptyNode($token->getLine());
    }

    public function getTag(): string
    {
        return 'extends';
    }

    protected function getNextParentFile(string $templateFile, string $extendsFile): string
    {
        $found = false;
        foreach ($this->loader->getPaths() as $path) {
            // Ignore file itself
            if (Str::startsWith($templateFile, $path . '/')) {
                $found = true;
                continue;
            }

            // Skip all paths "above" the currently extending file
            if (!$found) {
                continue;
            }

            // Use file if it is loadable
            $parentFilePath = Str::replaceStart($this->basePath . '/', '', $path . '/' . $extendsFile);
            if ($this->loader->exists($parentFilePath)) {
                return $parentFilePath;
            }
        }

        return $extendsFile;
    }
}
