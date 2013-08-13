<?php
/**
 * DateTime
 *
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Util;

use DateTimeZone;
use Yii;

class DateTime extends \DateTime {

    /** Db format */
    const FORMAT_DB = 'Y-m-d H:i:s';

    static private $defaultTimezone;

    static function getDefaultTimezone() {
        if (!self::$defaultTimezone) {
            self::$defaultTimezone = new DateTimeZone(date_default_timezone_get());
        }
        return self::$defaultTimezone;
    }

    static function setDefaultTimezone(DateTimeZone $timezone) {
        self::$defaultTimezone = $timezone;
    }

    /**
     * Возвращает дату в формате базы данных
     *
     * @return string
     */
    public function dbFormat() {
        $this->setTimezone(self::getDefaultTimezone());
        return $this->format(self::FORMAT_DB);
    }

    public function toS($dateWidth = 'full', $timeWidth = 'short') {
        return Yii::app()->dateFormatter->formatDateTime($this->getTimestamp(), $dateWidth, $timeWidth);
    }

    public function __toString() {
        return $this->dbFormat();
    }
}
