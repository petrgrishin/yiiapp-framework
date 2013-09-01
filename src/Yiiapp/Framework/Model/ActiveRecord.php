<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Model;
use BadMethodCallException;
use CActiveRecord;
use CBelongsToRelation;
use CConsoleApplication;
use CException;
use CModelEvent;
use Exception;
use Yii;
use Yiiapp\Framework\Model\DbCriteria;

/**
 * Class ActiveRecord
 *
 * @method boolean saveWithRelated($relations) Сохраняет объект с его множественными связями, указанными $relations
 * @method boolean saveRelated($relations) Сохраняет множественные связи объекта, указанные в $relations
 * @method null initModel() Подготовка модели поиска
 * @property WithRelatedBehavior $withRelated
 */
class ActiveRecord extends CActiveRecord {
    /**
     * Поле модели в котором хранятся произвольные данные
     */
    const EXTRA_ATTRIBUTES_STORAGE = 'data';

    /**
     * Алиас для родительской таблицы при склейке
     */
    const PARENT_TABLE_ALIAS = 't';

    /**
     * @var Флаг новой, вставленной в таблицу записи (не update)
     */
    private $inserted = false;

    /**
     * Произвольные данные в виде массива
     *
     * @var array
     */
    private $extraAttributes = null;

    public function tableName() {
        return $this->createTableName(get_class($this));
    }

    /**
     * Добавляем поведения
     *
     * @return array
     */
    public function behaviors() {
        return array(
            'datetime' => array(
                'class' => '\Yiiapp\Framework\Behavior\ActiveRecord\DateTime',
            ),
            'withRelated' => array(
                'class' => 'WithRelatedBehavior'
            )
        );
    }

    /**
     * Простая реализация доступа к хранилищу произвольных данных
     *
     * @param string $name
     * @return mixed|void
     */
    public function __get($name) {
        try {
            try {
                return $this->callGetter($name);
            } catch (BadMethodCallException $e) {
                return parent::__get($name);
            }
        } catch (CException $e) {
            if ($this->hasExtraAttribute($name)) {
                $this->_loadExtraAttributes();
                if (is_array($this->extraAttributes) && array_key_exists($name, $this->extraAttributes)) {
                    return $this->extraAttributes[$name];
                } else {
                    return null;
                }
            }
        }

    }

    /**
     * Простая реализация установки данных в произвольное хранилище
     *
     * @param string $name
     * @param mixed $value
     * @throws CException|Exception
     * @return bool|mixed|void
     */
    public function __set($name, $value) {
        try {
            try {
                return $this->callSetter($name, $value);
            } catch (BadMethodCallException $e) {
                return parent::__set($name, $value);
            }
        } catch (CException $e) {
            if ($this->hasExtraAttribute($name)) {

                $this->_loadExtraAttributes();

                return $this->extraAttributes[$name] = $value;
            }
            throw $e;
        }
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     * @throws BadMethodCallException
     */
    private function callSetter($name, $value) {
        $method = 'set' . $name;
        if (method_exists($this, $method)) {
            return $this->$method($value);
        } else {
            throw new BadMethodCallException('method not exists');
        }
    }

    private function callGetter($name) {
        $method = 'get' . $name;
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            throw new BadMethodCallException('method not exists');
        }
    }

    /**
     * Проверяет является ли свойство хранимым в произвольном хранилище
     *
     * @param $name
     * @return bool
     */
    public function hasExtraAttribute($name) {
        return !$this->hasProperty($name) && !$this->getMetaData()->hasRelation($name) && !$this->hasAttribute($name) && $this->hasAttribute(self::EXTRA_ATTRIBUTES_STORAGE);
    }

    /**
     * Получить массив произвольных данных
     *
     * @return array|null
     */
    public function getExtraAttributes() {
        $this->_loadExtraAttributes();
        return $this->extraAttributes;
    }

    /**
     * Задать массив произвольных данных
     *
     * @param $attributes
     */
    public function setExtraAttributes($attributes) {
        $this->extraAttributes = $attributes;
    }

    /**
     * Получить именованное значение массива произвольных данных
     *
     * @param string $name
     * @return mixed
     */
    public function getExtraAttribute($name) {
        $this->_loadExtraAttributes();
        return $this->extraAttributes[$name];
    }

    /**
     * Задать именованное значение массива произвольных данных
     *
     * @param string $name
     * @param $value
     */
    public function setExtraAttribute($name, $value) {
        $this->_loadExtraAttributes();
        $this->extraAttributes[$name] = $value;
    }

