<?php

namespace Yiiapp\Framework\Model;

use CException;
use ReflectionMethod;
use Yiiapp\Framework\Model\ActiveRecord;

/**
 * Класс AR реализующий функционал наследования в одной таблице
 * @link http://www.martinfowler.com/eaaCatalog/singleTableInheritance.html Описание принципа наследования в одной таблице
 * @author Anton Tyutin <anton@tyutin.ru>
 */
abstract class StiActiveRecord extends ActiveRecord {

    final public function tableName() {
        return $this->stiTableName();
    }

    /**
     * Определение имени таблицы для мэппинга наследников
     * @return string
     */
    abstract public function stiTableName();

    public function init() {
        parent::init();
        $this->setAttribute($this->discriminatorFieldName(), get_class($this));
    }

    protected function instantiate($attributes) {
        $class = $this->getClassByDiscriminator($attributes, $this->discriminatorFieldName());
        return new $class(null);
    }

    public function defaultScope() {
        $criteria = new DbCriteria(); // TODO попробовать $this->getCriteria()
        $method = new ReflectionMethod($this, 'stiTableName');
        if ($method->getDeclaringClass()->getName() !== $className = get_class($this)) {
            $criteria->compare($this->discriminatorFieldName(), $className);
        }
        return $criteria;
    }

    /**
     * @param array $attributes  Массив атрибутов записи
     * @throws CException Если не найдено указанное поле или не существует класс
     */
    private function getClassByDiscriminator($attributes) {
        $fieldName = $this->discriminatorFieldName();
        $tableName = $this->tableName();
        $className = @$attributes[$fieldName];
        $baseClassName = get_class($this);
        if (!$className) {
            throw new CException("Не указано значение поля $fieldName, необходимое для определения типа записи из $tableName");
        } elseif (!class_exists($className)) {
            throw new CException("Не найден класс $className, указанный как тип записи из $tableName");
        } elseif ($className !== $baseClassName && !is_subclass_of($className, $baseClassName)) {
            throw new CException("Класс $className, определенный как тип записи из $tableName, не является подклассом $baseClassName");
        }
        return $className;
    }

    /**
     * Имя поля дискриминатора для базового класса записей
     * @return string
     */
    protected function discriminatorFieldName() {
        return 'type';
    }

}
