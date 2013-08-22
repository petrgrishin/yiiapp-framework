<?php
/** @var $view \Yiiapp\Framework\View\View */
$items = $view->getDataParam('items', array());

$paddingStep = 30;
$fCalcPadding = function ($level) use ($paddingStep) {
    return (int)($paddingStep * ($level - 1));
};
$delegateItems = array();
foreach ($items as $item) {
    $linkOptions = array();
    if ($item['level'] > 1) {
        $linkOptions['style'] = 'padding-left: ' . $fCalcPadding($item['level']) . 'px;';
    }
    $delegateItems[] = array(
        'label' => $item['name'],
        'url' => $item['url'],
        'linkOptions' => $linkOptions
    );
}


$this->widget('bootstrap.widgets.TbMenu', array(
    'type' => 'tabs',
    'stacked' => true,
    'items' => $delegateItems
));
