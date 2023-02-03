<?php

declare(strict_types=1);

namespace Engelsystem\Renderer;

class HtmlEngine extends Engine
{
    /**
     * Render a template
     *
     * @param mixed[] $data
     */
    public function get(string $path, array $data = []): string
    {
        $data = array_replace_recursive($this->sharedData, $data);
        $template = file_get_contents($path);

        if (is_array($data)) {
            foreach ($data as $name => $content) {
                $template = str_replace('%' . $name . '%', $content, $template);
            }
        }

        return $template;
    }

    public function canRender(string $path): bool
    {
        return mb_strpos($path, '.htm') !== false && file_exists($path);
    }
}
