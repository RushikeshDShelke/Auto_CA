<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException as SymfonyProcessFailedException;

class Data extends AbstractHelper
{
    const REGX_CWEBP = '/^[a-zA-Z0-9-_\s]+$/mi';
    const REGX_IMAGEMAGICK = '/^[a-zA-Z0-9-_\s,=:]+$/mi';
    const REGX_CWEBP_PATH = '/(^[a-zA-Z0-9\/_\-\.]+cwebp$|^cwebp$)/mi';
    const REGX_IMAGEMAGICK_PATH = '/(^[a-zA-Z0-9\/_\-\.]+convert$|^convert$)/mi';

    protected $storeManager;

    protected $filesystem;

    protected $newFile;

    protected $ioFile;

    protected $imageQuality;

    protected $fileHelper;

    public function __construct(
        Context $context,
        ObjectManager $objectManager,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        Filesystem\Io\File $ioFile,
        File $fileHelper
    )
    {
        $this->ioFile = $ioFile;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->fileHelper = $fileHelper;
        parent::__construct($context);
    }

    /**
     * Convert Image to Webp Image
     *
     * @param $imageUrl
     * @return string
     */
    public function convert($imageUrl)
    {
        $webpUrl = $this->getWebpNameFromImage($imageUrl);
        $webpPath = $this->getImagePathFromUrl($webpUrl);
        $this->newFile = $webpPath;
        $folder = dirname($webpPath);
        $this->createFolderIfNotExist($folder);
        $imagePath = $this->getImagePathFromUrl($imageUrl);
        if (empty($imagePath)) {
            return $imageUrl;
        }

        if (!file_exists($imagePath)) {
            return $imageUrl;
        }

        if (!$this->isEnabled()) {
            return $imageUrl;
        }

        if (file_exists($webpPath)) {
            return $webpUrl;
        }

        // Detect alpha-transparency in PNG-images and skip it
        if ($this->hasCheckTransparency() && $this->hasAlphaTransparency($imagePath)) {
            return $imageUrl;
        }
        switch ($this->scopeConfig->getValue('webp/advance_mode/convert_tool', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            case 'cwebp':
                $this->newFile = $this->convertToWebpViaCwebp($imagePath, $webpPath);
                break;
            case 'convert':
                $this->newFile = $this->convertToWebpViaImageMagick($imagePath, $webpPath);
                break;
            case 'gd' :
                $this->newFile = $this->convertToWebpViaGd($imagePath, $webpPath);
                break;
        }

        $webpUrl = $this->getImageUrlFromPath($this->newFile);
        return $webpUrl;

    }

    /**
     * Method to convert an image to WebP using the GD method
     *
     * @param $imagePath
     * @param $webpPath
     *
     * @return bool
     */
    public function convertToWebpViaGd($imagePath, $webpPath)
    {
        if ($this->hasGdSupport() == false) {
            return $imagePath;
        }
        $imageData = file_get_contents($imagePath);

        try {
            $image = imagecreatefromstring($imageData);
            imagepalettetotruecolor($image);
        } catch (\Exception $ex) {
            return false;
        }

        imagewebp($image, $webpPath, $this->imageQuality());

        return $webpPath;

    }

    /**
     * Method to convert an image to WebP using the Imagemagick command
     *
     * @param $imagePath
     * @param $webpPath
     * @return string
     */
    public function convertToWebpViaImageMagick($imagePath, $webpPath)
    {
        if ($this->isLoadedImageMagick()) {
            $customCommand = $this->scopeConfig->getValue('webp/advance_mode/imagemagick_command', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $pathCommand = $this->scopeConfig->getValue('webp/advance_mode/path_to_imagemagick', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $cmd = $pathCommand != null ? $pathCommand : 'convert';
            try {
                if ($customCommand != null) {
                    $process = new SymfonyProcess(escapeshellarg($cmd) . ' ' . $imagePath . ' ' . $customCommand . ' ' . $webpPath);
                } else {
                    $process = new SymfonyProcess(escapeshellarg($cmd) . ' ' . $imagePath . ' -quality ' . $this->imageQuality() . ' -define webp:lossless=false,method=6,segments=4,sns-strength=80,auto-filter=true,filter-sharpness=0,filter-strength=25,filter-type=1,alpha-compression=1,alpha-filtering=fast,alpha-quality=100 ' . $webpPath);
                }
                $process->mustRun();
            } catch (SymfonyProcessFailedException $exception) {
                return $imagePath;
            }

            if (file_exists($webpPath)) {
                return $webpPath;
            }
        }
        return $imagePath;
    }

    /**
     * Method to convert an image to WebP using the Cwebp command
     *
     * @param $imagePath
     * @param $webpPath
     * @return string
     */
    public function convertToWebpViaCwebp($imagePath, $webpPath)
    {
            $customCommand = $this->scopeConfig->getValue('webp/advance_mode/cwebp_command', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $pathCommand = $this->scopeConfig->getValue('webp/advance_mode/path_to_cwebp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $cmd = $pathCommand != null ? $pathCommand : 'cwebp';
            try {
                if ($customCommand != null) {
                    $process = new SymfonyProcess(escapeshellarg($cmd) . ' ' . $imagePath . ' ' . $customCommand . ' ' . $webpPath);
                }
                else {
                    $process = new SymfonyProcess(escapeshellarg($cmd) . ' ' . $imagePath . ' -q ' . $this->imageQuality() . ' -alpha_q 100 -z 9 -m 6 -segments 4 -sns 80 -f 25 -sharpness 0 -strong -pass 10 -mt -alpha_method 1 -alpha_filter fast -o ' . $webpPath);
                }
                $process->mustRun();
            } catch (SymfonyProcessFailedException $exception) {
                return $imagePath;
            }

            if (file_exists($webpPath)) {
                return $webpPath;
            }

        return $imagePath;
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return $this->scopeConfig->getValue('webp/setting/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return bool
     */
    public function imageQuality($store = null)
    {
        if (!$this->imageQuality) {
            $this->imageQuality = $this->scopeConfig->getValue('webp/advance_mode/quality', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        }
        return $this->imageQuality;
    }

    /**
     * @return bool
     */
    public function hasGdSupport()
    {
        if (!function_exists('imagewebp')) {
            return false;
        }

        return true;
    }

    public function isLoadedImageMagick()
    {
        if (extension_loaded('imagick')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the WebP path equivalent of an image path
     *
     * @param $image
     *
     * @return mixed
     */
    public function getWebpNameFromImage($image)
    {
        $image = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $image);
        $image = str_replace('pub/media/', 'pub/media/webp_image/', $image);
        return $image;
    }

    /**
     * @return array
     */
    public function getSystemPaths()
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $systemPaths = array(
            'pub' => array(
                'url' => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA),
                'urlWithoutBaseUrl' => str_replace($baseUrl, '', $mediaUrl),
                'path' => $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath())
        );
        return $systemPaths;
    }

    /**
     * @param string $imageUrl
     *
     * @return mixed
     */
    public function getImagePathFromUrl($imageUrl)
    {
        $systemPaths = $this->getSystemPaths();

        foreach ($systemPaths as $systemPath) {
            if (strstr($imageUrl, $systemPath['url'])) {
                // if url have base url. ex: https://example.com/pub/media/..
                return str_replace($systemPath['url'], $systemPath['path'], $imageUrl);
            } elseif (strstr($imageUrl, $systemPath['urlWithoutBaseUrl'])) {
                // if url don't have base url. ex: /pub/media/..
                if (strpos($imageUrl, '/pub/media/') !== false) {
                    $imageUrl = str_replace('/pub/media/', 'pub/media/', $imageUrl);
                }
                // replace pub/media with system path
                return str_replace($systemPath['urlWithoutBaseUrl'], $systemPath['path'], $imageUrl);
            }
        }
        return false;
    }

    /**
     * @param string $imagePath
     *
     * @return mixed
     */
    public function getImageUrlFromPath($imagePath)
    {
        $systemPaths = $this->getSystemPaths();
        if (!preg_match('/^http/', $imagePath)) {
            foreach ($systemPaths as $systemPath) {
                if (strstr($imagePath, $systemPath['path'])) {
                    return str_replace($systemPath['path'], $systemPath['url'], $imagePath);
                }
            }
        }
        return false;
    }

    public function hasCheckTransparency($store = null)
    {
        return $this->scopeConfig->getValue('webp/setting/check_transparency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Detect whether an image has PNG alpha transparency
     *
     * @param $image
     *
     * @return bool
     */
    public function hasAlphaTransparency($image)
    {
        if (empty($image)) {
            return false;
        }

        if (file_exists($image) == false) {
            return false;
        }

        if (preg_match('/\.(jpg|jpeg)$/', $image)) {
            return false;
        }

        $fileIo = $this->fileHelper;
        $fileIo->setCwd(dirname($image));
        $fileIo->setIwd(dirname($image));

        $imageContents = $fileIo->read($image);
        $colorType = ord(substr($imageContents, 25, 1));

        if ($colorType == 6 || $colorType == 4) {
            return true;
        } elseif (stripos($imageContents, 'PLTE') !== false && stripos($imageContents, 'tRNS') !== false) {
            return true;
        }

        return false;
    }

    public function createFolderIfNotExist($path)
    {
        if (!is_dir($path)) {
            $ioAdapter = $this->ioFile;
            $ioAdapter->mkdir($path, 0775);
        }
    }

    public function deleteFile($filePath)
    {
        if(file_exists($filePath)) {
            $ioAdapter = $this->ioFile;
            $ioAdapter->rm($filePath);
        }
    }

    /**
     * @param $folderPath
     *
     * @return bool|string
     */
    public function removeFolder($folderPath)
    {
        if (is_dir($folderPath)) {
            if (is_writable($folderPath)) {
                $ioAdapter = $this->ioFile;
                $ioAdapter->rmdir($folderPath, true);
            } else {
                return false;
            }
        } else {
            return 'nowebpFolder';
        }
    }


    public function clearTestWebpFolder()
    {
        $webpFolder = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'webp_image/test';
        return $this->removeFolder($webpFolder) ;
    }

    public function clearWebp()
    {
        $webpFolder = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'webp_image';
        return $this->removeFolder($webpFolder) ;
    }

    public function getExcludeImageAttribute($store = null)
    {
        return $this->scopeConfig->getValue('webp/professional_mode/exclude_img', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    public function getCustomSrcTag($store = null)
    {
        return $this->scopeConfig->getValue('webp/professional_mode/src_tag', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    public function getCustomSrcSetTag($store = null)
    {
        return $this->scopeConfig->getValue('webp/professional_mode/srcset_tag', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    public function isNativeLazyLoadingEnabled($store = null) {
        return $this->scopeConfig->getValue('webp/native_lazy/enable_native_lazy', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    public function getExcludeNativeLazyloadImageAttribute($store = null) {
        return $this->scopeConfig->getValue('webp/native_lazy/exclude_native_lazy', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return string
     */
    public function getExcludeNativeLazyloadImageAttributes()
    {
        $excludeImageAttributes = $this->getExcludeNativeLazyloadImageAttribute();
        if ($excludeImageAttributes)
        {
            $excludeImageAttributes = explode(',', $excludeImageAttributes);
            $excludeImageAttributes = array_map('trim', $excludeImageAttributes);
            $excludeImageAttributes = implode(".*|.*", $excludeImageAttributes);
            $excludeImageAttributes = '/(.*data-nowebp=\"true\".*|.*' . $excludeImageAttributes . '.*)/mi';
        } else {
            $excludeImageAttributes = '/(.*data-nowebp=\"true\".*)/mi';
        }

        return $excludeImageAttributes;

    }
}