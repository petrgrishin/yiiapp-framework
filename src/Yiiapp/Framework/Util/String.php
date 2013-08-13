<?php
/**
 * String utils
 *
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Util;

use Yii;

class String {

    private $string;

    public function __construct($str) {
        $this->string = $str;
    }

    /**
     * Статический метод чтобы возможно было использовать так:
     * $shortText = \Util\String::set($longText)->truncate();
     *
     * @param $str
     * @return $this
     */
    static public function set($str) {
        return new self($str);
    }

    /**
     * Обрезает ссылку, оставляя домен и указааное кол-во символов после домена и в конце адреса
     *
     * @param int $afterDomain
     * @param int $end
     * @param string $etc
     * @return mixed
     */
    public function truncateUrl($afterDomain = 1, $end = 8, $etc = '...') {
        $regex = '#^ (?>((?:.*:/+)?[^/]+/.{' . $afterDomain . '})) .{4,} (.{' . $end . '})$ #x';
        $replace = '$1' . $etc . '$2';
        return preg_replace($regex, $replace, $this->string);
    }

    /**
     * Обрезать до определенной длины
     * @param int $value
     * @return string
     */
    public function byLen($value = 200) {
        if (strlen($this->string) <= $value) {
            return $this->string;
        }
        return substr($this->string, 0, $value) . '...';
    }

    /**
     * Вывод цены в представлении
     *
     * @param string $currency
     * @return string
     */
    public function formatMoney($currency = "руб") {
        return Yii::app()->numberFormatter->formatCurrency($this->string, $currency);
    }

    /**
     * В нижний регистр
     * @return string
     */
    public function toLower() {
        return mb_strtolower($this->string, mb_detect_encoding($this->string));
    }
}