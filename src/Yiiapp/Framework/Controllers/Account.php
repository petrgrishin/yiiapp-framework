<?php
/**
 * @author: Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Controllers;

use CFilterChain;
use Yii;

class Account extends Base {
    /**
     * @param $filterChain CFilterChain
     * @return bool
     */
    final public function filterAuthorized ($filterChain) {
        if (!Yii::app()->user->isAuthorized()) {
            Yii::app()->user->setReturnUrl(Yii::app()->request->requestUri);
            Yii::app()->request->redirect(Yii::app()->urlManager->createUrl('auth/login'));
            return false;
        }
        $filterChain->run();
        return true;
    }

    /**
     * @throws \CException
     * @return array
     */
    final public function filters() {
        $filters = array(
            'authorized',
            'accessControl'
        );

        if (is_array($this->additionalFilters())) {
            $filters = array_merge($filters, $this->additionalFilters());
        } else {
            throw new \CException('Method `additionalFilters` must return array');
        }

        return $filters;
    }

    /**
     * Фильтры контроллера, которые добавляются в основную цепочку
     *
     * @return array
     */
    public function additionalFilters() {
        return array();
    }

    /**
     * Правила доступа по фильтру accessControl
     * @return array
     */
    public function accessRules() {
        return array();
    }
}