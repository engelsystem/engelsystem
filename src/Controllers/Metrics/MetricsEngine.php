<?php

namespace Engelsystem\Controllers\Metrics;

use Engelsystem\Renderer\EngineInterface;

class MetricsEngine implements EngineInterface
{
    /**
     * Render metrics
     *
     * @param string  $path
     * @param mixed[] $data
     * @return string
     *
     * @example $data = ['foo' => [['labels' => ['foo'=>'bar'], 'value'=>42]], 'bar'=>123]
     */
    public function get(string $path, array $data = []): string
    {
        $return = [];
        foreach ($data as $name => $list) {
            if (is_int($name)) {
                $return[] = '# ' . $this->escape($list);
                continue;
            }

            $list = is_array($list) ? $list : [$list];
            $name = 'engelsystem_' . $name;

            if (isset($list['help'])) {
                $return[] = sprintf('# HELP %s %s', $name, $this->escape($list['help']));
                unset($list['help']);
            }

            if (isset($list['type'])) {
                $return[] = sprintf('# TYPE %s %s', $name, $list['type']);
                unset($list['type']);
            }

            $list = (!isset($list['value']) || !isset($list['labels'])) ? $list : [$list];
            foreach ($list as $row) {
                $row = is_array($row) ? $row : [$row];

                $return[] = $this->formatData($name, $row);
            }
        }

        return implode("\n", $return);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function canRender(string $path): bool
    {
        return $path == '/metrics';
    }

    /**
     * @param string      $name
     * @param array|mixed $row
     * @return string
     * @see https://prometheus.io/docs/instrumenting/exposition_formats/
     */
    protected function formatData($name, $row): string
    {
        return sprintf(
            '%s%s %s',
            $name,
            $this->renderLabels($row),
            $this->renderValue($row)
        );
    }

    /**
     * @param array|mixed $row
     * @return mixed
     */
    protected function renderLabels($row): string
    {
        $labels = [];
        if (!is_array($row) || empty($row['labels'])) {
            return '';
        }

        foreach ($row['labels'] as $type => $value) {
            $labels[$type] = $type . '="' . $this->formatValue($value) . '"';
        }

        return '{' . implode(',', $labels) . '}';
    }

    /**
     * @param array|mixed $row
     * @return mixed
     */
    protected function renderValue($row)
    {
        if (isset($row['value'])) {
            return $this->formatValue($row['value']);
        }

        return $this->formatValue(array_pop($row));
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function formatValue($value)
    {
        if (is_bool($value)) {
            return (int)$value;
        }

        return $this->escape($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function escape($value)
    {
        $replace = [
            '\\' => '\\\\',
            '"'  => '\\"',
            "\n" => '\\n',
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $value
        );
    }

    /**
     * Does nothing as shared data will onyly result in unexpected behaviour
     *
     * @param string|mixed[] $key
     * @param mixed          $value
     */
    public function share($key, $value = null) { }
}
