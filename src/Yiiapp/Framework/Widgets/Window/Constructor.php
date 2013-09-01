<?php
/**
 *
 *
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Widgets\Window;

use Yiiapp\Framework\Widgets\Widget;

class Constructor extends Widget {
    private $_buffered = false;

    public function init() {
    }

    public function start() {
        ob_start();
        $this->_buffered = true;
        return $this;
    }

    public function run() {
        if ($this->_buffered) {
            $this->content = ob_get_clean();
        }
        $this->useViewTemplate('constructor')->selfRender(array());
    }
}