<?php
/**
 * Обрабатываем объекты времени в моделях
 *
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Behavior\ActiveRecord;

use Yiiapp\Framework\Behavior\Behavior;

class DateTime extends Behavior {
    const COLUMN_DB_TYPE = "datetime";

    /**
     * Преобразуем объекты DateTime в формат db
     */
    public function beforeSave($event) {
        foreach ($this->owner->getTableSchema()->columns as $column) {
            $value = $this->owner->{$column->name};
            if (!$value || $column->dbType != static::COLUMN_DB_TYPE) {
                continue;
            }
            if (is_string($value)) {
                $value = new DateTime($value);
            } elseif (!$value instanceof DateTime) {
                continue;
            }
            $this->owner->{$column->name} = $value->dbFormat();
        }
    }

    /**
     * @param \CEvent $event
     */
    public function afterFind($event) {
        foreach ($this->owner->getTableSchema()->columns as $column) {
            if ($column->dbType == static::COLUMN_DB_TYPE) {
                $name = $column->name;
                $this->owner->$name = $this->getDateTime($name);
            }
        }
    }

    /**
     * Преобразует время в вормате db в объект DateTime
     *
     * @param $attribute
     * @return DateTime|null
     */
    public function getDateTime($attribute) {
        return $this->owner->$attribute ? new DateTime($this->owner->$attribute) : null;
    }
}
