<?php
/**
 * LayoutChanger Behavior
 *
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Behavior\Controller;

use Yii;
use Yiiapp\Framework\Behavior\Behavior;

class LayoutChanger extends Behavior {
    public function events() {
        return array(
            'onBeforeAction'    =>  'beforeAction'
        );
    }

    public function beforeAction($event) {
        $sender = $event->sender;

        if (Yii::app()->getRequest()->getIsAjaxRequest()) {
            $sender->layout = "ajax";
        }
    }
}