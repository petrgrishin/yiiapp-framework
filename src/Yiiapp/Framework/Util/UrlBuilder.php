<?php
/**
 * @author: Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Util;

use CException;
use CUrlManager;

/**
 * Class UrlBuilder
 */
class UrlBuilder {
    private $route;
    private $params = array();
    private $required = array();

    /**
     * @var CUrlManager
     */
    private $urlManager;

    public function __construct(CUrlManager $urlManager) {
        $this->urlManager = $urlManager;
    }

    /**
     * @return mixed
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * @param mixed $route
     * @return UrlBuilder
     */
    public function setRoute($route) {
        $this->route = $route;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParams() {
        return $this->params;
    }

    /**
     * @param mixed $params
     * @return UrlBuilder
     */
    public function setParams($params) {
        $this->params = $params;
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return UrlBuilder
     */
    public function setParam($name, $value){
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * @internal param bool $throw
     * @return string
     */
    private function concatenateToString() {
        return $this->getUrlManager()->createUrl($this->route, $this->params);
    }

    /**
     * @throws CException
     * @return string
     */
    public function toS() {
        foreach ($this->required as $param) {
            if (!isset($this->params[$param])) {
                throw new CException("Required param `{$param}` not exist");
            }
        }
        return $this->concatenateToString();
    }

    /**
     * @return CUrlManager
     */
    private function getUrlManager() {
        return $this->urlManager;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setHash($value) {
        $this->params["#"] = $value;
        return $this;
    }

    /**
     * @return UrlBuilder
     */
    public function setRequired() {
        $this->required = func_get_args();
        return $this;
    }

    /**
     * @return UrlBuilder
     */
    public function copy() {
        return clone $this;
    }

    /**
     * @return array
     */
    public function toJs() {
        return array(
            'uri'               => $this->concatenateToString(),
            'requiredParams'    => array_diff($this->required, array_keys($this->params))
        );
    }
}