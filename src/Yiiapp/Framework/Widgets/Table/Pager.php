<?php
namespace Yiiapp\Framework\Widgets\Table;

use CHtml;
use TbPager;
use Yiiapp\Framework\Util\HtmlTag;

class Pager extends TbPager {
    public $pagerPageSizesClass = "pageSizes";

    public $pageSizes = array(
        10, 50, 100
    );

    public function run(){
        $this->registerClientScript();
        $buttons=$this->createPageButtons();

        echo $this->header;
        echo $this->createSelectedItemsCount();
        if(!empty($buttons)) {
            echo CHtml::tag('ul',$this->htmlOptions,implode("\n",$buttons));
        }
        echo $this->createPageSizes();
        echo $this->footer;
    }

    /**
     * @return array
     */
    public function getPageSizes() {
        return $this->pageSizes;
    }

    /**
     * @param array $pageSizes
     * @return $this
     */
    public function setPageSizes($pageSizes) {
        $this->pageSizes = $pageSizes;
        return $this;
    }

    /**
     * @return string
     */
    protected function createPageSizes() {
        $options = array();

        $min = min($this->getPageSizes());

        if ($min > $this->pages->getItemCount()) {
            return '';
        }


        foreach($this->getPageSizes() as $size) {
            $options[] = CHtml::tag("option", array(
                "value" => $size,
                "selected" => ($this->pageSize == $size) ? "selected" : ""
            ), $size);
        }

        return CHtml::tag("div",
            array(
                "class" => $this->getPagerPageSizesClass() . " pull-right"
            ),
            CHtml::tag("select", array(
                'data-page' => $this->getCurrentPage()
            ), implode("\n", $options))
        );
    }

    /**
     * @return string
     */
    public function getPagerPageSizesClass() {
        return $this->pagerPageSizesClass;
    }

    /**
     * @param string $pagerPageSizesClass
     * @return $this
     */
    public function setPagerPageSizesClass($pagerPageSizesClass) {
        $this->pagerPageSizesClass = $pagerPageSizesClass;
        return $this;
    }

    /**
     * @param string $label
     * @param int $page
     * @param string $class
     * @param bool $hidden
     * @param bool $selected
     * @return string
     */
    protected function createPageButton($label, $page, $class, $hidden, $selected) {
        if ($hidden || $selected) {
            $class .= ' ' . ($hidden ? 'disabled' : 'active');
        }

        return CHtml::tag('li', array('class' => $class), CHtml::link($label, "#", array(
            "data-page" => $page,
            "data-count"=> $this->getPageSize()
        )));
    }

    public function createSelectedItemsCount() {
        return HtmlTag::create("div")
            ->addClass('clear')
            ->setContent("Колличество элементов: " . $this->pages->getItemCount())->toS();
    }
}
