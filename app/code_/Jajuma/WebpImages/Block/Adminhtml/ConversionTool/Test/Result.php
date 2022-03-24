<?php
/**
 * @author    JaJuMa GmbH <info@jajuma.de>
 * @copyright Copyright (c) 2020 JaJuMa GmbH <https://www.jajuma.de>. All rights reserved.
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Jajuma\WebpImages\Block\Adminhtml\ConversionTool\Test;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;
use Symfony\Component\Process\Process as SymfonyProcess;
use Symfony\Component\Process\Exception\ProcessFailedException as SymfonyProcessFailedException;

class Result extends \Magento\Framework\View\Element\Template
{
    protected $productCollection;

    protected $productRepository;

    protected $directoryList;

    protected $helper;

    protected $moduleReader;

    protected $error = null;

    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Jajuma\WebpImages\Helper\Data $helper,
        DirectoryList $directoryList,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        array $data = []
    ) {
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;
        $this->directoryList = $directoryList;
        $this->helper = $helper;
        $this->moduleReader = $moduleReader;
        parent::__construct($context, $data);
    }

    public function getImageFromLastProduct()
    {
        $productId = $this->getRequest()->getParam('product');
        if ($productId) {
            try
            {
                $product = $this->productRepository->getById($productId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                return null;
            }
        } else {
            $lastProductSku = $this->productCollection->getLastItem()->getSku();
            $product = $this->productRepository->get($lastProductSku);
        }
        return $product->getImage();
    }

    public function convert()
    {
        $convertTool = $this->getRequest()->getParam('convert_tool');

        $productImage = $this->getImageFromLastProduct();
        $this->helper->clearTestWebpFolder();
        $this->helper->createFolderIfNotExist($this->getMediaPath() . '/webp_image/test/');
        $t = time();
        if ($productImage) {
            $productOriginalImagePath = $this->getMediaPath() . '/catalog/product' . $productImage;
            $webpImage = preg_replace('/\.(png|jpg|jpeg)$/i', $t . '.webp', $productImage);
            $webpImage = explode('/', $webpImage);
            $webpImage = end($webpImage);
            $webpPath = $this->getMediaPath() . '/webp_image/test/' . $webpImage;
        } else {
            $modulePath = $this->moduleReader->getModuleDir(
                \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
                'Jajuma_WebpImages'
            );
            $productOriginalImagePath = $modulePath . '/adminhtml/web/images/test.png';
            $webpPath = $this->getMediaPath() . '/webp_image/test/'. $t .'.webp';
        }

        if ($this->helper->hasCheckTransparency() && $this->helper->hasAlphaTransparency($productOriginalImagePath)) {
            return __('Can not convert transparency image!');
        }

        switch ($convertTool) {
            case 'cwebp':
                $newFile = $this->convertToWebpViaCwebp($productOriginalImagePath, $webpPath);
                break;
            case 'convert':
                $newFile = $this->convertToWebpViaImageMagick($productOriginalImagePath, $webpPath);
                break;
            case 'gd' :
                $newFile = $this->convertToWebpViaGd($productOriginalImagePath, $webpPath);
                break;
            default :
                $newFile = false;
                break;
        }

        if ($newFile) {
            $originalUrl = $productImage ? $this->helper->getImageUrlFromPath($productOriginalImagePath) : $this->_assetRepo->getUrl("Jajuma_WebpImages::images/test.png");
            $webpUrl = $this->helper->getImageUrlFromPath($newFile);
            return ['original' => $originalUrl, 'webp' => $webpUrl];
        } else {
            return false;
        }
    }

    public function getMediaPath()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA);
    }

    /**
     * Method to convert an image to WebP using the GD method
     *
     * @param $imagePath
     * @param $webpPath
     *
     * @return bool
     */
    private function convertToWebpViaCwebp($imagePath, $webpPath)
    {
            $customCommand = $this->getRequest()->getParam('cwebp_command');
            $pathCommand = $this->getRequest()->getParam('path_to_cwebp');
            $cmd = $pathCommand != null ? $pathCommand : 'cwebp';
            if (!preg_match(\Jajuma\WebpImages\Helper\Data::REGX_CWEBP_PATH, $cmd)) {
                $this->error = __('Invalid Cwepb Path. Path must only include underscore (_), minus (-), dot (.), slash(/) and alphanumeric characters.');
                return false;
            }
            try {
                if ($customCommand != null) {
                    if (!preg_match(\Jajuma\WebpImages\Helper\Data::REGX_CWEBP, $customCommand)) {
                        $this->error = __('Invalid Cwepb Custom Command. Custom Command must only include underscore (_), minus (-), space ( ) and alphanumeric characters.');
                        return false;
                    } else {
                        $process = new SymfonyProcess(escapeshellarg($cmd) . ' ' . $imagePath . ' ' . $customCommand . ' ' . $webpPath);
                    }
                } else {
                    $process = new SymfonyProcess(escapeshellarg($cmd) . ' ' . $imagePath . ' -q ' . $this->getRequest()->getParam('quality') . ' -alpha_q 100 -z 9 -m 6 -segments 4 -sns 80 -f 25 -sharpness 0 -strong -pass 10 -mt -alpha_method 1 -alpha_filter fast -o ' . $webpPath);
                }
                $process->mustRun();
            } catch (SymfonyProcessFailedException $exception) {
                $this->error = __('Conversion Failed. Please make sure your custom path and command are correct and your quality configuration value is between 0 and 100!');
                return false;
            }

            if (file_exists($webpPath)) {
                return $webpPath;
            } else {
                return false;
            }
    }

    /**
     * Method to convert an image to WebP using the Imagemagick command
     *
     * @param $imagePath
     * @param $webpPath
     * @return string
     */
    private function convertToWebpViaImageMagick($imagePath, $webpPath)
    {
        if ($this->helper->isLoadedImageMagick()) {
            $customCommand = $this->getRequest()->getParam('imagemagick_command');
            $pathCommand = $this->getRequest()->getParam('path_to_imagemagick');
            $cmd = $pathCommand != null ? $pathCommand : 'convert';
            if (!preg_match(\Jajuma\WebpImages\Helper\Data::REGX_IMAGEMAGICK_PATH, $cmd)) {
                $this->error = __('Invalid ImageMagick Path. Path must only include underscore (_), minus (-), dot (.), slash(/) and alphanumeric characters.');
                return false;
            }
            try {
                if ($customCommand != null) {
                    if (!preg_match(\Jajuma\WebpImages\Helper\Data::REGX_IMAGEMAGICK, $customCommand)) {
                        $this->error = __('Invalid ImageMagick Custom Command. Custom Command must only include underscore (_), minus (-), space ( ), comma (,), colon (:), equals sign (=) and alphanumeric characters.');
                        return false;
                    } else {
                        $process = new SymfonyProcess(escapeshellarg($cmd) . ' ' . $imagePath . ' ' . $customCommand . ' ' . $webpPath);
                    }
                } else {
                    $process = new SymfonyProcess(escapeshellarg($cmd). ' ' . $imagePath . ' -quality ' . $this->getRequest()->getParam('quality') . ' -define webp:lossless=false,method=6,segments=4,sns-strength=80,auto-filter=true,filter-sharpness=0,filter-strength=25,filter-type=1,alpha-compression=1,alpha-filtering=fast,alpha-quality=100 ' . $webpPath);
                }
                $process->mustRun();
            } catch (SymfonyProcessFailedException $exception) {
                $this->error = __('Conversion Failed. Please make sure your custom path and command are correct and your quality configuration value is between 0 and 100!');
                return false;
            }

            if (file_exists($webpPath)) {
                return $webpPath;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Method to convert an image to WebP using the GD method
     *
     * @param $imagePath
     * @param $webpPath
     *
     * @return bool
     */
    private function convertToWebpViaGd($imagePath, $webpPath)
    {
        if ($this->helper->hasGdSupport() == false) {
            return false;
        }

        $imageData = file_get_contents($imagePath);

        try {
            $image = imagecreatefromstring($imageData);
            imagepalettetotruecolor($image);
        } catch (\Exception $ex) {
            return false;
        }

        imagewebp($image, $webpPath, $this->getRequest()->getParam('quality'));

        return $webpPath;
    }

    public function getUnsupportWebpImage()
    {
        return $this->getViewFileUrl('Jajuma_WebpImages::images/unsupport_webp.jpg');
    }

    public function getError()
    {
        return $this->error;
    }
}