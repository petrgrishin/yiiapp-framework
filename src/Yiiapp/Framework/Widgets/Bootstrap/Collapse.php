<?php
/**
 * Collapse
 * 
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Widgets\Bootstrap;

use Yiiapp\Framework\Widgets\Widget;

class Collapse extends Widget {

    public $class = '';

    public $toggleIcon = true;

    /**
     * Элементы аккордеона
     *
     * array(
     *   array('title' => '', 'content' => '', 'show' => true, 'classLink' => ''),
     *   ...
     * }
     *
     * @var array $items
     */
    public $items = array();

    /**
     * TODO: нужно рефакторить класс, использовать HtmlTag::create()
     *
     * @return mixed|void
     */
    public function run() {
        $this
            ->useViewTemplate('collapse')
            ->selfRender(array(
                'items'       => $this->items,
                'class'       => $this->class,
                'toggleIcon'  => $this->toggleIcon,
            ));
    }
}