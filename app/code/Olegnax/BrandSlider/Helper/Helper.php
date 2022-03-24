<?php
/**
 * @author      Olegnax
 * @package     Olegnax_BrandSlider
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 * @noinspection PhpDeprecationInspection
 */
declare(strict_types=1);


namespace Olegnax\BrandSlider\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Olegnax\Core\Helper\Helper as CoreHelperHelper;

class Helper extends CoreHelperHelper
{
    const HIDE_PRODUCT_DEFAULT = true;
    const BRANDS_PATH_SMALL = 'wysiwyg/brands/small/';
    const BRANDS_PATH = 'wysiwyg/brands/';

    const CONFIG_MODULE = 'olegnax_brandslider';
    const XML_PATH_ATTRIBUTE_CODE = 'general/attribute_code';

    protected $repository;
    protected $_mediaDirectory;

    /**
     * @var  array|null
     */
    protected $items;
    /**
     * @var array
     */
    private $absolutePath;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var Image
     */
    private $image;
    private $defaultOptions;

    /**
     * Helper constructor.
     * @param Context $context
     * @param Repository $repository
     * @param Escaper $escaper
     * @param Image $image
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        Repository $repository,
        Escaper $escaper,
        Image $image,
        Filesystem $filesystem
    ) {
        $this->repository = $repository;
        $this->escaper = $escaper;
        $this->image = $image;
        $this->_mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        parent::__construct($context);
    }

    /**
     * @param string $itemsList
     * @return array|mixed|null
     * @throws NoSuchEntityException
     */
    public function getItems($itemsList = '')
    {
        if (!isset($this->items[$itemsList]) || empty($this->items[$itemsList])) {
            $this->items[$itemsList] = [];
            $attributeCode = $this->getAttribute();
            if ($attributeCode == '') {
                return $this->items;
            }

            /** @var Option[]|null $options */
            $options = $this->repository->get($attributeCode)->getOptions();
            if (!empty($options) && is_array($options)) {
                array_shift($options);
                $_itemsList = array_filter(array_map('trim', explode(',', (string)$itemsList)));
                if (!empty($_itemsList)) {
                    foreach ($options as &$option) {
                        $label = $this->getDefaultLabel($option->getValue(), $option->getLabel());
                        if (in_array($label, $_itemsList)) {
                            $option->setData('image_name', $this->getFileName($label));
                        }
                    }
                } else {
                    foreach ($options as &$option) {
                        $label = $this->getDefaultLabel($option->getValue(), $option->getLabel());
                        $option->setData('image_name', $this->getFileName($label));
                    }
                }
                $this->items[$itemsList] = $options;
            }
        }

        return $this->items[$itemsList];
    }

    /**
     * @return string
     */
    public function getAttribute()
    {
        return $this->getModuleConfig(self::XML_PATH_ATTRIBUTE_CODE);
    }

    /**
     * @param string $name
     * @param string $path
     * @param bool $returnBool
     * @return bool|string
     */
    public function getFileName($name, $path = self::BRANDS_PATH, $returnBool = false)
    {
        if (!is_array($this->absolutePath)) {
            $this->absolutePath = [];
        }
        if (!array_key_exists($path, $this->absolutePath)) {
            $this->absolutePath[$path] = $this->_mediaDirectory->getAbsolutePath($path);
        }
        $name = str_replace(
            [' ', '\'', '/', ':', '*', '?', '"', '<', '>', '|', '+', '.'],
            '_',
            strtolower($name)
        );
        $absolutePath = $this->absolutePath[$path];
        $paths = glob($absolutePath . $name . '.*');
        if (!empty($paths)) {
            $file_name = basename(array_shift($paths));
            return $path . $file_name;
        }
        if ($returnBool) {
            return false;
        }
        return $path . $name . '.png';
    }

    /**
     * @param Product $product
     * @param bool $isBig
     * @param array $size
     * @param string $class
     * @return string
     */
    public function getProductBrandImage($product, $isBig = false, $size = [], $class = '')
    {
        $attributeCode = $this->getAttribute();
        if (empty($attributeCode)) {
            return '';
        }
		$width = $height = '';
        $attribute = $this->getProductAttributeText($product, $attributeCode);
        if (is_array($attribute)) {
            $attribute = array_shift($attribute);
        }
        if ($attribute) {
            $fileName = $this->getFileName(
                $attribute,
                ($isBig ? self::BRANDS_PATH : self::BRANDS_PATH_SMALL),
                self::HIDE_PRODUCT_DEFAULT
            );
            if ($fileName) {
                $url = $this->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $fileName;
                if (is_array($size)) {
                    $size = array_filter($size);
					$width = ' width="' . $size[0] . '" ';
					$height = ' height="' . $size[1] . '" ';
                }
                if (!empty($size)) {
                    $url = $this->image->adaptiveResize($fileName, $size)->getUrl();
                }
                return '<img
                    src="' . $this->escaper->escapeUrl($url) . '"
                    alt="' . $this->escaper->escapeHtmlAttr($attribute) . '"
					' . $width . '
					' . $height . '
                    class="ox-product-grid__brand-image ' . $this->escaper->escapeHtmlAttr($class) . '" />';
            }
        }
        return '';
    }

    /**
     * @param Product $product
     * @param string $attributeCode
     * @return bool|Phrase
     */
    private function getProductAttributeText($product, $attributeCode)
    {
        return $this->getDefaultLabel($product->getData($attributeCode), $product->getAttributeText($attributeCode));
    }

    /**
     * @param int $value
     * @param bool $default
     * @return bool|string
     */
    private function getDefaultLabel($value, $default = false)
    {
        if (true) { // @todo add option check whether to use default names from admin panel?
            $options = $this->getDefaultOptions();
            if (is_array($options) && isset($options[$value])) {
                return $options[$value];
            }
        }

        return $default;
    }

    /**
     * @return string[]
     */
    private function getDefaultOptions()
    {
        if (empty($this->defaultOptions)) {
            $attributeCode = $this->getAttribute();
            try {
                $defaultOptions = $this->repository
                    ->get($attributeCode)
                    ->getSource()
                    ->getAllOptions(false, true);
            } catch (NoSuchEntityException $e) {
                $defaultOptions = null;
            }
            if (!empty($defaultOptions) && is_array($defaultOptions)) {
                $this->defaultOptions = [];
                foreach ($defaultOptions as $option) {
                    $this->defaultOptions[$option['value']] = $option['label'];
                }
            }
        }
        return $this->defaultOptions;
    }

}