    /**
     * Загружает произвольные данные из json в массив
     */
    private function _loadExtraAttributes() {
        if (!is_array($this->extraAttributes)) {
            $this->extraAttributes = json_decode($this->{self::EXTRA_ATTRIBUTES_STORAGE}, true) ? : array();
            $event = new CModelEvent($this);
            if ($this->hasEventHandler('onLoadExtraAttributes')) {
                $this->onLoadExtraAttributes($event);
            }
        }
    }

    protected function onLoadExtraAttributes($event) {
        $this->raiseEvent("onLoadExtraAttributes", $event);
    }


    /**
     * Сохраняет произвольные данные в json
     */
    private function _saveExtraAttribute() {
        if ($this->extraAttributes) {
            $event = new CModelEvent($this);
            if ($this->hasEventHandler('onLoadExtraAttributes')) {
                $this->onSaveExtraAttributes($event);
            }
            $this->{self::EXTRA_ATTRIBUTES_STORAGE} = json_encode($this->extraAttributes);
        }
    }

    protected function onSaveExtraAttributes($event) {
        $this->raiseEvent("onSaveExtraAttributes", $event);
    }

    /**
     * Загрузка данных в модель
     *
     * @param array $attributes
     */
    public function loadAttributes(array $attributes) {
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param $event
     */
    protected function onBeforeInsert($event) {
        $this->raiseEvent('onBeforeInsert', $event);
    }

    /**
     * Хука обработчика вставки строки в таблицу
     */
    protected function beforeInsert() {
        if ($this->hasEventHandler('onBeforeInsert')) {
            $event = new CModelEvent($this);
            $this->onBeforeInsert($event);
            return $event->isValid;
        }
        return true;
    }

    /**
     * @param $event
     */
    protected function onBeforeUpdate($event) {
        $this->raiseEvent('onBeforeUpdate', $event);
    }

    /**
     * @return bool
     */
    protected function beforeUpdate() {
        if ($this->hasEventHandler('onBeforeUpdate')) {
            $event = new CModelEvent($this);
            $this->onBeforeUpdate($event);
            return $event->isValid;
        }
        return true;
    }

    /**
     * Обработчик модели перед сохранением в базу данных
     *
     * @return bool
     */
    protected function beforeSave() {

        if ($this->isNewRecord) {
            $return = $this->beforeInsert();
            if (!is_null($return) && !$return) {
                return false;
            }
            $this->inserted = true;
        } else {
            $return = $this->beforeUpdate();
            if (!is_null($return) && !$return) {
                return false;
            }
        }

        if ($this->hasAttribute(self::EXTRA_ATTRIBUTES_STORAGE)) {
            $this->_saveExtraAttribute();
        }

        return parent::beforeSave();
    }

    /**
     * @param $event
     */
    protected function onAfterInsert($event) {
        $this->raiseEvent('onAfterInsert', $event);
    }

    /**
     * @return bool
     */
    protected function afterInsert() {
        if ($this->hasEventHandler('onAfterInsert')) {
            $event = new CModelEvent($this);
            $this->onAfterInsert($event);
            return $event->isValid;
        }

        if (Yii::app() instanceof CConsoleApplication) {
            echo 'Insert `' . get_class($this) . '` with id `' . $this->id . "`\n";
        }

        return true;
    }

    /**
     * @param $event
     */
    protected function onAfterUpdate($event) {
        $this->raiseEvent('onAfterUpdate', $event);
    }

    /**
     * @return bool
     */
    protected function afterUpdate() {
        if ($this->hasEventHandler('onAfterUpdate')) {
            $event = new CModelEvent($this);
            $this->onAfterInsert($event);
            return $event->isValid;
        }

        if (Yii::app() instanceof CConsoleApplication) {
            echo 'Update `' . get_class($this) . '` with id `' . $this->id . "`\n";
        }

        return true;
    }

    protected function afterSave() {
        parent::afterSave();

        if ($this->inserted) {
            $return = $this->afterInsert();
            if (!is_null($return) && !$return) {
                return false;
            }
        } else {
            $return = $this->afterUpdate();
            if (!is_null($return) && !$return) {
                return false;
            }
        }
    }

    /**
     * Получаем атрибуты без хранилища экстра атрибутов
     *
     * @param bool $names
     * @return array
     */
    public function getAttributesWithoutExtraStorage($names = true) {
        $ret = $this->getAttributes($names);
        if ($this->hasAttribute(self::EXTRA_ATTRIBUTES_STORAGE)) {
            unset ($ret[self::EXTRA_ATTRIBUTES_STORAGE]);
        }
        return $ret;
    }

    /**
     *  Смешивает незаполненные элементы первого массива
     */
    public function mergeNullAttributes($attributes, $safeOnly = false) {
        $resultAttributes = $this->attributes;
        $isNullOrEmptyStr = function ($value) {
            return is_null($value) || $value === '';
        };
        foreach ($attributes as $attrName => $attrValue) {
            if ($isNullOrEmptyStr($resultAttributes[$attrName]) && !$isNullOrEmptyStr($attrValue)) {
                $resultAttributes[$attrName] = $attrValue;
            }
        }

        $this->setProperties($resultAttributes, $safeOnly);
    }

    /**
     * @param array $values
     * @param bool $safeOnly
     */
    public function setAttributes($values, $safeOnly = true) {
        if (!is_array($values)) {
            return;
        }
        $attributes = array_flip($safeOnly ? $this->getSafeAttributeNames() : $this->attributeNames());

        foreach ($values as $name => $value) {
            if (isset($attributes[$name])) {
                $this->setAttribute($name, $value);
            } elseif ($safeOnly) {
                $this->onUnsafeAttribute($name, $value);
            }
        }
    }

    /**
     * Установка свойств! (НЕ ОБЯЗАТЕЛЬНО АТРИБУТОВ)
     * @param $values
     * @param bool $safeOnly
     */
    public function setProperties($values, $safeOnly = true) {
        if (!is_array($values)) {
            return;
        }

        $excludedProps = array();
        if ($safeOnly) {
            $excludedProps = array_diff($this->attributeNames(), $this->getSafeAttributeNames());
        }

        foreach ($values as $property => $value) {
            if (in_array($property, $excludedProps)) {
                continue;
            }
            $this->{$property} = $value;
        }
    }

    /**
     * Метод по умолчанию возвращающий экземпляр модели
     *
     * @param null|string $name
     * @return $this
     */
    static public function model($name = null) {
        $model = parent::model($name ? : get_called_class());
        method_exists($model, 'initModel') && $model->initModel();
        return $model;
    }

    /**
     * @param $className
     * @return string
     */
    protected function createTableName($className) {
        return strtolower(str_replace('\\', '', preg_replace('/(.)([A-Z])/', '$1_$2', $className)));
    }

    /**
     * @param bool $createIfNull
     * @return DbCriteria|null
     */
    public function getDbCriteria($createIfNull = true) {
        $criteria = parent::getDbCriteria($createIfNull);
        if (!$criteria) {
            return null;
        }
        if (!$criteria instanceof DbCriteria) {
            $instance = new DbCriteria();
            $instance->mergeWith($criteria);
            $this->setDbCriteria($instance);
            $criteria = $instance;
        }
        return $criteria;
    }

    /**
     * @param string $relationName  Имя связи
     * @param object|string|[] $relationValue Значение связи (или множество значений в виде массива)
     */
    protected function addToMany($relationName, $relationValue) {
        if (!is_array($relationValue)) {
            $relationValue = array($relationValue);
        }
        $this->$relationName = array_merge($this->$relationName, $relationValue);
    }

    /**
     * @param string $relationName  Имя связи BELONGS_TO
     * @param CActiveRecord $relationValue Значение связи
     * @throws CException Если связь не BELONGS_TO
     */
    protected function setRelated($relationName, CActiveRecord $relationValue) {
        /** @var $relation CActiveRelation */
        if ($relation = $this->getMetaData()->relations[$relationName]) {
            if ($relation instanceof CBelongsToRelation) {
                $foreignKey = $relation->foreignKey;
                CActiveRecord::__set($relationName, $relationValue);
                $this->$foreignKey = $relationValue->{$relationValue->getMetaData()->tableSchema->primaryKey};
            } else {
                throw new CException('Невозможно установить связь, т.к. связь не является BELONGS_TO');
            }
        } else {
            throw new CException('Устанавлива ');
        }
    }

    /**
     * @param array $attributes
     * @param string|null $scenario
     * @throws CException
     * @return $this
     */
    static public function create($attributes, $scenario = null) {
        $class = get_called_class();
        /** @var $model ActiveRecord */
        $model = new $class();
        $model->setScenario($scenario);
        $model->setProperties($attributes);
        if (!$model->save()) {
            throw new CException('Запись не сохранилась в БД. ' . var_export($model->getErrors(), true));
        }
        return $model;
    }

    /**
     * @param string $scenario
     * @return bool
     */
    public function validateInScenario($scenario) {
        $srcScenario = $this->getScenario();
        $this->setScenario($scenario);
        $res = $this->validate();
        $this->setScenario($srcScenario);
        return $res;
    }

    /**
     * Получение списка ошибок в виде строки
     * @return mixed
     */
    public function getErrorsAsString() {
        return var_export($this->getErrors(), true);
    }

    /**
     * @return string
     */
    public function __toString() {
        return (string)($this->hasAttribute('name') ? $this->getAttribute('name') : parent::__toString());
    }
}
