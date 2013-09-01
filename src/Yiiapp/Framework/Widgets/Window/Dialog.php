<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Widgets\Window;

use Yiiapp\Framework\Widgets\Widget;

/**
 * Class Dialog
 * @package Widgets\Window
 */
class Dialog extends Widget {
    /** @var array */
    public $actions = array();
    /** @var string */
    public $title = "";
    /** @var \TbActiveForm */
    public $form;
    /** @var  string */
    public $type = "horizontal";

    public function init() {
        ob_start();
        $this->form = \Yii::app()->controller->beginWidget('\Widgets\Bootstrap\ActiveForm', array(
            "type" => $this->type
        ));
    }

    public function run() {
        // хак, чтобы формы отсылались по нажатию Enter на инпутах
        echo \CHtml::submitButton("", array("style" => "width:0;height:0;position:absolute;border:0;padding:0;margin:0"));

        \Yii::app()->controller->endWidget();
        $content = ob_get_clean();

        $this->useViewTemplate('dialog')->selfRender(array(
            "content" => $content,
            "actions" => $this->actions,
            "title" => $this->title,
        ));
    }

    private function _formCall($method, $args) {
        return call_user_func_array(array($this->form, $method), $args);
    }

    /**
     * @param $model
     * @param $attribute
     * @param array $htmlOptions
     * @return mixed
     */
    public function textFieldRow($model, $attribute, $htmlOptions = array()) {
        return $this->_formCall("textFieldRow", func_get_args());
    }

    /**
     * @param $model
     * @param $attribute
     * @param array $htmlOptions
     * @return mixed
     */
    public function passwordFieldRow($model, $attribute, $htmlOptions = array()) {
        return $this->_formCall("passwordFieldRow", func_get_args());
    }

    /**
     * @param $options
     * @return mixed
     */
    public function autoCompleteFieldRow($options) {
        return $this->_formCall("autoCompleteFieldRow", func_get_args());
    }

    /**
     * @param $model
     * @param $attribute
     * @param array $htmlOptions
     * @return mixed
     */
    public function hiddenField($model,$attribute,$htmlOptions=array()) {
        return $this->_formCall("hiddenField", func_get_args());
    }

    /**
     * @param $model
     * @param $attribute
     * @param array $htmlOptions
     * @return mixed
     */
    public function datepickerRow($model, $attribute, $htmlOptions = array()) {
        return $this->_formCall("datepickerRow", func_get_args());
    }

    /**
     * @param $model
     * @param $attribute
     * @param array $htmlOptions
     * @return mixed
     */
    public function dateRangeRow($model, $attribute, $htmlOptions = array()) {
        return $this->_formCall("dateRangeRow", func_get_args());
    }

    /**
     * @param $model
     * @param $attribute
     * @param array $data
     * @param array $htmlOptions
     * @return mixed
     */
    public function dropDownListRow($model, $attribute, $data = array(), $htmlOptions = array()) {
        return $this->_formCall("dropDownListRow", func_get_args());
    }

    public function checkBoxListRow($model, $attribute, $data = array(), $htmlOptions = array()) {
        return $this->_formCall("checkBoxListRow", func_get_args());
    }
}
