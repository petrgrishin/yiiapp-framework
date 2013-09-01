<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 * @var View $view
 * @var \Yiiapp\Framework\Widgets\Bootstrap\Box $this
 */

use Yiiapp\Framework\Util\HtmlTag;

$view->jsParams(array_merge($this->getJsParams(), array(
    "actionsContainer" => $actionsContainer = $view->getUniqString("actionsContainer")
)));

echo HtmlTag::create("div", array("id" => $actionsContainer))->toS();