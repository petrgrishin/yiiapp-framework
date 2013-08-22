<?php

namespace Yiiapp\Framework\Widgets;

use Yiiapp\Framework\View\View;

/**
 * Widget
 *
 * @method View createView(string $dirname, array $data)
 * @method mixed run()
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */
class Widget extends \CWidget {

    static $defaultView = '';
    /**
     * @var View
     */
    private $view;

    private $viewTemplateName = '';

    private $data = array();

    private $delegatedObject = null;

    public function __construct($owner = null) {
        parent::__construct($owner);

        $this->attachBehavior('render', '\Behavior\Controller\Render');
    }

    /**
     * @param $name
     * @return $this
     */
    protected function useViewTemplate($name) {
        $this->viewTemplateName = $name;
        return $this;
    }

    protected function getViewTemplateName() {
        $template = $this->viewTemplateName ? : static::$defaultView;
        if (!$template) {
            $aClass = explode('\\', get_class());
            $template = strtolower(array_pop($aClass));
        }
        return $template;
    }

    protected function getViewName() {
        $class = get_class($this);
        if (!$this->getViewTemplateName()) {
            throw new \CException('Need setup template name by Widget ' . $class);
        }
        $nameParts = explode('\\', $class);
        array_pop($nameParts);
        array_push($nameParts, $this->getViewTemplateName());
        $res = strtolower(implode('.', $nameParts));
        return $res;
    }

    /**
     * Хук получения объекта представления непосредственно перед отрисовкой.
     * @param View $view
     * @return bool
     */
    protected function onBeforeRenderer(View $view) {
        return true;
    }

    /**
     * Создает окружение View для шаблона
     *
     * @param string $file
     * @param null $data
     * @param bool $return
     * @return string
     */
    public function renderInternal($file, $data = null, $return = false) {

        $data = array_merge($this->getData(), $data ? : array());

        if (is_array($data)) {
            extract($data, EXTR_PREFIX_SAME, 'data');
        }

        /** @var $view View */
        $view = $this->view = $this->createView(dirname($file), $data, $this->getViewName());

        if (!$this->onBeforeRenderer($view)) {
            return false;
        }
        ob_start();
        ob_implicit_flush(false);
        require $file;
        $content = ob_get_clean();
        if (!$return) {
            echo $content;
        }
        $this->view->complete();
        return $content;
    }

    /**
     * Получить имя файла шаблона и подключить скрипты
     *
     * @param string $viewName
     * @return bool|string
     */
    public function getViewFile($viewName) {
        $this->useViewTemplate($viewName);
        $basePath = $this->getViewPath();
        $folderPath = $this->getViewFolderPath($this->getViewTemplateName(), $basePath);
        $ret = $folderPath . '/template.php';
        if (!file_exists($ret)) {
            $ret = false;
        } else {
            $this->assignViewDirWithName($viewName, $folderPath);
        }
        return $ret;
    }

    /**
     * Приднак запускаjs представления
     * @return bool
     */
    public function jsRunned() {
        return $this->view && $this->view->jsRunned();
    }

    public function getJsLink() {
        if (!$this->view) {
            throw new CException("not view");
        }
        return $this->view->getJsLink();
    }

    public function selfRender($params, $return = false) {
        $this->render($this->getViewTemplateName(), $params, $return);
    }

    public function __set($name, $value) {
        try {
            return parent::__set($name, $value);
        } catch (CException $e) {
            return $this->data[$name] = $value;
        }
    }

    public function __get($name) {
        try {
            return parent::__get($name);
        } catch (CException $e) {
            if (!isset($this->data[$name])) {
                throw $e;
            }
            return $this->data[$name];
        }
    }

    public function getData() {
        return $this->data ? : array();
    }

    /**
     * Получение объекта "отрисовки"
     * @return Widget
     */
    public function getRunnedObject() {
        return $this->delegatedObject ? : $this;
    }

    protected function delegate(Widget $widget) {
        $this->delegatedObject = $widget;
    }

    /**
     * @return View
     */
    protected function getView() {
        return $this->view;
    }
}