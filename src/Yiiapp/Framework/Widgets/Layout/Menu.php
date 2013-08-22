<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace Yiiapp\Framework\Widgets\Layout;
use Yiiapp\Framework\Widgets\Widget;

class Menu extends Widget {

    private $items = array();
    private $depthItems = array();

    /**
     * @param string $route
     * @return string
     */
    private function createUrl($route) {
        return \Yii::app()->urlManager->createUrl($route);
    }

    /**
     * @param $value
     */
    public function setItems($value) {
        $this->items = $value;
    }

    public function init() {
        $this->createItemsDepth($this->items);
    }

    private function createItemsDepth($items, $level = 1) {
        foreach ($items as $name => $item) {
            $url = $item;
            if (is_array($item)) {
                $url = '';
                if ($item['url']) {
                    $url = $item['url'];
                    unset($item['url']);
                }
            }
            $this->depthItems[] = array(
                'level' => $level,
                'url' => $url ? $this->createUrl($url) : '',
                'name' => $name
            );
            if (is_array($item)) {
                $this->createItemsDepth($item, $level + 1);
            }
        }
    }

    public function run() {
        $this->useViewTemplate('menu')->selfRender(array('items' => $this->depthItems));
    }
}
