<?php
/**
 * @author: Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Controllers;

class Gateway extends Base {
    public function onBeforeAction(){
        set_time_limit(600);

        return parent::onBeforeAction();
    }
}