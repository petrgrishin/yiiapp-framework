<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Widgets\Bootstrap;


use TbActiveForm;
use TbInput;
use Yii;

class ActiveForm extends TbActiveForm{
    const INPUT_HORIZONTAL = '\Yiiapp\Framework\Widgets\Bootstrap\Inputs\InputHorizontal';

    public $fieldConfig = array(
        "class" => '\Yiiapp\Framework\Widgets\Bootstrap\ActiveField'
    );

    public function autoCompleteFieldRow($options) {
        /** @var $field ActiveField */
        $field = new $this->fieldConfig["class"]($options);
        return $field->autoCompleteFieldRow($options);
    }

    protected function getInputClassName() {
        if (isset($this->input)) {
            return $this->input;
        } else {
            switch ($this->type) {
                case static::TYPE_HORIZONTAL:
                    return static::INPUT_HORIZONTAL;
                    break;

                case static::TYPE_INLINE:
                    return static::INPUT_INLINE;
                    break;

                case static::TYPE_SEARCH:
                    return static::INPUT_SEARCH;
                    break;

                case static::TYPE_VERTICAL:
                default:
                    return static::INPUT_VERTICAL;
                    break;
            }
        }
    }
}