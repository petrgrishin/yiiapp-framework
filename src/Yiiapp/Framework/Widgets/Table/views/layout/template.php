<?php
/** @var array $columns */
/** @var Row[] $rows */
/** @var $view View */
/** @var $urlBuilder UrlBuilder */
/** @var $pager CPagination */
/** @var $this Constructor */
use Yiiapp\Framework\Util\UrlBuilder;
use Yiiapp\Framework\Util\HtmlTag;
use Yiiapp\Framework\Widgets\Table\Constructor;
use Yiiapp\Framework\Widgets\Table\Row;

$data = $view->getData();
$pager = $data["pager"];
$view->jsParams(array(
    'container' => $container = $view->getUniqString('container'),
    'sort' => array(
        'order' => $data['order'],
        'by' => $data['by']
    ),
    'pagerClass' => $data["pagerCssClass"],
    'pagerPageSizesClass' => $data["pagerPageSizesClass"],
    'urls' => $view->getDataParam('urls', array())
));
$hasMenu = false;
ob_start();
/** @var $row Row */
foreach ($data["rows"] as $row) {
    $row->run();
    if ($row->isUseMenuActions()) {
        $hasMenu = true;
    }
}
$rowsContent = ob_get_clean();
?>
<div id="<?= $container; ?>" class="widgets-table-layout">
    <table>
        <tr>
            <?
            if ($this->getStatusesMap()):?>
                <th class="header-statuses">&nbsp;</th>
            <? endif ?>
            <? foreach ($data['columns'] as $columnName => $column): ?>
                <?php
                $th = HtmlTag::create("th");
                $th->addClass('header-column-' . $columnName);
                if ($column['align']) {
                    $th->addClass('align-' . $column['align']);
                }
                $th->begin();
                ?>

                <? if ($column['sortable']): ?>
                    <?
                    $sortIconClass = "icon-unsorted";
                    if ($data["by"] == $columnName) {
                        $sortIconClass = "icon-asc";

                        if ($data["order"] == "desc") {
                            $sortIconClass = "icon-desc";
                        }
                    }
                    ?>
                    <a href="#" class="sortable" data-name="<?= $columnName ?>">
                        <?= $column["title"] ?>
                        <i class="<?= $sortIconClass ?>"></i>
                    </a>
                <? else: ?>
                    <?= $column["title"]; ?>
                <?endif ?>

                <?php $th->end();; ?>
            <?
            endforeach;
            if ($hasMenu) {
                echo \CHtml::openTag('th', array('class' => 'header-menu align-center')) . '<i class="icon-th"></i></th>';
            }
            ?>

        </tr>
        <?= $rowsContent ?>
    </table>

    <?if($pager):?>
    <div class="pageNavigation <?= $data["pagerCssClass"] ?>">
        <?
        /** @var $this Widget */
        $this->widget('\Yiiapp\Framework\Widgets\Table\Pager', array(
            "pages" => $pager,
            'pagerPageSizesClass' => $data["pagerPageSizesClass"],
            "htmlOptions" => array(
                "class" => "pull-left"
            )
        ));
        ?>
    </div>
    <?endif?>