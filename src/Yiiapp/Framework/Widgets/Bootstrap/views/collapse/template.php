<?php

use CHtml;

/** @var string $containerId */
/** @var boolean $toggleIcon */
/** @var array $items */
/** @var \Yiiapp\Framework\View\View $view */
$containerId = $view->getUniqString('container');

echo CHtml::openTag('div', array(
    'id' => $containerId,
    'class' => 'accordion widgets-bootstrap-collapse ' . $class . ' ' .  $toggleIcon,
));

?>

<?php foreach ($items as $key => $item): ?>

    <?php $itemId = $containerId . '_' . $key; ?>

    <div class="accordion-group">

        <div class="accordion-heading">

            <?php echo CHtml::openTag('a', array(
                'href' => '#' . $itemId,
                'data-toggle' => 'collapse',
                'data-parent' => '#' . $containerId,
                'class' => 'accordion-toggle ' . ($item['show'] ? '' : 'collapsed') . ' ' . $item['classLink'],
            )); ?>

                <?php if (!empty($item['marker'])): ?>
                    <i class="fcs-icon-circle-<?php echo $item['marker']; ?>"></i>
                <?php endif; ?>

                <?php if ($toggleIcon): ?>
                    <i class="icon-chevron-right"></i>
                    <i class="icon-chevron-down"></i>
                <?php endif; ?>

                <span><?php echo $item['title']; ?></span>

            <?php echo CHtml::closeTag('a'); ?>

        </div>

        <?php echo CHtml::openTag('div', array(
            'id' => $itemId,
            'class' => 'accordion-body collapse' . ($item['show'] ? ' in' : ''),
        )); ?>

        <?php echo CHtml::tag('div', array('class' => 'accordion-inner'), $item['content']); ?>
        <?php echo CHtml::closeTag('div'); ?>

    </div>
<?php endforeach; ?>

<?php echo CHtml::closeTag('div'); ?>
