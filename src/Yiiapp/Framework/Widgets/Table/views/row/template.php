<?php
/** @var $columns array */
use Yiiapp\Framework\Util\HtmlTag;
use Yiiapp\Framework\Widgets\Table\Row;

/** @var $fields array */
/** @var $view View */
/** @var $this Row */
$jsParams = array(
    'container' => $container = $view->getUniqString('container'),
    'id' => $this->getId(),
    'actions' => array(),
    'click' => $this->getClickAction(),
    'dbClick' => $this->getDbClickAction()
);

$statusesClasses = array(
    Row::VIEW_STATUS_BLUE => "blue",
    Row::VIEW_STATUS_GREEN => "green",
    Row::VIEW_STATUS_ORANGE => "orange",
    Row::VIEW_STATUS_RED => "red",
    Row::VIEW_STATUS_GRAY => "gray",
    Row::VIEW_STATUS_WHITE => "white",
);

$tr = HtmlTag::create("tr", array("id" => $container))->begin();
if ($this->hasStatusColumn()) {
    $td = HtmlTag::create("td")->begin();
    if ($color = $statusesClasses[$this->getStatus()]) {
        echo HtmlTag::create("i", array('class' => 'fcs-icon-circle-' . $color))->toS();
    }
    $td->end();
}

foreach ($view->getDataParam('columns', array()) as $column => $columnData) {
    $td = HtmlTag::create("td", array("class" => "column-{$column}"));
    if ($columnData['align']) {
        $td->addClass("align-{$columnData['align']}");
    }

    if ($this->hasFieldObject($column) && $this->getFieldObject($column)->isShowTitle()) {
        $td->addAttr('title', $this->getFieldObject($column)->getContent(false));
    }

    $td->begin();

    if (!$this->hasFieldObject($column)) {
        echo $this->getField($column);
    } else {
        /** @var $field \Widgets\Table\Field */
        $field = $this->getFieldObject($column);
        if ($field->getLink()) {
            echo CHtml::openTag('a', array('href' => $field->getLink())) . $field . CHtml::closeTag('a');
        }
        if ($field->getAction()) {
            $fAction = $field->getAction();
            $fActionUrl = $view->getDataParam('actions.' . $fAction, false);
            $jsParams['actions'][$aLinkId = $view->getIteratedUniqString('actionLink')] = array(
                'name' => $fAction,
                'url' => $fActionUrl
            );
            echo CHtml::openTag('a', array('href' => $fActionUrl ? : '#', 'id' => $aLinkId)) . $field . CHtml::closeTag('a');
        }
        if (!$field->getAction() && !$field->getLink()) {
            echo $field;
        }
    }

    $td->end();
}
if ($view->getDataParam('menuActions', false)) {
    $td = HtmlTag::create("td")->begin();
    $menuItems = array();
    foreach ($view->getDataParam('menuActions', array()) as $actName => $actParams) {
        $jsParams['actions'][$actionLinkId = $view->getIteratedUniqString('actionLink')] = array(
            'name' => $actName,
            'url' => $actParams['url'] ? : null,
            'confirm' => $actParams['confirm'] ?: null
        );
        $menuItems[] = array(
            'url' => $actParams['url'] ? : '#',
            'label' => $actParams['label'],
            'linkOptions' => array(
                'id' => $actionLinkId
            )
        );
    }
    $this->widget('bootstrap.widgets.TbButtonGroup', array(
        'size' => 'medium',
        'type' => '',
        'encodeLabel' => false,
        'buttons' => array(
            array(
                'dropdownOptions' => array(
                    'class' => 'pull-right'
                ),
                'label' => '',
                'icon' => 'icon-align-justify',
                'items' => $menuItems
            ),
        ),
    ));
    $td->end();
}

$tr->end();
$view->jsParams($jsParams);
