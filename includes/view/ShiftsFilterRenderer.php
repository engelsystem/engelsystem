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

  private $days = [];

  private $event_config = null;

  public function __construct(ShiftsFilter $shiftsFilter) {
    $this->shiftsFilter = $shiftsFilter;
  }

  /**
   * Renders the filter.
   *
   * @return Generated HTML
   */
  public function render() {
    $toolbar = [];
    if ($this->daySelectionEnabled && ! empty($this->days)) {
      $today = date("Y-m-d");
      $selected_day = date("Y-m-d", $this->shiftsFilter->getStartTime());
      $day_dropdown_items = [];
      foreach ($this->days as $day) {
        $day_dropdown_items[] = toolbar_item_link('', '', $day);
      }
      $toolbar[] = toolbar_dropdown('', $selected_day, $day_dropdown_items, 'active');
    }
    return toolbar_pills($toolbar);
  }

  /**
   * Should the filter display a day selection.
   */
  public function enableDaySelection($days, $event_config) {
    $this->daySelectionEnabled = true;
    $this->days = $days;
    $this->event_config = $event_config;
  }

  /**
   * Should the filter display a day selection.
   */
  public function isDaySelectionEnabled() {
    return $this->daySelectionEnabled;
  }
}

?>