<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Widgets\Bootstrap;


use CHtml;
use Yii;
use Yiiapp\Framework\Model\ActiveRecord;

/*
 * TODO: DEPRECATED
 * Нужно будет перенести на обычный InputHorizontal
*/
class ActiveField {
    public $template = "{label}\n{input}\n{error}";

    /** @var  ActiveRecord */
    public $model;

    /** @var  string */
    public $name;

    /** @var array */
    public $errorOptions = array('tag' => 'p', 'class' => 'help-block');

    /** @var  string */
    public $errorText;

    /** @var  string */
    public $attribute;

    /** @var  string */
    public $label;

    public function __construct($options) {
        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @throws \CException
     */
    public function autoCompleteFieldRow(array $options) {
        if (!$options['htmlOptions']['id']) {
            throw new \CException('Обязательно должен присутствовать html идентификатор элемента');
        }

        if ($this->model) {
            $options["name"] = get_class($this->model) . "[{$this->attribute}]";
        } else {
            $options["name"] = $this->name;
        }

        $name = $options['name'];
        $options['name'] = $name . '_input';

        $widgetOptions = array();
        foreach (array(
                     "sourceUrl",
                     "source",
                     "model",
                     "attribute",
                     "value",
                     "name",
                     "htmlOptions"
                 ) as $key) {
            $widgetOptions[$key] = $options[$key];
        }

        $hiddenId = $widgetOptions['htmlOptions']['id'] . '_hidden' ? : null;
        $widgetOptions = array_merge($widgetOptions, array(
            'options' => array(
                'select' => 'js: function(event, ui) {
                    // действие по умолчанию, значение текстового поля
                    // устанавливается в значение выбранного пункта
                    this.value = ui.item.label;
                    // устанавливаем значения скрытого поля
                    $("#' . $hiddenId . '").val(ui.item.id);
                    return false;
                }'
            )
        ));
        $input = '<div class="controls">';
        $input .= Yii::app()->controller->widget('zii.widgets.jui.CJuiAutoComplete', $widgetOptions, true);
        $input .= CHtml::tag('input', array('id' => $hiddenId, 'name' => $name, 'type' => 'hidden'));
        $input .= "</div>";
        return $this->render($input);
    }

    public function begin() {
        return '<div class="control-group">';
    }

    public function end() {
        return '</div>';
    }

    public function render($input) {
        ob_start();
        echo $this->begin();
        echo "\n";
        echo strtr($this->template, array(
            '{input}' => $input,
            '{label}' => $this->label(),
            '{error}' => $this->error(),
        ));
        echo "\n";
        echo $this->end();

        return ob_get_clean();
    }

    public function label() {
        if ($this->model) {
            $labelText = $this->model->getAttributeLabel($this->attribute);
        } else {
            $labelText = $this->label;
        }

        // TODO: прописать верный for
        return \CHtml::label($labelText, "", array("class" => "control-label"));
    }

    public function error() {
        $tag = $this->errorOptions["tag"];
        unset($this->errorOptions["tag"]);

        if ($this->model) {
            $errorText = $this->model->getError($this->attribute);
        } else {
            $errorText = $this->errorText;
        }

        return CHtml::tag($tag, $this->errorOptions, $errorText);
    }
} 