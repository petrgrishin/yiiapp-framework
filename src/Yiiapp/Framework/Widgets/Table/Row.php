<?php
/**
 *
 *
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Widgets\Table;
use \Yiiapp\Framework\Widgets\Widget;

/**
 * Class Row
 * @package Widgets\Table
 */
class Row extends Widget {

    const VIEW_STATUS_RED = 1;
    const VIEW_STATUS_GREEN = 2;
    const VIEW_STATUS_ORANGE = 3;
    const VIEW_STATUS_BLUE = 4;
    const VIEW_STATUS_GRAY = 5;
    const VIEW_STATUS_WHITE = 6;

    private $fields = array();
    private $actions = array();
    private $id = null;
    private $status;

    private $renderParams = array();
    private $useMenuActions = false;

    public function setFields(array $fields) {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param $list
     * @return $this
     */
    public function setActions($list) {
        $this->actions['list'] = $list;
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setId($value) {
        $this->id = $value;
        return $this;
    }

    /**
     * @param bool $autoGenerate
     * @return null|string
     */
    public function getId($autoGenerate = true) {
        return $this->id;
    }

    public function run() {
        $usesActions = $this->getListActions();
        $menuActions = array();
        foreach ($usesActions as $actionKey => $action) {
            $actionUrl = null;
            if (is_string($actionKey)) {
                $actionUrl = is_array($action) ? $action['url'] : $action;
                !is_array($action) && ($action = $actionKey);
            } else if (!is_array($action)) {
                $actionKey = $action;
            }
            $actionLabel = is_array($action) && $action['name'] ? $action['name'] : $this->renderParams['actionsMap'][$actionKey];
            $actionConfirm = is_array($action) ? $action['confirm'] : null;

            if (!$actionLabel) {
                continue;
            }
            $menuActions[$actionKey] = array(
                'label' => $actionLabel,
                'url' => $actionUrl,
                'confirm' => $actionConfirm
            );
        }

        $this->render("row", array(
            'columns' => $this->renderParams['columns'],
            'status' => $this->renderParams['statusesMap'][$this->status],
            'menuActions' => $menuActions,
            'actions' => $this->getListActions(),
            'fields' => $this->fields,
        ));
        if (!empty($menuActions)) {
            $this->useMenuActions = true;
        }
    }

    /**
     * @return bool
     */
    public function isUseMenuActions() {
        return $this->useMenuActions;
    }

    /**
     * @return array
     */
    public function getColumns() {
        return $this->columns;
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function setColumns($columns) {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @param $name
     * @param $html
     * @return $this
     */
    public function setFieldContent($name, $html) {
        $this->fields[$name] = $html;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setClickAction($name) {
        $this->actions["click"] = $name;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setDbClickAction($name) {
        $this->actions["dbClick"] = $name;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setStatus($name) {
        $this->status = $name;
        return $this;
    }

    /**
     * @param $aFContent
     * @return $this
     */
    public function setFieldsContent($aFContent) {
        foreach ($aFContent as $name => $html) {
            $this->setFieldContent($name, $html);
        }
        return $this;
    }

    /**
     * @param $value
     * @return bool
     */
    private function _isFieldObject($value) {
        return is_object($value) && $value instanceof Field;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasFieldObject($name) {
        return $this->_isFieldObject($this->fields[$name]);
    }

    /**
     * @param $name
     * @return \Widgets\Table\Field
     */
    public function getFieldObject($name) {
        $value = $this->fields[$name];
        if ($this->_isFieldObject($value)) {
            return $value;
        }
        return $this->fields[$name] = new Field($value);
    }

    /**
     * @param $name
     * @return string
     */
    public function getField($name) {
        $value = $this->fields[$name];
        if (!$this->_isFieldObject($value)) {
            return $value;
        }
        /** @var $value Field */
        return $value->toS();
    }

    /**
     * @return array
     */
    public function getListActions() {
        return $this->actions['list'] ? : array();
    }

    /**
     * @return string
     */
    public function getClickAction() {
        return $this->actions['click'] ? : '';
    }

    /**
     * @return string
     */
    public function getDbClickAction() {
        return $this->actions['dbClick'] ? : '';
    }

    /**
     * @param $params
     * @return $this
     */
    public function setRenderParams($params) {
        $this->renderParams = $params;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasStatusColumn() {
        return (boolean)$this->renderParams['statusesMap'];
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        $map = $this->renderParams['statusesMap'];
        return $map[$this->status];
    }

    /**
     * @return bool
     */
    public function hasAction() {
        return (bool)$this->actions['list'];
    }
}
