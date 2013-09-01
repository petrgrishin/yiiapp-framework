<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace Yiiapp\Framework\Util;

use Yii;

class SessionUniqGenerator {
    static private $_value;

    static public function get() {
        /** @var $session \CHttpSession */
        $session = Yii::app()->session;
        self::$_value = $session[__CLASS__] ? : 0;
        $session[__CLASS__] = self::$_value + 1;
        return self::$_value;
    }
}