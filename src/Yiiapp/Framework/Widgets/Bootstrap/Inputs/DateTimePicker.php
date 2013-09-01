<?php
/**
 * DateTimePicker
 * 
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Widgets\Bootstrap\Inputs;


class DateTimePicker extends \CInputWidget {

    public $form;
    public $options;

    public function init() {
        $this->options = array_merge(array(
            'format' => 'yyyy-mm-dd hh:ii:ss',
            'autoclose' => true,
            'todayBtn' => true,
        ), $this->options);
    }

    public function run() {

        list($name, $id) = $this->resolveNameID();

        if ($this->hasModel()) {
            if ($this->form) {
                echo $this->form->textField($this->model, $this->attribute, $this->htmlOptions);
            } else {
                echo \CHtml::activeTextField($this->model, $this->attribute, $this->htmlOptions);
            }

        } else {
            echo \CHtml::textField($name, $this->value, $this->htmlOptions);
        }

        /** @var \ClientScript $clientScript */
        $clientScript = \Yii::app()->getClientScript();

        $clientScript->registerPackage('datetimepicker');

        $options = !empty($this->options) ? \CJavaScript::encode($this->options) : '';

        ob_start();
        echo "jQuery('#{$id}').datetimepicker({$options})";
        $clientScript->registerScript(__CLASS__ . '#' . $this->getId(), ob_get_clean() . ';');

    }
}