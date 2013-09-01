<?
/** @var $this CBaseController */
use Yiiapp\Framework\Util\DateTime;
use Yiiapp\Framework\Util\HtmlTag;
use Yii;

/** @var $widget Filter */
/** @var $view View */
/** @var array $filterData */
/** @var array $filterOptions */
/** @var array $fields */


$presetActions = array(
    'save' => $view->getUniqString('presetAction_Save'),
    'add' => $view->getUniqString('presetAction_Add'),
    'delete' => $view->getUniqString('presetAction_Delete'),
    'rename' => $view->getUniqString('presetAction_Rename'),
    'saveAs' => $view->getUniqString('presetAction_SaveAs'),
    'cancel' => $view->getUniqString('presetAction_Cancel'),
    'search' => $view->getUniqString('presetAction_Search'),
    'searchForm' => $view->getUniqString("presetAction_SearchForm")
);

?>

    <div class="widgets-table-filter" id="<?= $container = $view->getUniqString("filterContainer") ?>">
        <form action="" class="form-horizontal <?= $presetActions["searchForm"] ?>">
            <div class="tabbable">
                <div class="filter-header">
                    <ul class="nav nav-tabs presets-tabs-actions pull-right">
                        <li>
                            <a href="#" class="<?= $presetActions["add"] ?>">
                                <i class="icon-plus"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"
                               id="<?= $tabSelector = $view->getUniqString("tabSelector")?>">
                                <i class="icon-align-justify"></i>
                            </a>
                            <ul class="dropdown-menu">

                            </ul>
                        </li>
                        <li>
                            <a href="#" id="<?= $toggler = $view->getUniqString("toogler")?>">
                                <i class="icon-chevron-up"></i>
                                <i class="icon-chevron-down"></i>
                            </a>
                        </li>
                    </ul>
                    <div class="presets-tabs">
                        <div class="presets-tabs__background-helper"></div>
                        <ul class="nav nav-tabs"
                            id="<?= $tabContainer = $view->getUniqString("filterTabContainer") ?>">
                        </ul>
                    </div>
                </div>
                <div class="tab-box">
                    <div class="content-tabs"
                         id="<?= $tabsContentContainer = $view->getUniqString("filterTabsContentContainer") ?>">
                        <div class="preset">
                            <?
                            foreach ($view->getDataParam('fields', array()) as $fieldName => $field) {
                                $id = $view->getIteratedUniqString($fieldName);
                                $field = $fields[$fieldName];

                                $fieldDiv = HtmlTag::create("div", array(
                                    'class' => "control-group field field-{$fieldName}"
                                ))->begin();

                                echo HtmlTag::create("label", array(
                                    "class" => "control-label pull-left",
                                    "for" => $id
                                ))->setContent($field["label"])->toS();

                                $link = HtmlTag::create("a", array(
                                    "href" => "#",
                                    "class" => "btn pull-right"
                                ))->begin();
                                echo HtmlTag::create("i", array("class" => "icon-minus"))->toS();
                                $link->end();

                                $controlsDiv = HtmlTag::create("div", array("class" => "controls"))->begin();

                                switch ($field["type"]) {
                                    case "date":
                                        $this->widget("bootstrap.widgets.TbDatePicker", array(
                                            "name" => $fieldName,
                                            "options" => array(
                                                'format' => 'yyyy-m-dd',
                                                'autoclose' => true,
                                                'todayBtn' => true,
                                            ),
                                            "htmlOptions" => array(
                                                "id" => $id
                                            )
                                        ));
                                        break;

                                    case "daterange":
                                        $this->createWidget('\Widgets\Bootstrap\Inputs\DateRangePicker', array(
                                            "name" => $fieldName,
                                            "options" => array(
                                                'format' => 'yyyy-MM-dd',
                                                'locale' => array(
                                                    "applyLabel" => '<i class="icon-ok icon-white"></i>',
                                                    "clearLabel" => '<i class="icon-remove icon-white"></i>',
                                                    "fromLabel" => 'От',
                                                    "toLabel" => 'До',
                                                    "weekLabel" => 'W',
                                                    "firstDay" => 0,
                                                )
                                            ),
                                            "htmlOptions" => array(
                                                "id" => $id,
                                                "class" => "input-small"
                                            ),
                                        ))->run();
                                        break;

                                    case "textrange":
                                        echo CHtml::tag("div",
                                            array(
                                                "class" => "range-group",
                                            ),
                                            CHtml::textField($fieldName . "[]", "", array(
                                                "id" => $id . "_from",
                                                "class" => "input-small"
                                            ))
                                        );
                                        echo CHtml::tag("div",
                                            array(
                                                "class" => "range-group",
                                            ),
                                            CHtml::textField($fieldName . "[]", "", array(
                                                "id" => $id . "_to",
                                                "class" => "input-small"
                                            ))
                                        );
                                        break;

                                    case "list":
                                        $this->widget("bootstrap.widgets.TbSelect2", array(
                                            "name" => $fieldName,
                                            "data" => array_replace(
                                                array(
                                                    '' => '---'
                                                ),
                                                $field["values"]
                                            ),
                                            "val" => '',
                                            "htmlOptions" => array(
                                                "id" => $id
                                            ),
                                        ));
                                        break;
                                    case "multilist":
                                        $this->widget("bootstrap.widgets.TbSelect2", array(
                                            "name" => $fieldName,
                                            "data" => $field["values"],
                                            "htmlOptions" => array(
                                                "id" => $id,
                                                "multiple" => "true"
                                            )
                                        ));

                                        break;
                                    case "checkbox":
                                        ?>
                                        <label class="checkbox">
                                            <?=CHtml::checkBox($fieldName, null, array(
                                                "value" => "1"
                                            ));?>
                                        </label>
                                        <?
                                        break;

                                    case "checkboxgroup":
                                        ?>
                                        <div class="checkbox-group">
                                            <?foreach($field["values"] as $key=>$value):?>
                                                <label class="checkbox">
                                                    <?=CHtml::checkBox($fieldName . '[]', null, array(
                                                        "value" => $key
                                                    ));?>
                                                    <?=$value?>
                                                </label>
                                            <?endforeach?>
                                        </div>
                                        <?
                                        break;

                                    case "radio":
                                        ?>
                                        <div class="radio-group">
                                            <?foreach($field["values"] as $key=>$value):?>
                                                <label class="radio">
                                                    <?=CHtml::radioButton($fieldName, null, array(
                                                        "value" => $key
                                                    ));?>
                                                    <?=$value?>
                                                </label>
                                            <?endforeach?>
                                        </div>
                                        <?
                                        break;

                                    default:
                                        echo CHtml::textField($fieldName, "", array(
                                            "id" => $id
                                        ));
                                }

                                $controlsDiv->end();
                                $fieldDiv->end();
                            }
                            ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="form-controls-group pull-left">
                            <button class="btn btn-primary <?= $presetActions["search"] ?>"><i
                                    class="icon-search icon-white"></i> Найти
                            </button>
                            <a href="#" class="btn <?= $presetActions["cancel"] ?>">Отменить</a>
                        </div>

                        <div class="form-controls-group pull-right">
                            <?
                            $this->widget('bootstrap.widgets.TbButtonGroup', array(
                                'size' => 'medium',
                                'type' => '',
                                'buttons' => array(
                                    array(
                                        'label' => '',
                                        'icon' => 'icon-wrench',
                                        'items' => array(
                                            array('label' => 'Сохранить', 'url' => '#', 'linkOptions' => array('class' => $presetActions['save'])),
                                            array('label' => 'Сохранить как', 'url' => '#', 'linkOptions' => array('class' => $presetActions['saveAs'])),
                                            '---',
                                            array('label' => 'Переименовать', 'url' => '#', 'linkOptions' => array('class' => $presetActions['rename'])),
                                            array('label' => 'Удалить', 'url' => '#', 'linkOptions' => array('class' => $presetActions['delete'])),
                                        )
                                    ),
                                ),
                            ));

                            $fieldsButtons = array();
                            $viewFieldsMenu = array(
                                'fields' => array(),
                                'hideAll' => $viewFieldsMenuHideAllLink = $view->getUniqString('viewFieldsMenu_HideAll'),
                                'showAll' => $viewFieldsMenuShowAllLink = $view->getUniqString('viewFieldsMenu_ShowAll'),
                            );
                            foreach ($view->getDataParam('fields', array()) as $fieldName => $field) {
                                $viewFieldsMenu['fields'][$fieldName] = $fViewFieldLink = $view->getUniqString('viewFieldsMenu_Item_' . $fieldName);
                                $fieldsButtons[] = array(
                                    'label' => $field["label"],
                                    'url' => '#',
                                    'icon' =>
                                    'icon-ok',
                                    'linkOptions' => array(
                                        'id' => $fViewFieldLink,
                                        'data-dropdown-noswitch' => 'true'
                                    ));
                            }

                            $fieldsButtons = array_merge($fieldsButtons, array(
                                "---",
                                array('label' => "Показать все поля", 'url' => '#', 'linkOptions' => array('id' => $viewFieldsMenuShowAllLink)),
                                array('label' => "Скрыть все условия", 'url' => '#', 'linkOptions' => array('id' => $viewFieldsMenuHideAllLink))
                            ));

                            $this->widget('bootstrap.widgets.TbButtonGroup', array(
                                'size' => 'medium',
                                'type' => '',
                                'buttons' => array(
                                    array(
                                        'label' => '',
                                        'icon' => 'icon-plus',
                                        'items' => $fieldsButtons
                                    ),
                                ),
                            ));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?
