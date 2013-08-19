<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace Yiiapp\Framework\User;

class WebUser extends \CWebUser {

    public $defaultController;
    public $defaultControllerRole = array();

    private $model;
    private $roleProfile = array();

    /**
     * @return bool
     */
    public function isAuthorized() {
        return !$this->getIsGuest();
    }

    /**
     * @return User
     */
    public function getModel() {
        if ($this->model === null) {
            $this->model = User::model()->findByPk($this->id);
        }
        return $this->model;
    }

    /**
     * @return CActiveRecord|null|SaleManagerProfile
     */
    public function getSaleManagerProfile() {
        if ($this->roleProfile[User::ROLE_MANAGER] === null) {
            $this->roleProfile[User::ROLE_MANAGER] = SaleManagerProfile::getByUser(Yii::app()->user->getModel());
        }
        return $this->roleProfile[User::ROLE_MANAGER];
    }

    /**
     * @return CActiveRecord|null|ChiefProfile
     */
    public function getChiefProfile() {
        if ($this->roleProfile[User::ROLE_CHIEF] === null) {
            $this->roleProfile[User::ROLE_CHIEF] = ChiefProfile::getByUser(Yii::app()->user->getModel());
        }
        return $this->roleProfile[User::ROLE_CHIEF];
    }

    /**
     * @return bool
     */
    public function isSaleManager() {
        return Yii::app()->user->checkAccess(User::ROLE_MANAGER);
    }

    /**
     * @return bool
     */
    public function isChief() {
        return Yii::app()->user->checkAccess(User::ROLE_CHIEF);
    }

    /**
     * Так называемый шлюз перехода к дефолтному контроллеру роли после авторизации
     *
     * @param null $defaultUrl
     * @return string
     */
    public function getReturnUrl($defaultUrl = null) {
        if ($defaultUrl === null && $controller = $this->_getDefaultControllerRole()) {
            $this->setReturnUrl(null);
            $defaultUrl = $controller;
        }
        return parent::getReturnUrl($defaultUrl);
    }

    /**
     * Возвращает контроллер роли по умолчанию
     *
     * @return null|array
     */
    private function _getDefaultControllerRole(){
        foreach ($this->defaultControllerRole as $role => $controller) {
            if (Yii::app()->user->checkAccess($role)) {
                return array($controller);
            }
        }
        if ($this->defaultController) {
            return array($this->defaultController);
        }
        return null;
    }


}