<?php
/**
 * @author: Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Util;
use CException;
use CHtml;

/**
 * Class HtmlTag
 */
class HtmlTag{
    public $htmlOptions = array();
    public $tagName;
    public $content = "";

    /**
     * @param $tagName
     * @param $htmlOptions
     * @return HtmlTag
     */
    static function create($tagName, array $htmlOptions = array()) {
        /** @var $htmlTag HtmlTag */
        $htmlTag = new static();

        return $htmlTag
            ->setTagName($tagName)
            ->setHtmlOptions($htmlOptions);
    }

    /**
     * @return $this
     */
    public function begin() {
        ob_start();
        return $this;
    }

    /**
     * @param bool $return
     * @return $this|string
     */
    public function end($return = false) {
        $this->content = ob_get_clean();

        if ($return) {
            return $this->toS();
        }

        echo $this->toS();
        return $this;
    }

    /**
     * @return string
     */
    public function toS() {
        ob_start();
        echo CHtml::openTag($this->getTagName(), $this->getHtmlOptions());
        echo $this->content;
        echo CHtml::closeTag($this->getTagName());
        return ob_get_clean();
    }

    /**
     * @return $this
     */
    public function addClass() {
        $classes = explode(" ", isset($this->htmlOptions['class']) ? trim($this->htmlOptions["class"]) : '');
        $classes = array_merge($classes, func_get_args());
        $this->htmlOptions["class"] = trim(implode(" ", array_unique($classes)));
        return $this;
    }

    /**
     * @param $className
     * @return $this
     */
    public function removeClass($className) {
        $classes = explode(" ", $this->htmlOptions["class"]);
        $this->htmlOptions["class"] = trim(implode(" ", array_diff($classes, array($className))));
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     * @throws CException
     */
    public function addAttr($name, $value) {
        if (!isset($value)) {
            throw new CException("value is not setted for attr `{$name}`");
        }
        return $this->attr($name, $value);
    }

    /**
     * @param $name
     * @param null $value
     * @return $this
     */
    public function attr($name, $value = null) {
        if (!isset($value)) {
            return $this->htmlOptions[$name];
        }
        $this->htmlOptions[$name] = $value;
        return $this;

    }

    /**
     * @param $name
     * @return $this
     */
    public function removeAttr($name) {
        unset($this->htmlOptions[$name]);
        return $this;
    }

    /**
     * @return mixed
     */
    protected function getTagName() {
        return $this->tagName;
    }

    /**
     * @param mixed $tagName
     * @return $this
     */
    public function setTagName($tagName) {
        $this->tagName = $tagName;
        return $this;
    }

    /**
     * @return array
     */
    public function getHtmlOptions() {
        return $this->htmlOptions;
    }

    /**
     * @param array $htmlOptions
     * @return $this
     */
    public function setHtmlOptions(array $htmlOptions = array()) {
        $this->htmlOptions = array_merge($this->htmlOptions, $htmlOptions);
        return $this;
    }

    /**
     * @return HtmlTag
     */
    public function copy() {
        return clone $this;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }
}