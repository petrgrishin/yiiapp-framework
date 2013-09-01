<?php
/**
 *
 *
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Widgets\Table;

use CPagination;
use Yiiapp\Framework\Widgets\Table\Row;
use Yiiapp\Framework\Util\UrlBuilder;
use Yiiapp\Framework\View\View;
use Yiiapp\Framework\Widgets\Widget;

class Constructor extends Widget {

    public $columns = array();
    public $order = "";
    public $by = "";
    /** @var CPagination */
    public $pager;
    public $enablePagination = true;
    public $pagerCssClass = "pagination";
    public $pagerPageSizesClass = "pageSizes";
    public $pageSizes = array();

    private $urls = array();

    private
        $statusesMap = array(),
        $itemsActionsMap = array();

    /**
     * @var Row[]
     */
    private $rows = array();

    /**
     * @return array
     */
    public function getStatusesMap() {
        return $this->statusesMap;
    }

    protected function onBeforeRenderer(View $view) {
        foreach ($this->rows as $row) {
            $view->injectWidget('rows[]', $row);
        }
        return true;
    }

    public function run() {
        foreach ($this->rows as $row) {
            $row->setRenderParams(array(
                'columns' => $this->columns,
                'statusesMap' => $this->statusesMap,
                'actionsMap' => $this->itemsActionsMap
            ));
        }

        $urls = array();
        foreach ($this->urls as $uName => $url) {
            $urls[$uName] = $url instanceof UrlBuilder ? $url->toS() : $url;
        }

        $this
            ->useViewTemplate('layout')
            ->selfRender(array(
                'columns' => $this->columns,
                'rows' => $this->rows,
                'order' => $this->order,
                'urls' => $urls,
                'by' => $this->by,
                'enablePagination' => $this->enablePagination,
                'pagerCssClass' => $this->pagerCssClass,
                'pager' => $this->pager,
                'pageSizes' => $this->pageSizes,
                'pagerPageSizesClass' => $this->pagerPageSizesClass,
            ));
    }

    public function setColumns(array $columns) {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @param $id
     * @param $fields
     * @return Row
     */
    public function addRow($id, $fields) {
        $this->rows[] = $row = new Row();
        $row->setId($id)->setFields($fields);
        return $row;
    }

    /**
     * @return string
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * @param string $order
     * @return $this
     */
    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string
     */
    public function getBy() {
        return $this->by;
    }

    /**
     * @param string $by
     * @return $this
     */
    public function setBy($by) {
        $this->by = $by;
        return $this;
    }

    /**
     * @return \CPagination
     */
    public function getPager() {
        return $this->pager;
    }

    /**
     * @param \CPagination $pagination
     * @return $this
     */
    public function setPager(CPagination $pagination) {
        $this->pager = $pagination;
        return $this;
    }

    /**
     * @return array
     */
    public function getPageSizes() {
        return $this->pager->pageCount;
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
     * @param $aActions
     * @return $this
     */
    public function setItemsActions($aActions) {
        $this->itemsActionsMap = $aActions;
        return $this;
    }

    /**
     * @param $map
     * @return $this
     */
    public function setStatuses($map) {
        $this->statusesMap = $map;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setUrls($value) {
        $this->urls = $value;
        return $this;
    }
}
