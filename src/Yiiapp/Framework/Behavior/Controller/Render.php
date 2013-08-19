<?php
/**
 * Render Behavior
 *
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\Behavior\Controller;

use CException;
use Yiiapp\Framework\Behavior\Behavior;
use Yiiapp\Framework\Util\UrlBuilder;
use View;
use Yii;

class Render extends Behavior {
    /** @var array Соответствие директорий шаблонов к именам шаблонов */
    public $viewNamesByDirs = array();

    /**
     * Создает UrlBuilder
     *
     * @param $route
     * @param array $params
     * @return UrlBuilder
     * @throws CException
     */
    public function createUrlBuilder($route, $params = array()) {
        if (!$route) {
            throw new CException("Route for UrlBuilder must not be empty");
        }

        $object = new UrlBuilder(Yii::app()->getUrlManager());

        return $object->setRoute($route)->setParams($params);
    }

    /**
     * Устанавливает соответствие директори шаблона к имени шаблока
     *
     * @param $name
     * @param $dir
     * @return void
    @param $dir
     */
    public function assignViewDirWithName($name, $dir) {
        $this->viewNamesByDirs[$dir] = $name;
    }

    /**
     * Получить имя шаблона по ее дирекктории
     *
     * @param $dir
     * @return mixed
     */
    public function getViewNameByDir($dir) {
        return $this->viewNamesByDirs[$dir];
    }

    /**
     * @param $viewName
     * @param $basePath
     * @param null $moduleViewPath
     * @return string
     */
    public function getViewFolderPath($viewName, $basePath, $moduleViewPath = null) {
        if ($moduleViewPath) {
            $ret = $moduleViewPath . '/' . $viewName;
        } else {
            $ret = $basePath . '/' . $viewName;
        }
        return $ret;
    }

    /**
     * Создает окружение для шаблона View
     *
     * @param $dir
     * @param $data
     * @param null $name
     * @return View
     */
    public function createView($dir, $data, $name = null) {
        $object = new View($this->getViewNameByDir($dir));
        $object->setData($data)->setClientScript(Yii::app()->getClientScript())->setDir($dir);
        if ($name) {
            $object->setName($name);
        }
        $object->init();
        return $object;
    }
}