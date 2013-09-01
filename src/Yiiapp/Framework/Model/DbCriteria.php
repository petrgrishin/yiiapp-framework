<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */
namespace Yiiapp\Framework\Model;

use CDbCriteria;

class DbCriteria extends CDbCriteria {

    /**
     * @param array $value
     * @return $this
     */
    public function addWith($value = array()) {
        $this->with = array_merge($this->with ? : array(), $value);
        return $this;
    }

    private function _collectCommaString() {
        $params = array_filter(func_get_args());
        return implode(', ', $params);
    }

    /**
     * Добавление данных в выборку
     * @param $value
     * @return $this
     */
    public function addSelect($value) {
        if (!$this->select) {
            $this->select = '*';
        }
        $this->select = $this->_collectCommaString($this->select, $value);
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function addOrder($value) {
        $this->order = $this->_collectCommaString($this->order, $value);
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function addJoin($value) {
        $this->join .= ' ' . $value;
        return $this;
    }

    /**
     * @param $aValue
     * @return $this
     */
    public function addSearchParams(array $aValue) {
        $this->params = array_merge($this->params, $aValue ? : array());
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function addGroup($value) {
        $this->group = $this->_collectCommaString($this->group, $value);
        return $this;
    }

    /**
     * @param string $column
     * @param array $values
     * @param string $operator
     * @return $this|CDbCriteria
     */
    public function addInCondition($column, $values, $operator = 'AND') {
        parent::addInCondition($column, $values, $operator = 'AND');
        return $this;
    }

    /**
     * @param mixed $condition
     * @param string $operator
     * @return $this|CDbCriteria
     */
    public function addCondition($condition, $operator = 'AND') {
        parent::addCondition($condition, $operator);
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function addOffset($value) {
        $this->offset = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function addLimit($value) {
        $this->limit = $value;
        return $this;
    }
}