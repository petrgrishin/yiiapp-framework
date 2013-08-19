<?php
/**
 * @author: Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace Yiiapp\Framework\View;

use Yii;

class AssetManager extends \CAssetManager {
    /**
     * @var array published assets
     */
    private $lessImportDirs = array();
    private $lessWatchChanges = array();
    private $hashForDirs = array();

    /**
     * @return array
     */
    public function getLessImportDirs() {
        return $this->lessImportDirs;
    }

    /**
     * @param array $lessImportDirs
     */
    public function setLessImportDirs($lessImportDirs) {
        $this->lessImportDirs = $lessImportDirs;
    }

    /**
     * @param $dirs
     * @return string
     */
    private function getHashForDirs($dirs) {
        $dirsNamesHash = md5(implode("", $dirs));
        if (isset($this->hashForDirs[$dirsNamesHash])) {
            return $this->hashForDirs[$dirsNamesHash];
        }

        $time = 0;
        foreach ($dirs as $dir) {
            $time += filemtime($dir);
        }

        return $this->hashForDirs[$dirsNamesHash] = sprintf('%x',crc32($time.Yii::getVersion()));
    }

    /**
     * @return lessc
     */
    private function getLessCompiler() {
        return new lessc();
    }

    private function getWebRoot() {
        return Yii::getPathOfAlias('webroot');
    }

    /**
     * @param $assetPath
     * @param $basePath
     * @return mixed
     */
    public function convert($assetPath, $basePath) {
        $pos = strrpos($assetPath, '.');
        if ($pos !== false) {
            $basePath = $this->getWebRoot() . "/$basePath";
            $ext = substr($assetPath, $pos + 1);
            if ($ext == "less") {
                $result = substr($assetPath, 0, $pos + 1) . $this->getHashForDirs($this->getLessWatchChanges()) . ".css";
                if (@filemtime("$basePath/$result") < filemtime("$basePath/$assetPath")) {
                    $lessCompiler = $this->getLessCompiler();
                    $lessCompiler->setImportDir($this->getLessImportDirs());
                    $lessCompiler->compileFile("$basePath/$assetPath", "$basePath/$result");
                }
                return $result;
            }
        }
        return $assetPath;
    }

    /**
     * @return array
     */
    public function getLessWatchChanges() {
        return $this->lessWatchChanges;
    }

    /**
     * @param array $lessWatchChanges
     */
    public function setLessWatchChanges($lessWatchChanges) {
        $this->lessWatchChanges = $lessWatchChanges;
    }
}