ob_start();
$windowInputNameId = $view->getUniqString("windowInputNameId");
?>
    <div>
        <form class="form-horizontal">
            <div class="control-group">
                <label class="control-label" for="<?= $windowInputNameId ?>">Название фильтра</label>

                <div class="controls">
                    <input name="name" type="text" id="<?= $windowInputNameId ?>"/>
                </div>
            </div>
        </form>
    </div>
<?
$view->widget('\Widgets\Window\Constructor', 'window', array(
    'title' => 'Название фильтра',
    'content' => ob_get_clean()
))->run();

$view->jsParams(array(
    "data" => array(),
    "urls" => array(
        'saveData' => $view->getDataParam('urls.saveData')->toJs(),
        'saveOptions' => $view->getDataParam('urls.saveOptions')->toJs(),
    ),
    "options" => $view->getDataParam('options', $data['options'] ? : array()),
    "fields" => $view->getDataParam('fields'),
    "container" => $container,
    "tabsContentContainer" => $tabsContentContainer,
    'viewFieldsMenu' => $viewFieldsMenu,
    'presetActions' => $presetActions,
    'toggler' => $toggler,

    "tabContainer" => $tabContainer,

    'tabsParams' => array(
        "tabContainer" => $tabContainer,
        "tabSelector" => $tabSelector
    )
));
