<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */
use \Yiiapp\Framework\Widgets\Window\Dialog;
use \Yiiapp\Framework\Util\HtmlTag;

/** @var $view View */
/** @var $this Dialog */

$view->jsParams(array(
    "dialog" => array(
        "content"   =>  HtmlTag::create("div")->setContent($content)->toS(),
        "actions"   =>  $actions,
        "modal"     =>  true
    )
));
$view->widget('\Widgets\Window\Constructor', 'window', array(
    "title"     =>  $title,
))->run();