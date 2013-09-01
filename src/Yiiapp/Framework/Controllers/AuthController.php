<?php
namespace Yiiapp\Framework\Controllers;

use CException;
use Yii;
use Yiiapp\Framework\Controllers\Gateway;
use Yiiapp\Framework\User\UserIdentity;

/**
 * Default Controller
 *
 * @autor Petr Grishin <petr.grishin@grishini.ru>
 */

class AuthController extends Gateway {

    public $layout = 'null';

    /**
     * @return mixed|\Yiiapp\Framework\User\WebUser
     */
    private function webUser() {
        return Yii::app()->user;
    }

    public function actionLogin() {
        $login = Yii::app()->request->getParam('login');
        $password = Yii::app()->request->getParam('password');
        $hasErrors = false;
        if ($login || $password) {
            if ($this->authorize($login, $password)) {
                $this->request()->redirect($this->webUser()->returnUrl);
            } else {
                $hasErrors = true;
            }
        }
        $this->render('auth.form', array(
            'hasErrors' => $hasErrors,
            'login' => $login ? : ''
        ));
    }

    /**
     * @param $login
     * @param $password
     * @return bool
     */
    public function authorize($login, $password) {
        $identity = new UserIdentity($login, $password);
        if ($identity->authenticate()) {
            Yii::app()->user->login($identity);
            return true;
        }
        return false;
    }

    public function actionLogout() {
        if ($this->webUser()->isAuthorized()) {
            $this->webUser()->logout();
        }
        $this->request()->redirect($this->createUrl('auth/login'));
    }

    /**
     * Перенаправляет на контроллер указанный по умолчанию у роли
     */
    public function actionDefault() {
        if ($this->webUser()->isAuthorized()) {
            $this->redirect($this->webUser()->returnUrl);
        } else {
            $this->redirect($this->createUrl('auth/login'));
        }
    }

    /**
     * @param $id
     * @throws CException
     */
    public function actionSwitchUser($id) {
        if (!Yii::app()->isEnvironmentTest()) {
            throw new CException('Available only Test Environment');
        }

        if (!$id) {
            throw new CException('Id is empty');
        }

        $identity = new UserIdentity(null,null);
        $identity->setId($id);

        $this->webUser()->login($identity);

        $this->redirect($this->webUser()->returnUrl);
    }
}
