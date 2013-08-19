<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace Yiiapp\Framework\View;

use Yii;
use Yiiapp\Framework\Util\HtmlTag;
use Yiiapp\Framework\Util\Options;
use CException;

class View {
    const PRIORITY_HIGH = 2;
    const PRIORITY_STANDARD = 5;
    const PRIORITY_LOW = 8;

    private $data;
    private $name;
    private $dir;
    private $widgets;
    private $jsParams = array();
    private $jsLink;
    private $priority;
    /** @var  HtmlTag[] */
    private $tagsStack;

    /**
     * @var Options
     */
    private $dataOptions;

    private $uniq;
    private $uniqIterated = array();

    /** @var  ClientScript */
    private $clientScript;

    public function __construct($name) {
        $this->setName($name);
    }

    public function setDir($value) {
        $this->dir = $value;
        return $this;
    }

    /**
     * @param $name
     * @param bool $trowExist
     * @return $this
     * @throws CException
     */
    public function useScript($name, $trowExist = true) {
        $path = $this->getDir() . '/' . $name . '.js';
        if (file_exists($path)) {
            $this->getClientScript()->useScript($path, ClientScript::POS_HEAD);
            return;
        }
        if ($trowExist) {
            throw new CException("Path by script $name not exists in view `{$this->getName()}``");
        }
        return $this;
    }

