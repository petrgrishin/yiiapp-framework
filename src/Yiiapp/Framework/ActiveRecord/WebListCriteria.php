<?php
/**
 * @author: Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\ActiveRecord;

use CActiveRecord;
use CDbCriteria;
use CException;
use CPagination;
use Yii;
use Yiiapp\Framework\Util\DateTime;

class WebListCriteria extends \CComponent {
    const DEFAULT_PAGE_COUNT = 10;

    private $key;
    private $filterMeta;
    private $usingPagination;

    /**
     * @return CHttpSession
     */
    private function getSession() {
        return Yii::app()->session;
    }

    public function init() {
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    private function setSessionValue($key, $value) {
        $this->getSession()->add($this->getKey($key), $value);
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    private function getSessionValue($key) {
        return $this->getSession()->get($this->getKey($key));
    }

    /**
     * @param null $value
     * @return mixed
     */
    public function sortParams($value = null) {
        $value = $value ? : $this->getSessionValue('sort');
        $this->setSessionValue('sort', $value);
        return $value;
    }

    /**
     * @param $value array|CPagination|null
     * @internal param $key
     * @return array
     */
    public function pagerParams($value = null) {
        if (!$value) {
            return $this->getSessionValue('pager');
        }
        if (is_object($value) && $value instanceof CPagination) {
            $pagination = $value;
            $value = $this->getSessionValue('pager');
            $pagination->setCurrentPage($value['number'] ? : 0);
            $pagination->setPageSize($value['count'] ? : static::DEFAULT_PAGE_COUNT);
        } else {
            $this->setSessionValue('pager', $value);
        }
        return $value;
    }

    /**
     * @param null $data
     * @return mixed
     */
    public function filterData($data = null) {
        $data = is_array($data) ? $data : $this->getSessionValue('filter');
        $this->setSessionValue('filter', $data);
        return $data;
    }

    /**
     * @param  [] $metaData
     * <code>
     * array(
    'fieldName' => 'fieldType'
     * );
     * </code>
     * @return $this
     */
    public function setupFilterMeta($metaData) {
        $this->filterMeta = $metaData;
        return $this;
    }

    /**
     * @throws CException
     * @return array
     */
    private function getFilterMeta() {
        if (is_null($this->filterMeta)) {
            throw new CException('filter meta information not exists');
        }
        return $this->filterMeta;
    }

    /**
     * @param CDbCriteria $criteria
     * @param CActiveRecord $model
     * @param bool $throw
     * @return CDbCriteria
     */
    public function fillDbCriteria(CDbCriteria $criteria, CActiveRecord $model, $throw = true) {
        $this
            ->fillFilterCriteria($criteria, $throw)
            ->fillPagerCriteria($criteria, $model)
            ->fillSortCriteria($criteria);
        return $criteria;
    }

    /**
     * @param $postfix
     * @return string
     * @throws CException
     */
    public function getKey($postfix) {
        if (!$this->key) {
            throw new CException("Key not exists");
        }

        return $this->key . '_' . $postfix;
    }

    /**
     * @param $key
     * @return $this
     * @throws CException
     */
    public function useStoreKey($key) {
        if (!$key) {
            throw new CException("Key param for list criteria is empty");
        }
        $this->key = $key;

        return $this;
    }

    /**
     * @param CDbCriteria $criteria
     * @param bool $throw
     * @throws CException
     * @return WebListCriteria
     */
    public function fillFilterCriteria(CDbCriteria $criteria, $throw = true) {
        $meta = $this->getFilterMeta();
        $data = $this->filterData() ? : array();

        foreach ($data as $name => $value) {
            if ($throw && !isset($meta[$name])) {
                throw new CException('in meta information no params by field `' . $name . '`');
            }
            $fMeta = $meta[$name];
            switch ($fMeta) {
                case 'like':
                    $criteria->addSearchCondition($name, $value);
                    break;
                case 'equal':
                    // TODO: создать тест
                    $qName = ':' . $name;
                    $criteria->addCondition($name . ' = ' . $qName);
                    $criteria->params[$qName] = $value;
                    break;
                case 'range':
                    // TODO: создать тест
                    $qNameFrom = ':' . $name . '_from';
                    $qNameTo = ':' . $name . '_to';

                    $value[0] && ($criteria->addCondition($name . ' >= ' . $qNameFrom)->params[$qNameFrom] = $value[0]);
                    $value[1] && ($criteria->addCondition($name . ' <= ' . $qNameTo)->params[$qNameTo] = $value[1]);
                    break;
                case 'in':
                    if (!empty($value)) {
                        $criteria->addInCondition($name, $value);
                    }
            }
        }
        return $this;
    }

    /**
     * @return CPagination
     * @throws CException
     */
    public function getUsingPagination() {
        if (is_null($this->usingPagination)) {
            throw new CException('pagination object not exists');
        }
        return $this->usingPagination;
    }

    /**
     * @param CDbCriteria $criteria
     * @param CActiveRecord $model
     * @return WebListCriteria
     */
    public function fillPagerCriteria(CDbCriteria $criteria, CActiveRecord $model) {
        $count = $model->count($criteria);
        $this->usingPagination = new CPagination($count);
        $this->pagerParams($this->usingPagination);
        $this->usingPagination->applyLimit($criteria);
        return $this;
    }

    /**
     * @param CDbCriteria $criteria
     * @return WebListCriteria
     */
    public function fillSortCriteria(CDbCriteria $criteria) {
        if (($params = $this->sortParams()) && isset($params['order']) && isset($params['by'])) {
            $criteria->order = $params['by'] . ' ' . $params['order'];
        }
        return $this;
    }

    /**
     *
     * @param array $data
     * @return array|$this
     */
    public function filterOptions($data = null) {
        if (!is_null($data)) {
            $this->setSessionValue('filterOptions', $data);
            return $data;
        } else {
            return $this->getSessionValue('filterOptions');
        }

    }

    /**
     * @param $sizes
     * @return array
     */
    public function pageSizes(array $sizes) {
        return array_merge(array(static::DEFAULT_PAGE_COUNT), $sizes);
    }
}