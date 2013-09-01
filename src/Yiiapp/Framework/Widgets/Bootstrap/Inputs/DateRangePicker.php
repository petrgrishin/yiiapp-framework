<?php
/**
 * @author: Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Widgets\Bootstrap\Inputs;

use CHtml;
use CJavaScriptExpression;
use TbDateRangePicker;
use Util\DateTime;
use Yii;

class DateRangePicker extends TbDateRangePicker {
    public $splitRange = true;
    public $dbFormat = "yyyy-MM-dd";


    public function run() {
        $this->setLocaleSettings();

        if ($this->selector) {
            Yii::app()->bootstrap->registerDateRangePlugin($this->selector, $this->options, $this->callback);
        } else {
            list($name, $id) = $this->resolveNameID();

            if ($this->hasModel()) {
                $value = $this->model[$this->attribute];
            } else {
                $value = explode(" - ", $this->value);
            }

            if ($this->splitRange) {

                $idFrom = $id . "_from";
                $idTo = $id . "_to";

                echo CHtml::tag("div",array("class" => "range-group",),
                    CHtml::textField($name . "[]", $value[0], array_merge(
                        $this->htmlOptions,
                        array(
                            "id" => $idFrom
                        )
                    ))
                );
                echo CHtml::tag("div",array("class" => "range-group",),
                    CHtml::textField($name . "[]", $value[1], array_merge(
                        $this->htmlOptions,
                        array(
                            "id" => $idTo
                        )
                    ))
                );

                $this->callback = new CJavaScriptExpression("
                    function (startDate, endDate) {
                        \$('#{$idFrom}').val(startDate.toString('{$this->dbFormat}')).trigger('change');
                        \$('#{$idTo}').val(endDate.toString('{$this->dbFormat}')).trigger('change');
                    }
                ");
                Yii::app()->bootstrap->registerDateRangePlugin("#{$idFrom}, #{$idTo}", $this->options, $this->callback);
            } else {
                echo CHtml::textField($name, $this->value, $this->htmlOptions);
                Yii::app()->bootstrap->registerDateRangePlugin("#{$id}", $this->options, $this->callback);
            }
        }
    }

    private function setLocaleSettings() {
        $this->setDaysOfWeekNames();
        $this->setMonthNames();
    }

    private function setDaysOfWeekNames(){
        if (empty($this->options['locale']['daysOfWeek'])) {
            $this->options['locale']['daysOfWeek'] = Yii::app()->locale->getWeekDayNames('narrow', true);
        }
    }

    private function setMonthNames() {
        if (empty($this->options['locale']['monthNames'])) {
            $this->options['locale']['monthNames'] = array_values(
                Yii::app()->locale->getMonthNames('wide', true)
            );
        }
    }
}