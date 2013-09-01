<?
namespace Yiiapp\Framework\Widgets\Table;

use CException;
use CHtml;
use Yiiapp\Framework\Model\WebListCriteria;
use Yiiapp\Framework\Widgets\Widget;
use Yii;

class Filter extends Widget {
    const FIELD_TYPE_TEXT = 'text';
    const FIELD_TYPE_TEXTRANGE = 'textrage';
    const FIELD_TYPE_DATE = 'data';
    const FIELD_TYPE_DATERANGE = 'daterange';
    const FIELD_TYPE_LIST = 'list';
    const FIELD_TYPE_CHECKBOX = 'checkbox';

    static $fTypes = array(
        self::FIELD_TYPE_TEXT,
        self::FIELD_TYPE_TEXTRANGE,
        self::FIELD_TYPE_DATE,
        self::FIELD_TYPE_DATERANGE,
        self::FIELD_TYPE_LIST,
        self::FIELD_TYPE_CHECKBOX
    );

    private
        $data = array(
            "options" => array(
                "presets" => array(
                    'main' => array(
                        "name" => "Фильтр",
                    ),
                )
            ),
        ),
        $uniqKey;

    public function init() {
        $this->setOptions($this->getCriteriaComponent()->filterOptions());
    }

    /**
     * @return WebListCriteria
     */
    public function getCriteriaComponent() {
        /** @var $webListCriteria WebListCriteria */
        $webListCriteria = Yii::app()->webListCriteria;
        return $webListCriteria->useStoreKey($this->uniqKey);
    }

    public function run() {
        $this
            ->useViewTemplate("filter")
            ->selfRender($this->data);
        return $this;
    }

    /**
     * @return array
     */
    public function getFields() {
        return $this->data['fields'];
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields($fields) {
        foreach ($fields as $name => $data) {
            $this->setupField($name, $data);
        }
        return $this;
    }

    /**
     * @param $name
     * @param $data
     * @throws CException
     */
    public function setupField($name, $data) {
        if (!$data['label']) {
            throw new CException($name . ' `label` required in setup field');
        }
        if (!$data['type'] || !in_array($data['type'], static::$fTypes)) {
            throw new CException($name . ' `type` not support or not setup');
        }
        $this->data['fields'][$name] = $data;
    }

    /**
     * @return array
     */
    public function getDefault() {
        return $this->data['default'];
    }

    /**
     * @param array $default
     * @return $this
     */
    public function setDefault($default) {
        $this->data['default'] = $default;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions() {
        return $this->data['options'];
    }

    /**
     * @param array $filterParams
     * @return $this
     */
    public function setOptions($filterParams) {
        if (is_array($filterParams)) {
            $this->data['options'] = $filterParams;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getSaveUrl() {
        return $this->data['saveUrl'];
    }

    /**
     * @param string $saveUrl
     */
    public function setSaveUrl($saveUrl) {
        $this->data['saveUrl'] = $saveUrl;
    }

    /**
     * @return string
     */
    public function getUniqKey() {
        return $this->uniqKey;
    }

    /**
     * @param string $uniqKey
     */
    public function setUniqKey($uniqKey) {
        $this->uniqKey = $uniqKey;
    }
}