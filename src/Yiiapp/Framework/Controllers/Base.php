<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Controllers;

use \Yiiapp\Framework\Util\UrlBuilder;
use CController;
use CEvent;
use CHttpRequest;
use View;
use WebListCriteria;
use WebUser;
use Yii;

/**
 * Class Base
 * @method  View        createView($dir, $data)
 * @method  UrlBuilder  createUrlBuilder($route, $params = array())
 * @method  void        assignViewDirWithName($name, $dir)
 * @method  string      getViewNameByDir($dir)
 * @method  string      getViewFolderPath($viewName, $basePath)
 */
class Base extends CController {

    /**
     * @param string $key
     * @return WebListCriteria
     */
    protected function getListCriteria($key = "") {
        /** @var $webCriteria WebListCriteria */
        $webCriteria = Yii::app()->webListCriteria;

        if ($key) {
            $webCriteria->useStoreKey($key);
        }

        return $webCriteria;
    }

    /**
     * Добовляем поведения
     *
     * @return array
     */
    public function behaviors() {
        return array(
            'render' => array('class' => '\Behavior\Controller\Render'),
            'layoutChanger' => array('class' => '\Behavior\Controller\LayoutChanger'),
        );
    }

    /**
     * Получить имя файла шаблона и подключить скрипты
     *
     * @param string $viewName
     * @param string $viewPath
     * @param string $basePath
     * @param null $moduleViewPath
     * @return bool|mixed|string
     */
    public function resolveViewFile($viewName, $viewPath, $basePath, $moduleViewPath = null) {
        $folderPath = $this->getViewFolderPath($viewName, $basePath, $moduleViewPath);
        if (!($ret = parent::resolveViewFile($viewName, $viewPath, $basePath, $moduleViewPath))) {
            $ret = $folderPath . '/template.php';
        }
        if (!file_exists($ret)) {
            $ret = false;
        } else {
            $this->assignViewDirWithName($viewName, $folderPath);
        }
        return $ret;
    }

    /**
     * @param string $file
     * @param null $data
     * @param bool $return
     * @return string
     */
    public function renderInternal($file, $data = null, $return = false) {
        if (is_array($data)) {
            extract($data, EXTR_PREFIX_SAME, 'data');
        }

        $view = $this->createView(dirname($file), $data);

        ob_start();
        ob_implicit_flush(false);
        require $file;
        $content = ob_get_clean();
        if (!$return) {
            echo $content;
        }
        $view->complete();
        return $content;
    }

    /**
     * @param \CAction $action
     * @return bool
     */
    final public function beforeAction($action) {
        $this->onBeforeAction();
        return true;
    }

    public function onBeforeAction() {
        $this->raiseEvent('onBeforeAction', new CEvent($this));
    }

    public function getStorageValue($key, $default = null) {
        $session = Yii::app()->session;
        return $session[$this->getUniqueId() . '_' . $key] ? : $default;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setStorageValue($key, $value) {
        $session = Yii::app()->session;
        $session[$this->getUniqueId() . '_' . $key] = $value;
        return $this;
    }

    /**
     * Генерация уникального значения по имени в пространстве исполняемого класса.
     * @param $name
     * @return string
     */
    protected function generateUniqKey($name) {
        return $this->getUniqueId() . '_' . $name;
    }

    /**
     * @return CHttpRequest
     */
    public function request() {
        return Yii::app()->request;
    }

    /**
     * @return WebUser
     */
    protected function user() {
        return Yii::app()->getUser();
    }
}
