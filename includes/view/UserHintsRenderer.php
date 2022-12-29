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
            $dom_id = md5(microtime() . $icon);

            return '<li class="nav-item nav-item--userhints d-flex align-items-center bg-' . $class_hint . '">'
                . '<a id="' . $dom_id . '" href="#" tabindex="0" class="nav-link">'
                . icon($icon . ' text-white')
                . '<small class="bi bi-caret-down-fill"></small>'
                . '</a>'
                . '<script type="text/javascript">
                        new bootstrap.Popover(document.getElementById(\'' . $dom_id . '\'), {
                            container: \'body\',
                            html: true,
                            content: \'' . addslashes(join('', $this->hints)) . '\',
                            placement: \'bottom\',
                            customClass: \'popover--userhints\'
                        })
                    </script></li>';
        }

        return '';
    }
}
