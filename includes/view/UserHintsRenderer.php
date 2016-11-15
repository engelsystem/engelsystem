<?php

namespace Engelsystem;

class UserHintsRenderer {

  private $hints = [];

  private $important = false;

  /**
   * Render the added hints to a popover for the toolbar.
   */
  public function render() {
    if (count($this->hints) > 0) {
      $hint_class = $this->important ? 'danger' : 'info';
      $glyphicon = $this->important ? 'warning-sign' : 'info-sign';
      
      return toolbar_popover($glyphicon . ' text-' . $hint_class, '', $this->hints, 'bg-' . $hint_class);
    }
    
    return '';
  }

  /**
   * Add a hint to the list, if its not null and a not empty string.
   *
   * @param string $hint
   *          The hint
   * @param boolean $important
   *          Is the hint important?
   */
  public function addHint($hint, $important = false) {
    if ($hint != null && $hint != '') {
      if ($important) {
        $this->important = true;
        $this->hints[] = error($hint, true);
      } else {
        $this->hints[] = info($hint, true);
      }
    }
  }

  /**
   * Get all hints.
   */
  public function getHints() {
    return $this->hints;
  }

  /**
   * Are there important hints? This leads to a more intensive icon.
   */
  public function isImportant() {
    return $this->important;
  }
}

?>