<?php
/**
 *
 *
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

/** @var $view View */
$view->jsParams(array(
    'container' => $container = $view->getUniqString('container'),
    'title' => $title,
    'content' => $content,
    'btns' => $view->getDataParam('btns', array())
));

?>
<div id="<?php echo $container; ?>" class="widgets-window-constructor modal hide">
    <? if ($title): ?>
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">×</button>
            <h3 class="title"><?php echo $title; ?></h3>
        </div>
    <? endif ?>
    <div class="modal-body">
    </div>
    <div class="modal-footer">
        <?php echo $footer; ?>
    </div>
</div>