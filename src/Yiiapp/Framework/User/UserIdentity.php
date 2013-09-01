<?php
/**
 * @author Maxim Sokolovsky <sokolovsky@worksolutions.ru>
 */

namespace Yiiapp\Framework\User;

use CException;
use CUserIdentity;
use Models\User;
use Yii;

class UserIdentity extends CUserIdentity {

    private $id;

    public function authenticate() {
        $login = strtolower($this->username);
        /** @var $user User */
        $user = User::model()->find('LOWER(login)=?', array($login));
        if (!$user || !$user->isUsePassword($this->password) || !$user->isActive()) {
            return false;
        }
        $this->id = $user->id;
        return true;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param $id
     * @throws CException
     */
    public function setId($id) {
        if (!Yii::app()->isEnvironmentTest()) {
            throw new CException('Available only Test Environment');
        }

        $this->id = $id;
    }
}