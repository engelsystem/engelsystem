<?php

namespace Engelsystem\Controllers\Metrics;

use Engelsystem\Renderer\EngineInterface;

class MetricsEngine implements EngineInterface
{
    /** @var string */
    protected $prefix = 'engelsystem_';

    /**
     * Render metrics
     *
     * @param string  $path
     * @param mixed[] $data
     *
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
            $name = $this->prefix . $name;

            if (isset($list['help'])) {
                $return[] = sprintf('# HELP %s %s', $name, $this->escape($list['help']));
                unset($list['help']);
            }

            $type = null;
            if (isset($list['type'])) {
                $type = $list['type'];
                $return[] = sprintf('# TYPE %s %s', $name, $list['type']);
                unset($list['type']);
            }

            $list = (!isset($list['value']) || !isset($list['labels'])) ? $list : [$list];
            foreach ($list as $row) {
                $row = $this->expandData($row);

                if ($type == 'histogram') {
                    $return = array_merge($return, $this->formatHistogram($row, $name));

                    continue;
                }

                $return[] = $this->formatData($name, $row);
            }
        }

        return implode("\n", $return);
    }

    /**
     * @param array  $row
     * @param string $name
     *
     * @return array[]
     */
    protected function formatHistogram(array $row, string $name): array
    {
        $return = [];
        $data = ['labels' => $row['labels']];

        if (!isset($row['value']['+Inf'])) {
            $row['value']['+Inf'] = !empty($row['value']) ? max($row['value']) : 'NaN';
        }
        asort($row['value']);

        foreach ($row['value'] as $le => $value) {
            $return[] = $this->formatData(
                $name . '_bucket',
                array_merge_recursive($data, ['value' => $value, 'labels' => ['le' => $le]])
            );
        }

        $sum = isset($row['sum']) ? $row['sum'] : 'NaN';
        $count = $row['value']['+Inf'];
        $return[] = $this->formatData($name . '_sum', $data + ['value' => $sum]);
        $return[] = $this->formatData($name . '_count', $data + ['value' => $count]);

        return $return;
    }

    /**
     * Expand the value to be an array
     *
     * @param $data
     *
     * @return array
     */
    protected function expandData($data): array
    {
        $data = is_array($data) ? $data : [$data];
        $return = ['labels' => [], 'value' => null];

        if (isset($data['labels'])) {
            $return['labels'] = $data['labels'];
            unset($data['labels']);
        }

        if (isset($data['sum'])) {
            $return['sum'] = $data['sum'];
            unset($data['sum']);
        }

        if (isset($data['value'])) {
            $return['value'] = $data['value'];
            unset($data['value']);
        } else {
            $return['value'] = $data;
        }

        return $return;
    }

    /**
     * @param string      $name
     * @param array|mixed $row
     *
     * @return string
     * @see https://prometheus.io/docs/instrumenting/exposition_formats/
     */
    protected function formatData($name, $row): string
    {
        return sprintf(
            '%s%s %s',
            $name,
            $this->renderLabels($row['labels']),
            $this->renderValue($row['value'])
        );
    }

    /**
     * @param array $labels
     *
     * @return mixed
     */
    protected function renderLabels(array $labels): string
    {
        if (empty($labels)) {
            return '';
        }

        foreach ($labels as $type => $value) {
            $labels[$type] = $type . '="' . $this->formatValue($value) . '"';
        }

        return '{' . implode(',', $labels) . '}';
    }

    /**
     * @param array|mixed $row
     *
     * @return mixed
     */
    protected function renderValue($row)
    {
        if (is_array($row)) {
            $row = array_pop($row);
        }

        return $this->formatValue($row);
    }

    /**
     * @param mixed $value
     *
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
     *
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
     * @param string $path
     *
     * @return bool
     */
    public function canRender(string $path): bool
    {
        return $path == '/metrics';
    }

    /**
     * Does nothing as shared data will only result in unexpected behaviour
     *
     * @param string|mixed[] $key
     * @param mixed          $value
     */
    public function share($key, $value = null): void
    {
    }
}
