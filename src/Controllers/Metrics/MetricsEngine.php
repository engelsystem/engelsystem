<?php

declare(strict_types=1);

namespace Engelsystem\Controllers\Metrics;

use Engelsystem\Renderer\EngineInterface;

class MetricsEngine implements EngineInterface
{
    protected string $prefix = 'engelsystem_';

    /**
     * Render metrics
     *
     * @param mixed[] $data
     *
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

        return implode("\n", $return) . "\n";
    }

    /**
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
     */
    protected function expandData(mixed $data): array
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
     *
     * @see https://prometheus.io/docs/instrumenting/exposition_formats/
     */
    protected function formatData(string $name, mixed $row): string
    {
        return sprintf(
            '%s%s %s',
            $name,
            $this->renderLabels($row['labels']),
            $this->renderValue($row['value'])
        );
    }

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

    protected function renderValue(mixed $row): mixed
    {
        if (is_array($row)) {
            $row = array_pop($row);
        }

        return $this->formatValue($row);
    }

    protected function formatValue(mixed $value): mixed
    {
        if (is_bool($value)) {
            return (int) $value;
        }

        return $this->escape($value);
    }

    protected function escape(mixed $value): mixed
    {
        $replace = [
            '\\' => '\\\\',
            '"'  => '\\"',
            "\n" => '\\n',
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            (string) $value
        );
    }

    public function canRender(string $path): bool
    {
        return $path == '/metrics';
    }

    /**
     * Does nothing as shared data will only result in unexpected behaviour
     *
     * @param string|mixed[] $key
     */
    public function share(string|array $key, mixed $value = null): void
    {
    }
}
