<?php

namespace Engelsystem;

class ShiftsFilterRenderer {

  /**
   * The shiftFilter to render.
   *
   * @var ShiftsFilter
   */
  private $shiftsFilter;

  /**
   * Should the filter display a day selection.
   *
   * @var boolean
   */
  private $daySelectionEnabled = false;

  /**
   * Days that can be selected.
   * Format Y-m-d
   *
   * @var string[]
   */
  private $days = [];

  public function __construct(ShiftsFilter $shiftsFilter) {
    $this->shiftsFilter = $shiftsFilter;
  }

  /**
   * Renders the filter.
   *
   * @return Generated HTML
   */
  public function render($link_base) {
    $toolbar = [];
    if ($this->daySelectionEnabled && ! empty($this->days)) {
      $selected_day = date("Y-m-d", $this->shiftsFilter->getStartTime());
      $day_dropdown_items = [];
      foreach ($this->days as $day) {
        $day_dropdown_items[] = toolbar_item_link($link_base . '&shifts_filter_day=' . $day, '', $day);
      }
      $toolbar[] = toolbar_dropdown('', $selected_day, $day_dropdown_items, 'active');
    }
    return div('form-group', [
        toolbar_pills($toolbar) 
    ]);
  }

  /**
   * Should the filter display a day selection.
   */
  public function enableDaySelection($days) {
    $this->daySelectionEnabled = true;
    $this->days = $days;
  }

  /**
   * Should the filter display a day selection.
   */
  public function isDaySelectionEnabled() {
    return $this->daySelectionEnabled;
  }
}

?>