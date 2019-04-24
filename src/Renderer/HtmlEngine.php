<?php

namespace Engelsystem\Renderer;

class HtmlEngine implements EngineInterface
{
    /**
     * Render a template
     *
     * @param string  $path
     * @param mixed[] $data
     * @return string
     */
    public function get($path, $data = [])
    {
        $template = file_get_contents($path);
        if (is_array($data)) {
            foreach ($data as $name => $content) {
                $template = str_replace('%' . $name . '%', $content, $template);
            }
        }

        return $template;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function canRender($path)
    {
        return mb_strpos($path, '.htm') !== false && file_exists($path);
    }
}
