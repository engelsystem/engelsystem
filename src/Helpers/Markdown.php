<?php

declare(strict_types=1);

namespace Engelsystem\Helpers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Util\HtmlFilter;

class Markdown
{
    public function render(string $text, bool $allowHtml = false): string
    {
        $renderer = $this->getRenderer($allowHtml);
        $content = $renderer->convert($text)
            ->getContent();
        return rtrim($content, PHP_EOL);
    }

    protected function getRenderer(bool $allowHtml): MarkdownConverter
    {
        $config = [
            'html_input' => $allowHtml ? HtmlFilter::ALLOW : HtmlFilter::ESCAPE,
            'allow_unsafe_links' => false,
            'max_nesting_level' => 42,
            'max_delimiters_per_line' => 42,
            'default_attributes' => [
                Table::class => [
                    'class' => ['table', 'table-striped', 'table-sticky-header', 'data'],
                ],
            ],
            'table' => [
                'alignment_attributes' => [
                    'left' => ['class' => 'text-start'],
                    'center' => ['class' => 'text-center'],
                    'right' => ['class' => 'text-end'],
                ],
            ],
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new DefaultAttributesExtension());

        return new MarkdownConverter($environment);
    }
}
