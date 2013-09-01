<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\Widgets\Bootstrap;

use CHtml;
use CException;
use Yiiapp\Framework\Util\SessionUniqGenerator;
use Yii;
use Yiiapp\Framework\Widgets\Widget;

class Box extends Widget {
    /**
     * @var mixed
     * Box title
     * If set to false, a box with no title is rendered
     */
    public $title = false;

    /**
     * @var string
     * The class icon to display in the header title of the box.
     * @see <http://twitter.github.com/bootstrap/base-css.html#icon>
     */
    public $headerIcon;


    /**
     * @var string
     * Box Content
     * optional, the content of this attribute is echoed as the box content
     */
    public $content = '';

    /**
     * @var array
     * box HTML additional attributes
     */
    public $htmlOptions = array();

    /**
     * @var array
     * box header HTML additional attributes
     */
    public $htmlHeaderOptions = array();

    /**
     * @var array
     * box content HTML additional attributes
     */
    public $htmlContentOptions = array();

    /**
     * @var array the configuration for additional header buttons. Each array element specifies a single button
     * which has the following format:
     * <pre>
     *     array(
     *        array(
     *          'class' => 'bootstrap.widgets.TbButton',
     *          'label' => '...',
     *          'size' => '...',
     *          ...
     *        ),
     *      array(
     *          'class' => 'bootstrap.widgets.TbButtonGroup',
     *          'buttons' => array( ... ),
     *          'size' => '...',
     *        ),
     *      ...
     * )
     * </pre>
     */
    public $headerButtons = array();

    public $headerAppendTitle = false;

    public $headerPrependTitle = false;

    public $actions = array();

    private $_jsParams = array();

    /**
     *### .init()
     *
     * Widget initialization
     */
    public function init()
    {
        if (isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] = 'widgets-bootstrap-box ' . $this->htmlOptions['class'];
        } else {
            $this->htmlOptions['class'] = 'widgets-bootstrap-box';
        }

        if (isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] = 'bootstrap-widget ' . $this->htmlOptions['class'];
        } else {
            $this->htmlOptions['class'] = 'bootstrap-widget';
        }

        if (isset($this->htmlContentOptions['class'])) {
            $this->htmlContentOptions['class'] = 'bootstrap-widget-content ' . $this->htmlContentOptions['class'];
        } else {
            $this->htmlContentOptions['class'] = 'bootstrap-widget-content';
        }

        if (!isset($this->htmlContentOptions['id'])) {
            $this->htmlContentOptions['id'] = $this->getId();
        }

        if (isset($this->htmlHeaderOptions['class'])) {
            $this->htmlHeaderOptions['class'] = 'bootstrap-widget-header ' . $this->htmlHeaderOptions['class'];
        } else {
            $this->htmlHeaderOptions['class'] = 'bootstrap-widget-header';
        }

        echo CHtml::openTag('div', $this->htmlOptions);

        $this->registerClientScript();
        $this->renderHeader();
        $this->renderContentBegin();
    }

    /**
     *### .run()
     *
     * Widget run - used for closing procedures
     */
    public function run()
    {
        $this->renderContentEnd();
        echo CHtml::closeTag('div') . "\n";

        $this->useViewTemplate("box")->selfRender();
    }

    /**
     *### .renderHeader()
     *
     * Renders the header of the box with the header control (button to show/hide the box)
     */
    public function renderHeader()
    {
        if ($this->title !== false || $this->headerPrependTitle !== false || $this->headerAppendTitle !== false) {
            echo CHtml::openTag('div', $this->htmlHeaderOptions);

            $this->renderMenu();
            $this->_renderPrependTitle();

            if ($this->title) {
                $this->title = '<h3 class="bootstrap-widget-header__title">' . $this->title . '</h3>';
                if ($this->headerIcon) {
                    $this->title = '<i class="' . $this->headerIcon . '"></i>' . $this->title;
                }
                echo $this->title;
            }
            $this->_renderAppendTitle();
            $this->renderButtons();

            echo CHtml::closeTag('div');
        }
    }

    private function _renderAppendTitle() {
        if (!$this->headerAppendTitle) {
            return;
        }

        echo $this->headerAppendTitle;
    }

    private function _renderPrependTitle() {
        if (!$this->headerPrependTitle) {
            return;
        }

        echo $this->headerPrependTitle;
    }

    /**
     *### .renderButtons()
     *
     * Renders a header buttons to display the configured actions
     */
    public function renderButtons()
    {
        if (empty($this->headerButtons)) {
            return;
        }

        echo '<div class="bootstrap-toolbar pull-right">';

        if (!empty($this->headerButtons) && is_array($this->headerButtons)) {
            foreach ($this->headerButtons as $button) {
                $options = $button;
                $button = $options['class'];
                unset($options['class']);

                if (strpos($button, 'TbButton') === false) {
                    throw new CException('message');
                }

                if (!isset($options['htmlOptions'])) {
                    $options['htmlOptions'] = array();
                }

                $class = isset($options['htmlOptions']['class']) ? $options['htmlOptions']['class'] : '';
                $options['htmlOptions']['class'] = $class . ' pull-right';

                $this->controller->widget($button, $options);
            }
        }

        echo '</div>';
    }

    /*
     *### .renderContentBegin()
     *
     * Renders the opening of the content element and the optional content
     */
    public function renderContentBegin()
    {
        echo CHtml::openTag('div', $this->htmlContentOptions);
        if (!empty($this->content)) {
            echo $this->content;
        }
    }

    /*
     *### .renderContentEnd()
     *
     * Closes the content element
     */
    public function renderContentEnd()
    {
        echo CHtml::closeTag('div');
    }

    /**
     *### .registerClientScript()
     *
     * Registers required script files (CSS in this case)
     */
    public function registerClientScript()
    {
        Yii::app()->clientScript->registerPackage('bootstrap-box');
        Yii::app()->bootstrap->registerAssetCss('bootstrap-box.css');
    }

    public function getActions() {
        return $this->actions;
    }

    public function renderMenu() {
        $menuItems = array();

        foreach ($this->actions as $actName => $actParams) {
            $this->_jsParams['actions'][$actionLinkId = 'actionLink_' . $actionLinkId = SessionUniqGenerator::get()] = array(
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

        if (!$menuItems) {
            return;
        }

        echo CHtml::openTag("div", array("class" => "bootstrap-widget-header__menu"));

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

        echo CHtml::closeTag("div");
    }

    public function getJsParams() {
        return $this->_jsParams;
    }
} 