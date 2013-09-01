<?php
/**
 * @author: Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Controllers\Service;

use Yiiapp\Framework\Controllers\Account;

class ListCriteria extends Account {

    public function actionSavePager($key, $number, $count) {
        $this->getListCriteria($key)->pagerParams(array('number' => $number, 'count' => $count));
        $this->renderText(array(
            'save' => true
        ));
    }

    public function actionSaveSort($key, $order = "number", $by = "asc") {
        $this->getListCriteria($key)->sortParams(array('order' => $order, 'by' => $by));
        $this->renderText(array(
            'save' => true
        ));
    }

    public function actionSaveFilter($key, array $data = null) {
        $this->getListCriteria($key)->filterData($data ? : array());
        $this->renderText(array(
            'save' => true
        ));
    }

    public function actionSaveFilterOptions($key, array $data) {
        $this->getListCriteria($key)->filterOptions($data);
        $this->renderText(array(
            'save' => true
        ));
    }
}
