<?php

/**
 * Renders a bargraph
 *
 * @param string $dom_id
 * @param string $key       key name of the x-axis
 * @param array  $row_names key names for the data rows
 * @param array  $colors    colors for the data rows
 * @param array  $data      the data
 * @return string
 */
function bargraph($dom_id, $key, $row_names, $colors, $data)
{
    $labels = [];
    foreach ($data as $dataset) {
        $labels[] = $dataset[$key];
    }

    $datasets = [];
    foreach ($row_names as $row_key => $name) {
        $values = [];
        foreach ($data as $dataset) {
            $values[] = $dataset[$row_key];
        }
        $datasets[] = [
            'label'     => $name,
            'backgroundColor' => $colors[$row_key],
            'data'      => $values
        ];
    }

    return '<canvas id="' . $dom_id . '" style="width: 100%; height: 300px;"></canvas>
      <script type="text/javascript">
      $(function(){
        var ctx = $(\'#' . $dom_id . '\').get(0).getContext(\'2d\');
        var chart = new Chart(ctx, ' . json_encode([
            'type'     => 'bar',
            'data'     => [
                'labels'   => $labels,
                'datasets' => $datasets
            ]
        ]) . ');
      });
      </script>';
}