    public function useStyle($name, $trowExist = true) {
        $path = $this->getDir() . '/' . $name;
        if (file_exists($path)) {
            $this->getClientScript()->useStyle($path, $trowExist);
            return;
        }
        if ($trowExist) {
            throw new CException("Path by script $name not exists in view `{$this->getName()}``");
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function init() {
        try {
            $this->useScript('script');
            $this->jsParams(array());
        } catch (CException $e) {
            $this->jsParams = array();
        }
        $this->useStyle('style', false);
        /** @var $session CHttpSession */
        $session = Yii::app()->session;
        $this->uniq = $session['uniq'] ? : 0;
        $session['uniq'] = $this->uniq + 1;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDir() {
        return $this->dir;
    }

    /**
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data) {
        $this->data = $data;
        $this->dataOptions = null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $viewName
     * @return $this
     */
    public function setName($viewName) {
        $this->name = $viewName;
        return $this;
    }

    /**
     * @return ClientScript
     */
    protected function getClientScript() {
        return $this->clientScript;
    }

    /**
     * @param ClientScript $clientScript
     * @return $this
     */
    public function setClientScript($clientScript) {
        $this->clientScript = $clientScript;
        return $this;
    }


    /**
     * @param array $value
     * @param null $scriptName
     * @return $this
     */
    public function jsParams($value = array(), $scriptName = null) {
        $this->jsParams[$scriptName ? : $this->getName()] = $value;
        return $this;
    }

    public function getUniqString($name) {
        return str_replace(array('.', '/'), '_', $this->getName()) . '_' . $this->uniq . '_' . $name;
    }

    public function getIteratedUniqString($name) {
        $uniqPostfix = $this->uniqIterated[$name] ? : 0;
        $this->uniqIterated[$name]++;
        return $this->getUniqString($name) . '_' . $uniqPostfix;
    }

    private function registerWidget($name, $object) {
        $isList = (bool)preg_match("#\[\]$#", $name);
        $name = str_replace('[]', '', $name);
        if ($this->widgets[$name]) {
            if ($isList) {
                if (!is_array($this->widgets[$name])) {
                    throw new CException("inject js param $name use as list");
                }
                $this->widgets[$name][] = $object;
            } else {
                if (is_array($this->widgets[$name])) {
                    throw new CException("inject js param $name use as list");
                    $this->widgets[$name] = $object;
                }
            }
        } else {
            $this->widgets[$name] = $isList ? array($object) : $object;
        }

    }

    /**
     * Внедрение дочернего виджета
     * @param $name
     * @param $object
     * @return $this
     */
    public function injectWidget($name, $object) {
        $this->registerWidget($name, $object);
        return $this;
    }

    public function getJsLink() {
        if (is_null($this->jsLink)) {
            $this->jsLink = $this->getUniqString('generated_js_link');
        }
        return $this->jsLink;
    }

    /**
     * @param Widget|array $object
     * @return array
     */
    private function getWidgetsLinks($object) {
        $res = array();
        /**
         * @param Widget $w
         * @return Widget
         */
        $fRunnedObject = function (Widget $w) {
            return $w->getRunnedObject();
        };
        if (is_array($object)) {
            $objectsList = $object;
            /** @var $object Widget */
            foreach ($objectsList as $object) {
                if ($fRunnedObject($object)->jsRunned()) {
                    $res[] = $fRunnedObject($object)->getJsLink();
                }
            }
        } elseif ($fRunnedObject($object)->jsRunned()) {
            $res = $fRunnedObject($object)->getRunnedObject()->getJsLink();
        }
        return $res;
    }

    public function createWidget($class, $params = null) {
        if (!is_subclass_of($class, 'Widget')) {
            throw new Exception('Widget class need has parent class Widget, been ' . $class);
        }
        /** @var $widget Widget */
        $widget = Yii::app()->getWidgetFactory()->createWidget($this, $class, $params ? : array());
        $widget->init();
        return $widget;
    }

    /**
     * @param $class
     * @param $injectName
     * @param array $params
     * @return Widget|Widgets\Table\Constructor
     */
    public function widget($class, $injectName, $params = array()) {
        $widget = $this->createWidget($class, $params);
        $this->registerWidget($injectName, $widget);
        return $widget;
    }

    public function complete() {
        $widgetLinks = array();
        if ($this->widgets) {
            /** @var $widget Widget */
            foreach ($this->widgets as $injectName => $widget) {
                if ($links = $this->getWidgetsLinks($widget)) {
                    $widgetLinks[$injectName] = $links;
                }
            }
        }
        foreach ($this->jsParams as $scriptName => $value) {
            $params = array();
            $params['data'] = $value;
            $params['links'] = $widgetLinks;
            $this->getClientScript()->registerViewParams($scriptName, $this->getJsLink(), $params, $this->priority ? : self::PRIORITY_STANDARD);
        }
    }

    /**
     * @return Options
     */
    public function getDataOptions() {
        if (!$this->dataOptions) {
            $this->dataOptions = new Options($this->data);
        }
        return $this->dataOptions;
    }

    /**
     * @param $path Путь к параметру через знак "."
     * @param null $default
     * @return mixed
     */
    public function getDataParam($path, $default = null) {
        return $this->getDataOptions()->get($path, $default);
    }

    /**
     * Признак выполнения js
     * @return bool
     */
    public function jsRunned() {
        return (bool)$this->jsParams;
    }

    public function useAsContext() {
        $this->getClientScript()->useAsContext($this);
    }

    /**
     * Выводит под-шаблон по имени файла в вызываемом основном шаблоне
     *
     * @param $viewName
     * @param null $data
     * @param null $return
     * @return string
     * @throws CException
     */
    public function renderPartial($viewName, $data = null, $return = null) {

        $file = $this->getDir() . '/' . $viewName . '.php';

        if (!file_exists($file)) {
            throw new \CException("View name `$viewName` not found in dir `" . $this->getDir() . "`");
        }

        if (is_array($data)) {
            extract($data, EXTR_PREFIX_SAME, 'data');
        }

        $view = $this;

        ob_start();
        ob_implicit_flush(false);
        require $file;
        $content = ob_get_clean();
        if (!$return) {
            echo $content;
        }
        return $content;
    }

    /**
     * @param $value
     * @return $this
     * @throws CException
     */
    public function setPriority($value) {
        if (!in_array($value, array(self::PRIORITY_HIGH, self::PRIORITY_LOW, self::PRIORITY_STANDARD))) {
            throw new CException('view priority not exists');
        }
        $this->priority = $value;
        return $this;
    }


    /**
     * @param $tagName
     * @param array $htmlOptions
     * @return \Util\HtmlTag
     */
    public function beginTag($tagName, $htmlOptions = array()) {
        return $this->tagsStack[] = $this->tag($tagName, $htmlOptions)->begin();
    }

    /**
     * @param bool $return
     * @return $this|string
     * @throws CException
     */
    public function endTag($return = false) {
        /** @var $htmlTag HtmlTag */
        if (!$htmlTag = array_pop($this->tagsStack)) {
            throw new CException("Not exist tags in tagsStack");
        }
        return $htmlTag->end($return);
    }

    /**
     * @param $tagName
     * @param array $htmlOptions
     * @return HtmlTag
     */
    public function tag($tagName, $htmlOptions = array()) {
        return HtmlTag::create($tagName, $htmlOptions);
    }

    /**
     * Возвращает текущий контроллер
     * @return \Controllers\Base
     */
    public function getController() {
        return Yii::app()->getController();
    }
}
