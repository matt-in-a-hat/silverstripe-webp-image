<?php

namespace LoveDuckie\SilverStripe\WebPImage\Flysystem;

use SilverStripe\Assets\Flysystem\FlysystemAssetStore as SS_FlysystemAssetStore;
use Imagick;

class LoveDuckieFlysystemAssetStore extends SS_FlysystemAssetStore
{
    private static $webp_default_quality = 80;

    private int $_webp_quality;

    public function __construct()
    {
        $this->_webp_quality = LoveDuckieFlysystemAssetStore::$webp_default_quality;
    }

    public function setFromString($data, $filename, $hash = null, $variant = null, $config = [])
    {
        $fileID = $this->getFileID($filename, $hash);

        if ($this->getPublicFilesystem()->has($fileID) && $filename) {
            $tmpFile = $this->createTemporaryFile($data, $filename);
            $this->convertToWebP($tmpFile, $filename, $hash, $variant);
        }

        return parent::setFromString($data, $filename, $hash, $variant, $config);
    }

    /**
     * @param $path
     * @param $filename
     * @param $hash
     * @param $variant
     * @param $config
     * @return array
     */
    public function setFromLocalFile($path, $filename = null, $hash = null, $variant = null, $config = [])
    {
        if ($filename && empty($config['visibility'] === self::VISIBILITY_PROTECTED)) {
            $this->convertToWebP($path, $filename, $hash, $variant);
        }

        return parent::setFromLocalFile($path, $filename, $hash, $variant, $config);
    }

    /**
     * @param string $sourcePath
     * @param string $filename
     * @param string|null $hash
     * @param $variant
     * @return void
     */
    private function convertToWebP(string $sourcePath, string $filename, ?string $hash, $variant = null): void
    {
        if (!extension_loaded('imagick')) {
            return;
        }

        try {
            $imagick = new Imagick($sourcePath);
            $imagick->setImageFormat('webp');
            $imagick->setImageCompressionQuality($this->_webp_quality);

            $webpPath = $this->getAbsoluteWebPPath($filename, $hash, $variant);
            $this->ensureDirectoryExists(dirname($webpPath));

            $imagick->writeImage($webpPath);
            $imagick->clear();
        } catch (\Exception $e) {
            error_log('Failed to convert image to WebP: ' . $e->getMessage());
        }
    }

    private function createTemporaryFile(string $data, string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $tmpFile = TEMP_PATH . DIRECTORY_SEPARATOR . uniqid('raw_') . '.' . $extension;
        file_put_contents($tmpFile, $data);
        return $tmpFile;
    }

    private function getAbsoluteWebPPath(string $filename, ?string $hash, $variant = null): string
    {
        $relativeWebPPath = $this->createWebPName($this->getAsURL($filename, $hash, $variant));
        return PUBLIC_PATH . DIRECTORY_SEPARATOR . $relativeWebPPath;
    }

    /**
     * @param string $filename
     * @return string
     */
    private function createWebPName(string $filename): string
    {
        $directory = pathinfo($filename, PATHINFO_DIRNAME);
//        $picname = pathinfo($filename, PATHINFO_FILENAME);
        $baseName = pathinfo($filename, PATHINFO_BASENAME);
        return $directory . '/' . $baseName . '.webp';
    }

    /**
     * @param string $directory
     * @return void
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
