<?php
/**
 * @author Petr Grishin <petr.grishin@grishini.ru>
 */

namespace Yiiapp\Framework\View;

class ClientScript extends \CClientScript {
    /**
     * @var View
     */
    private $contextView;

    /** @var array Карта js библиотек */
    private $jslibs = array();

    /** @var string */
    private $jsNamespace = '';

    private $includesScripts = array();

    private $jsParams = array();
    private $jsPriorities = array();

    /** @var  string Папка с файлами библиотек */
    private $jsLibsPath;

    /**
     * @var array
     */
    private $styles;

    /**
     * @param mixed $jsLibsPath
     */
    public function setJsLibsPath($jsLibsPath) {
        $this->jsLibsPath = $jsLibsPath;
    }

    /**
     * @return mixed
     */
    public function getJsLibsPath() {
        return $this->jsLibsPath;
    }

    /**
     * @param array $jslibs
     */
    public function setJslibs($jslibs) {
        $this->jslibs = $jslibs;
    }

    /**
     * @return array
     */
    public function getJslibs() {
        return $this->jslibs;
    }

    /**
     * @return AssetManager
     */
    private function assertManager() {
        return Yii::app()->assetManager;
    }

    public function init() {
        foreach ($this->corePackages ? : array() as $name => $config) {
            if (!empty($config['sourcePath']) && !isset($config['baseUrl'])) {
                $config['baseUrl'] = $this->assertManager()->publish($config['sourcePath']);
                $this->corePackages[$name] = $config;
            }
        }
    }

    private function _addJsPriority($priority, $link) {
        $this->jsPriorities[(int)$priority][] = $link;
    }

    public function getJsPriorities() {
        return $this->jsPriorities;
    }

    public function registerViewParams($viewName, $link, $params, $priority) {
        $this->jsParams[$link] = array('viewName' => $viewName, 'data' => $params['data'], 'links' => $params['links']);
        $this->_addJsPriority($priority, $link);
        $this->registerScript(md5($viewName . serialize($params)), $this->getJsNamespace() . '.addViewWithParams("' . $viewName . '", "' . $link . '",' . json_encode($params ? : array()) . ', ' . ((int)$priority) . ')', self::POS_HEAD);
    }

    /**
     * Renders the specified core javascript library.
     */
    public function renderCoreScripts() {
        if ($this->coreScripts === null)
            return;
        $cssFiles = array();
        $jsFiles = array();
        foreach ($this->coreScripts as $name => $package) {
            $baseUrl = $this->getPackageBaseUrl($name);
            if (!empty($package['js'])) {
                foreach ($package['js'] as $js) {
                    $js = $this->assertManager()->convert($js, $baseUrl);
                    $jsFiles["$baseUrl/$js"] = "$baseUrl/$js";
                }
            }
            if (!empty($package['css'])) {
                foreach ($package['css'] as $css) {
                    $css = $this->assertManager()->convert($css, $baseUrl);
                    $cssFiles["$baseUrl/$css"] = '';
                }
            }
        }
        // merge in place
        if ($cssFiles !== array()) {
            foreach ($this->cssFiles as $cssFile => $media)
                $cssFiles[$cssFile] = $media;
            $this->cssFiles = $cssFiles;
        }
        if ($jsFiles !== array()) {
            if (isset($this->scriptFiles[$this->coreScriptPosition])) {
                foreach ($this->scriptFiles[$this->coreScriptPosition] as $url => $value)
                    $jsFiles[$url] = $value;
            }
            $this->scriptFiles[$this->coreScriptPosition] = $jsFiles;
        }
    }

    public function useScript($path, $position = self::POS_BEGIN) {
        $scriptPath = $this->assertManager()->publish($path);
        $this->registerScriptFile($scriptPath, $position);
    }

    public function getJsNamespace() {
        if (is_null($this->jsNamespace)) {
            throw new CException('not registered js namespace');
        }
        return $this->jsNamespace;
    }

    /**
     * @param string $value
     */
    public function setJsNamespace($value) {
        $this->jsNamespace = $value;
    }

    public function getUsagesJsParams() {
        return $this->jsParams;
    }

    public function getUsagesScriptsMap() {
        return $this->includesScripts;
    }

    public function useStyle($path, $trowExist = true) {
        $stylePath = $this->assertManager()->publish($path);
        if (file_exists($path . '/style.less')) {
            $fileName = $this->assertManager()->convert("style.less", $stylePath);
            $pathToFile = "$stylePath/$fileName";
            $this->registerCssFile($pathToFile);
            $this->jsRegisterStyle($pathToFile);
            return $this;
        }
        if ($trowExist) {
            throw new CException("Path by $path less not exists in view style");
        }
    }

    /**
     * @param $path
     * @return $this
     */
    private function jsRegisterStyle($path) {
        $key = md5($path);
        $this->registerScript($key, $this->getJsNamespace() . '.registerCss("' . $key . '", "' . $path . '", false)', self::POS_HEAD);
        $this->styles[$key] = $path;
        return $this;
    }

    public function getStyles() {
        return $this->styles;
    }

    public function useLessFile($path, $trowExist = true) {
        $path = Yii::getPathOfAlias('webroot') . "/" . $path;

        if (!file_exists($path) && $trowExist) {
            throw new CException("Path by $path less not exists in view style");
        }

        $this->registerCssFile($this->assertManager()->publish($path));
    }

    public function useAsContext(View $view) {
        if ($this->contextView) {
            throw new CException('Context view must be one');
        }
        $this->contextView = $view;
    }

    public function getContextViewLink() {
        return $this->contextView ? $this->contextView->getJsLink() : '';
    }

    public function isAddedPackage($name) {
        return isset($this->packages[$name]) || isset($this->corePackages[$name]);
    }

    public function addPackage($name, $definition) {
        if (!$this->isAddedPackage($name)) {
            $this->packages[$name] = $definition;
        }
        return $this;
    }

    public function getInlineScripts() {
        $html = "";
        foreach (array(static::POS_HEAD, static::POS_BEGIN, static::POS_END, static::POS_LOAD, static::POS_READY) as $position) {
            if (!empty($this->scripts[$position])) {
                $html .= $this->renderScriptBatch($this->scripts[$position]);
            }
        }
        return $html;
    }

    public function render(&$output = null) {
        if(!$this->hasScripts)
            return;

        $this->renderCoreScripts();

        if(!empty($this->scriptMap))
            $this->remapScripts();

        $this->unifyScripts();

        foreach ($this->scriptFiles as $position) {
            foreach ($position as $script) {
                $pathHash = md5($script);
                $this->registerScript($pathHash, $this->getJsNamespace() . '.usageScript("' . $pathHash . '", "' . $script . '")', self::POS_HEAD);
                $this->includesScripts[$pathHash] = $script;
            }
        }

        foreach ($this->cssFiles as $cssFile => $media) {
            $pathHash = md5($cssFile);
            $this->styles[$pathHash] = $cssFile;
        }

        if ($output) {
            $this->renderHead($output);
            if($this->enableJavaScript) {
                $this->renderBodyBegin($output);
                $this->renderBodyEnd($output);
            }
        }
    }
}
