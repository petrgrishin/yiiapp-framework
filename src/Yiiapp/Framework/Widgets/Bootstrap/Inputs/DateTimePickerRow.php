<?php
/**
 * DateTimePickerRow
 * 
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Widgets\Bootstrap\Inputs;


class DateTimePickerRow extends \CInputWidget {
    public $form;
    public $options;
    public $model;
    public $attribute;
    public $prepend;
    public $append;
    public $hintText;


    public function run() {
        /** @var \Controller $controller */
        $controller = \Yii::app()->controller;

        echo '<div class="control-group">';
            echo \CHtml::activeLabelEx($this->model, $this->attribute, array('class' => 'control-label'));

            echo '<div class="controls">';


                if ($this->_useAddOn()) {
                    echo \CHtml::openTag('div', array('class' => $this->_classAddOn()));
                }

                echo $this->_prepend();

                $controller->createWidget('\Widgets\Bootstrap\Inputs\DateTimePicker', array(
                    'form'        => $this->form,
                    'model'       => $this->model,
                    'attribute'   => $this->attribute,
                    'options'     => $this->options,
                ))->run();

                echo $this->_append();

                if ($this->_useAddOn()) {
                    echo \CHtml::closeTag('div');
                }

                echo $this->getError() . $this->getHint();

            echo '</div>';
        echo '</div>';
    }

    private function _prepend() {
        return $this->prepend ? $this->_renderAddOn($this->prepend) : '';
    }

    private function _append() {
        return $this->append ? $this->_renderAddOn($this->append) : '';
    }

    private function _renderAddOn($str) {
        return '<span class="add-on">' . $str . '</span>';
    }

    private function _useAddOn() {
        return $this->prepend || $this->append;
    }

    private function _classAddOn() {
        $classAddOn = array();
        $this->prepend && $classAddOn[] = 'input-prepend';
        $this->append && $classAddOn[] = 'input-append';
        return implode(' ', $classAddOn);
    }

    private function getError() {
        return $this->form->error(
            $this->model,
            $this->attribute
        );
    }

    private function getHint() {
        return $this->hintText ? \CHtml::tag('p', array('class' => 'help-block'), $this->hintText) : '';
    }

}