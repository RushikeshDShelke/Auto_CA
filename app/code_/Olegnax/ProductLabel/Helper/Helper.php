<?php /** @noinspection PhpDeprecationInspection */
/**
 * Olegnax ProductLabel
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_ProductLabel
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */
declare(strict_types=1);

namespace Olegnax\ProductLabel\Helper;

use DateTime;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Core\Helper\Helper as CoreHelperHelper;

class Helper extends CoreHelperHelper
{
    const CONFIG_MODULE = 'olegnax_productlabel';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;
    /**
     * @var CollectionFactory
     */
    protected $reportBestsellers;
    /**
     * @var array|int[]
     */
    private $_bestsellerIds;
    /**
     * @var int
     */
    private $storeCode;
    /**
     * @var array|array[]
     */
    private $cacheCondition = [];

    /**
     * @param string $method
     * @param array $args
     *
     * @return bool|string
     * @throws LocalizedException
     */
    public function __call($method, $args)
    {
        if (preg_match('/^showLabel(.+)$/', $method, $matches)) {
            $label = strtolower($matches[1]);
            $product = isset($args[0]) ? $args[0] : null;

            return $this->getLabel($product, $label);
        }
        if (preg_match('/^isEnabledLabel(.+)$/', $method, $matches)) {
            $label = strtolower($matches[1]);

            return $this->isEnabledLabel($label);
        }

        throw new LocalizedException(
            new Phrase('Invalid method %1::%2', [get_class($this), $method])
        );
    }

    /**
     * @param Product $product
     * @param string $label
     *
     * @return string
     */
    public function getLabel($product, $label)
    {
        if ($this->isEnabledLabel($label) && $this->isCondition($product, $label)) {
            $style = $this->prepareStyle([
                'color' => $this->getLabelAttr($label, 'color'),
                'background-color' => $this->getLabelAttr($label, 'background_color'),
            ]);
            if (!empty($style)) {
                $style = ' style="' . $style . '"';
            }

            $text = $this->getLabelAttr($label, 'text');
            if (false !== strpos($text, '{{')) {
                $text = $this->prepareText($text, $product);
            }
            $text = trim($text);

            if (empty($text)) {
                return '';
            }

            return '<span class="ox-product-label-' . $label . '"' . $style . '>' . $text . '</span>';
        }

        return '';
    }

    /**
     * @param string $label
     *
     * @return bool
     */
    public function isEnabledLabel($label)
    {
        return (bool)$this->isEnabled() && $this->getModuleConfig('label/' . $label);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getModuleConfig('general/enable');
    }

    /**
     * @param string $path
     * @param null|int $storeCode
     * @param string $scopeType
     *
     * @return mixed
     */
    public function getModuleConfig($path = '', $storeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return parent::getModuleConfig($path, $this->getStoreId());
    }

    /**
     * @return int
     */
    private function getStoreId()
    {
        if (empty($this->storeCode)) {
            $this->storeCode = $this->getStore()->getId();
        }

        return $this->storeCode;
    }

    /**
     * @param Product $product
     * @param string $label
     *
     * @return bool|mixed
     */
    protected function isCondition($product, $label)
    {
        $result = false;
        $productId = $product->getId();
        if (isset($this->cacheCondition[$productId]) && isset($this->cacheCondition[$productId][$label])) {
            return $this->cacheCondition[$productId][$label];
        }

        switch ($label) {
            case 'bestseller':
                $result = $this->isConditionBestseller($productId);
                break;
            case 'custom':
                $result = $this->isConditionCustom($product);
                break;
            case 'featured':
                $result = $this->isConditionFeatured($product);
                break;
            case 'new':
                $result = $this->isConditionNew($product);
                break;
            case 'sale':
                $result = $this->isConditionSale($product);
                break;
            default:
                $method = 'isCondition' . ucfirst($label);
                if (method_exists($this, $method)) {
                    $result = call_user_func([$this, $method], $product);
                }
        }
        $this->cacheCondition[$productId][$label] = $result;

        return $result;
    }

    /**
     * @param int $productId
     *
     * @return bool
     */
    public function isConditionBestseller($productId)
    {
        return in_array($productId, $this->getBestsellerIds());
    }

