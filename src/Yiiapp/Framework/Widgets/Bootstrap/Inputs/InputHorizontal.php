<?

namespace Widgets\Bootstrap\Inputs;

use TbInputHorizontal;

class InputHorizontal extends TbInputHorizontal{
    protected function dateRangeField() {

        if (isset($this->htmlOptions['options'])) {
            $options = $this->htmlOptions['options'];
            unset($this->htmlOptions['options']);
        }

        if (isset($options['callback'])) {
            $callback = $options['callback'];
            unset($options['callback']);
        }

        echo $this->getLabel();
        echo '<div class="controls">';
        echo $this->getPrepend();
        $this->widget(
            '\Widgets\Bootstrap\Inputs\DateRangePicker',
            array(
                'model' => $this->model,
                'attribute' => $this->attribute,
                'options' => isset($options) ? $options : array(),
                'callback' => isset($callback) ? $callback : array(),
                'htmlOptions' => $this->htmlOptions,
            )
        );
        echo $this->getAppend();
        echo $this->getError() . $this->getHint();
        echo '</div>';
    }
}