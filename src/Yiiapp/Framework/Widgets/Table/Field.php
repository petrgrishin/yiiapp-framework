<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace Yiiapp\Framework\Widgets\Table;


use Yiiapp\Framework\Behavior\ActiveRecord\DateTime;
use Yiiapp\Framework\Util\String;

class Field {

    const VIEW_TYPE_PRICE = 1;
    const VIEW_TYPE_NUMBER = 2;
    const VIEW_TYPE_TIME = 3;

    private
        $content = null,
        $length = null,
        $type = null,
        $link = '',
        $action = '',
        $showTitle = false,
        $dateFormat = 'medium',
        $timeFormat = 'short',
        $htmlSpecialchars = true;


    public function __construct($data = null) {
        $this->setContent($data);
    }

    /**
     * Внимание! Применимо для простых значений, т.е. без использования пользовательских тегов
     * @param $value
     * @return $this
     */
    public function setType($value) {
        $this->type = $value;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setAction($name) {
        $this->action = $name;
        $this->link = '';
        return $this;
    }

    /**
     * @param $addr
     * @return $this
     */
    public function link($addr) {
        $this->link = $addr;
        $this->action = '';
        return $this;
    }

    /**
     * !При использовании кастомные html инекции не поддерживаются
     * @param int $len
     * @return $this
     */
    public function setLength($len = 200) {
        $this->length = $len;
        return $this;
    }

    /**
     * @param $html
     * @return $this
     */
    public function setContent($html) {
        $this->content = $html;
        return $this;
    }

    /**
     * @param bool $useLength
     * @return string
     */
    public function getContent($useLength = true) {
        $res = (string)$this->content;
        if ($useLength && $this->isUseFixLength()) {
            $res = String::set($res)->byLen($this->getLength());
        }
        if ($this->htmlSpecialchars) {
            $res = htmlspecialchars($res);
        }
        return $res;
    }

    public function toS() {
        $res = (string)$this->getContent();
        if ($this->type) {
            switch ($this->type) {
                case self::VIEW_TYPE_NUMBER:
                    // TODO: узнать, каким должен быть результат, при пустом контенте
                    $res = number_format($this->content, 0, '', '');
                    break;
                case self::VIEW_TYPE_PRICE:
                    // @todo зависимость "numberFormatter"
                    $res = String::set($this->content)->formatMoney();
                    break;
                case self::VIEW_TYPE_TIME:
                    if ($this->content) {
                        $time = new DateTime($this->content);
                        if ($this->content instanceof \DateTime) {
                            $time->setTimestamp($this->content->getTimestamp());
                        }
                        $res = $time->toS($this->dateFormat, $this->timeFormat);
                    } else {
                        $res = '';
                    }

                    break;
            }
        }
        return $res;
    }

    public function __toString() {
        return $this->toS();
    }

    public function getLink() {
        return $this->link;
    }

    public function getAction() {
        return $this->action;
    }

    public function getLength() {
        return $this->length;
    }

    public function setHtmlSpecialchars($value = true) {
        $this->htmlSpecialchars = $value;
        return $this;
    }

    public function setShowTitle($value = true) {
        $this->showTitle = $value;
        return $this;
    }

    public function isShowTitle() {
        return $this->showTitle || $this->isUseFixLength();
    }

    public function isUseFixLength() {
        return (bool)$this->length;
    }

    /**
     * @param $formatName Имя формата short | medium | small | full | null - если не надо выводить
     * @return $this
     */
    public function setDateFormat($formatName){
        $this->dateFormat = $formatName;
        return $this;
    }

    /**
     * @param $formatName Имя формата short | medium | small | full | null - если не надо выводить
     * @return $this
     */
    public function setTimeFormat($formatName){
        $this->timeFormat = $formatName;
        return $this;
    }
}