    /**
     * @return array
     */
    protected function getBestsellerIds()
    {
        if (empty($this->_bestsellerIds)) {
            $this->_bestsellerIds = [];
            $collection = $this->getReportBestsellers()->create();
            if ($this->getModuleConfig('label/bestseller_period')) {
                $collection
                    ->addFieldToFilter(
                        'period',
                        [
                            'date' => true,
                            'from' => (new DateTime())
                                ->modify('-1 ' . $this->getModuleConfig('label/bestseller_period'))
                                ->setTime(0, 0, 0)
                                ->getTimestamp(),
                        ]
                    );
            }

            $bestsellers = $collection
                ->setOrder('MIN(rating_pos)', Collection::SORT_ORDER_ASC)
                ->setPageSize(
                    max(
                        (int)($this->getModuleConfig('label/bestseller_count') ?: 200),
                        10
                    )
                );
            foreach ($bestsellers as $item) {
                $this->_bestsellerIds[] = $item->getProductId();
            }
        }

        return $this->_bestsellerIds;
    }

    /**
     * @return CollectionFactory
     */
    protected function getReportBestsellers()
    {
        if (!$this->reportBestsellers) {
            $this->reportBestsellers = $this->_loadObject(CollectionFactory::class);
        }

        return $this->reportBestsellers;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function isConditionCustom($product)
    {
        return (bool)$product->getData('ox_custom');
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function isConditionFeatured($product)
    {
        return (bool)$product->getData('ox_featured') || $product->getData('thm_featured');
    }

    /**
     * @param Product $product
     *
     * @return bool
     * @noinspection PhpUndefinedMethodInspection
     */
    public function isConditionNew($product)
    {
        $productFrom = $product->getNewsFromDate();
        $productTo = $product->getNewsToDate();
        $productFrom = '0000-00-00 00:00:00' === $productFrom ? '' : $productFrom;
        $productTo = '0000-00-00 00:00:00' === $productTo ? '' : $productTo;

        if (!empty($productFrom) && !empty($productTo)) {
            return ($this->getEndDate() >= $productFrom && $this->getDate() <= $productTo);
        } elseif (!empty($productFrom) && empty($productTo)) {
            return ($this->getEndDate() >= $productFrom);
        } elseif (empty($productFrom) && !empty($productTo)) {
            return ($this->getDate() <= $productTo);
        }

        $productType = $product->getTypeID();
        switch ($productType) {
            case 'bundle':
            case 'grouped':
                $childs = $product->getTypeInstance()->getAssociatedProducts($product);
                foreach ($childs as $child) {
                    if ($this->isConditionNew($child)) {
                        return true;
                    }
                }
                break;
            case 'configurable':
                $childs = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($childs as $child) {
                    if ($this->isConditionNew($child)) {
                        return true;
                    }
                }
                break;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getEndDate()
    {
        return $this->getDate('23:59:59');
    }

    /**
     * @param string $time
     *
     * @return string
     */
    protected function getDate($time = '0:0:0')
    {
        return $this->getDateTime()->date(null, $time);
    }

    /**
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected function getDateTime()
    {
        if (!$this->dateTime) {
            $this->dateTime = $this->_loadObject(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        }

        return $this->dateTime;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function isConditionSale($product)
    {
        switch ($product->getTypeID()) {
            case 'grouped':
                $childs = $product->getTypeInstance()->getAssociatedProducts($product);
                foreach ($childs as $child) {
                    if ($this->isConditionSale($child)) {
                        return true;
                    }
                }
                break;
            case 'configurable':
                $childs = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($childs as $child) {
                    if ($this->isConditionSale($child)) {
                        return true;
                    }
                }
                break;
            default:
                $specialPrice = $product->getSpecialPrice();
                if (!empty($specialPrice) && $specialPrice < $product->getPrice()) {
                    $productFrom = $product->getSpecialFromDate();
                    $productTo = $product->getSpecialToDate();
                    $productFrom = '0000-00-00 00:00:00' === $productFrom ? '' : $productFrom;
                    $productTo = '0000-00-00 00:00:00' === $productTo ? '' : $productTo;

                    if (!empty($productFrom) && !empty($productTo)) {
                        return ($this->getEndDate() >= $productFrom && $this->getDate() <= $productTo);
                    } elseif (!empty($productFrom) && empty($productTo)) {
                        return ($this->getEndDate() >= $productFrom);
                    } elseif (empty($productFrom) && !empty($productTo)) {
                        return ($this->getDate() <= $productTo);
                    }
                }
        }

        return false;
    }

    /**
     * @param array $style
     * @param string $separatorValue
     * @param string $separatorAttribute
     *
     * @return string
     */
    public function prepareStyle(array $style, $separatorValue = ': ', $separatorAttribute = ';')
    {
        $style = array_filter($style);
        if (empty($style)) {
            return '';
        }
        foreach ($style as $key => &$value) {
            $value = $key . $separatorValue . $value;
        }
        $style = implode($separatorAttribute, $style);

        return $style;
    }

    /**
     * @param string $label
     * @param string $attribute
     *
     * @return mixed
     */
    protected function getLabelAttr($label, $attribute = '')
    {
        return $this->getModuleConfig(sprintf('label/%s_%s', $label, $attribute));
    }

    /**
     * @param string $text
     * @param Product $product
     *
     * @return string|string[]
     */
    protected function prepareText($text, $product)
    {
        $search = [];
        $replace = [];

        $templates = $this->templateReplace($product);
        $index = 0;
        if (is_array($templates) && !empty($templates)) {
            foreach ($templates as $key => $value) {
                $search[$index] = '{{' . $key . '}}';
                $replace[$index] = $value;
                $index++;
            }
        }

        if (preg_match_all('/{{attribute:([^:}]+)}}/im', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $matche) {
                $search[$index] = $matche[0];
                $replace[$index] = $product->getData($matche[1]);
                $index++;
            }
        }

        return str_replace($search, $replace, $text);
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    public function templateReplace($product)
    {
        $discount = '';
        $discount_percent = '';
        $special_price = '';

        $productType = $product->getTypeID();
        switch ($productType) {
            case 'grouped':
                $childs = $product->getTypeInstance()->getAssociatedProducts($product);
                $_discount = [];
                $_discount_percent = [];
                $_special_price = [];
                foreach ($childs as $child) {
                    $specialPrice = $child->getSpecialPrice();
                    if (!empty($specialPrice)) {
                        $_special_price[] = $specialPrice;
                        $_discount[] = $child->getPrice() - $specialPrice;
                        $_discount_percent[] = round((1 - $specialPrice / $child->getPrice()) * 100);
                    }
                }
                if (1 == count($_discount)) {
                    $discount = array_shift($_discount);
                    $discount = $this->convertValute($discount);
                    $discount_percent = array_shift($_discount_percent) . '%';
                    $special_price = array_shift($_special_price);
                } elseif (1 < count($_discount)) {
                    $minDiscount = min($_discount);
                    $maxDiscount = max($_discount);
                    $minDiscount = $this->convertValute($minDiscount);
                    $maxDiscount = $this->convertValute($maxDiscount);
                    if ($minDiscount !== $maxDiscount) {
                        $discount = $minDiscount . '-' . $maxDiscount;
                    } else {
                        $discount = $maxDiscount;
                    }
                    $minDiscount = min($_discount_percent);
                    $maxDiscount = max($_discount_percent);
                    if ($minDiscount !== $maxDiscount) {
                        $discount_percent = min($_discount_percent) . '%-' . max($_discount_percent) . '%';
                    } else {
                        $discount_percent = max($_discount_percent) . '%';
                    }
                    $special_price = min($_special_price);
                }
                break;
            case 'configurable':
                $childs = $product->getTypeInstance()->getUsedProducts($product);
                $_discount = [];
                $_discount_percent = [];
                $_special_price = [];
                foreach ($childs as $child) {
                    $specialPrice = $child->getSpecialPrice();
                    if (!empty($specialPrice)) {
                        $_special_price[] = $specialPrice;
                        $_discount[] = $child->getPrice() - $specialPrice;
                        $_discount_percent[] = round((1 - $specialPrice / $child->getPrice()) * 100);
                    }
                }
                if (1 == count($_discount)) {
                    $discount = array_shift($_discount);
                    $discount = $this->convertValute($discount);
                    $discount_percent = array_shift($_discount_percent) . '%';
                    $special_price = array_shift($_special_price);
                } elseif (1 < count($_discount)) {
                    $minDiscount = min($_discount);
                    $maxDiscount = max($_discount);
                    $minDiscount = $this->convertValute($minDiscount);
                    $maxDiscount = $this->convertValute($maxDiscount);
                    if ($minDiscount !== $maxDiscount) {
                        $discount = $minDiscount . '-' . $maxDiscount;
                    } else {
                        $discount = $maxDiscount;
                    }
                    $minDiscount = min($_discount_percent);
                    $maxDiscount = max($_discount_percent);
                    if ($minDiscount !== $maxDiscount) {
                        $discount_percent = $minDiscount . '%-' . $maxDiscount . '%';
                    } else {
                        $discount_percent = $maxDiscount . '%';
                    }
                    $special_price = min($_special_price);
                }
                break;
            case 'bundle':
                $specialPrice = $product->getSpecialPrice();
                if (!empty($specialPrice)) {
                    $discount_percent = round(100 - $specialPrice) . '%';
                }
                break;
            case 'simple':
            default:
                $specialPrice = $product->getSpecialPrice();
                if (!empty($specialPrice)) {
                    $special_price = $specialPrice;
                    $discount = $product->getPrice() - $specialPrice;
                    $discount = $this->convertValute($discount);

                    if ($product->getPrice() > 0) {
                        $discount_percent = round((1 - $specialPrice / $product->getPrice()) * 100) . '%';
                    }
                }
        }

        return [
            'discount' => $discount,
            'discount_percent' => $discount_percent,
            'special_price' => $this->convertValute($special_price),
        ];
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function convertValute($value)
    {
        $value = floatval($value);
        $value = number_format($value, 2, ',', ' ');

        return $value;
    }

    /**
     * @return bool|Cache|string
     */
    public function showLabelsProduct()
    {
        $labelsPosition = 'ox-product-labels--' . $this->getModuleConfig('label/label_position_product');
        $product = $this->getProduct();
        if ($product) {
            return $this->showLabels($product, $labelsPosition);
        }

        return '';
    }

    /**
     * @return Product|null
     */
    protected function getProduct()
    {
        $register = $this->_loadObject(Registry::class);
        $product = $register->registry('product');
        if ($product) {
            return $product;
        }
        $product = $register->registry('current_product');
        if ($product) {
            return $product;
        }

        return null;
    }

    /**
     * @param Product $product
     * @param null $labelsPosition
     *
     * @return bool|Cache|string
     */
    public function showLabels($product, $labelsPosition = null)
    {
        if (!$this->isEnabled()) {
            return '';
        }
        $_labelsPosition = $labelsPosition;
        $productId = $product->getId();
        $html = $this->getCacheHtml($productId, $_labelsPosition);
        if (!empty($html)) {
            return $html;
        }
        $labels = [];
        foreach (['new', 'sale', 'featured', 'bestseller', 'custom'] as $label) {
            $labels[] = $this->getLabel($product, $label);
        }
        if (empty($labelsPosition)) {
            $labelsPosition = 'ox-product-labels--' . $this->getModuleConfig('label/label_position');
        }
        $labelsOutput = implode('', $labels);
        if ($labelsOutput) {
            $labelsOutput = '<div class="ox-product-labels-wrapper ' . $labelsPosition . '">' . $labelsOutput . '</div>';
        }
        $this->setCacheHtml($labelsOutput, $productId, $_labelsPosition);

        return $labelsOutput;
    }

    /**
     * @param int $productId
     * @param $labelsPosition
     *
     * @return bool|Cache|string
     */
    protected function getCacheHtml($productId, $labelsPosition)
    {
        /** @var Cache $cache */
        $cache = $this->_loadObject(Cache::class);
        $cache = $cache->load($cache->getId('showLabels', [$productId, $labelsPosition, $this->getStoreId()]));

        return empty($cache) ? "" : $cache;
    }

    /**
     * @param string $html
     * @param int $productId
     * @param $labelsPosition
     *
     * @return bool
     */
    protected function setCacheHtml($html, $productId, $labelsPosition)
    {
        /** @var Cache $cache */
        $cache = $this->_loadObject(Cache::class);

        return $cache->save(
            $html,
            $cache->getId('showLabels', [$productId, $labelsPosition, $this->getStoreId()])
        );
    }

    /**
     * @param string $date
     *
     * @return DateTime|false
     */
    protected function convDate($date)
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', $date);
    }
}
