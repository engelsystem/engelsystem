<?php

/**
 * Renders a bargraph
 * @param string $key keyname of the x-axis
 * @param array $row_names keynames for the data rows
 * @param unknown $colors colors for the data rows
 * @param unknown $data the data
 */
function bargraph($id, $key, $row_names, $colors, $data) {
  $labels = [];
  foreach ($data as $dataset)
    $labels[] = $dataset[$key];
  
  $datasets = [];
  foreach ($row_names as $row_key => $name) {
    $values = [];
    foreach ($data as $dataset)
      $values[] = $dataset[$row_key];
    $datasets[] = [
        'label' => $name,
        'fillColor' => $colors[$row_key],
        'data' => $values 
    ];
  }
  
  return '<canvas id="' . $id . '" style="width: 100%; height: 300px;"></canvas>
      <script type="text/javascript">
      $(function(){
        var ctx = $("#' . $id . '").get(0).getContext("2d");
        var chart = new Chart(ctx).Bar(' . json_encode([
      'labels' => $labels,
      'datasets' => $datasets 
  ]) . ');
      });
      </script>';
}

?>