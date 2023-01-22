<?php

namespace Engelsystem;

class UserHintsRenderer
{
    /** @var string[] */
    private $hints = [];

    private $important = false;

    /**
     * Add a hint to the list, if its not null and a not empty string.
     *
     * @param string  $hint      The hint
     * @param boolean $important Is the hint important?
     */
    public function addHint($hint, $important = false)
    {
        if (!empty($hint)) {
            if ($important) {
                $this->important = true;
                $this->hints[] = error($hint, true);
            } else {
                $this->hints[] = info($hint, true);
            }
        }
    }

    /**
     * Render the added hints to a popover for the toolbar.
     *
     * @return string
     */
    public function render()
    {
        if (count($this->hints) > 0) {
            $class_hint = $this->important ? 'danger' : 'info';
            $icon = $this->important ? 'exclamation-triangle' : 'info-circle';
            $data_bs_attributes = [
                'toggle'       => 'popover',
                'container'    => 'body',
                'placement'    => 'bottom',
                'custom-class' => 'popover--userhints',
                'html'         => 'true',
                'content'      => htmlspecialchars(join('', $this->hints)),
            ];
            $attr = '';
            foreach ($data_bs_attributes as $attr_key => $attr_value) {
                $attr .= ' data-bs-' . $attr_key . '="' . $attr_value . '"';
            }

            return '<li class="nav-item nav-item--userhints d-flex align-items-center bg-' . $class_hint . '">'
                . '<a class="nav-link dropdown-toggle text-light" href="#" role="button"' . $attr . '>'
                . icon($icon)
                . '</a>'
                . '</li>';
        }

        return '';
    }
}
