<?php

/**
 * Olegnax
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
 * @package     Olegnax_ProductSlider
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\ProductSlider\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\ShortcutButtons\InCatalog\PositionAfter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\CatalogInventory\Helper\Stock;
use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url\Helper\Data;
use Magento\Msrp\Block\Popup;
use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Block\BlockInterface;
use Olegnax\Core\Helper\ProductImage;
use Olegnax\ProductSlider\Model\Config\Source\SortOrder;
use Olegnax\ProductSlider\Model\Config\Source\SortOrderCat;
use Magento\Framework\View\LayoutFactory;

abstract class AbstractShortcode extends AbstractProduct implements BlockInterface, IdentityInterface
{
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;
    /**
     * @var RendererList
     */
    private $rendererListBlock;
    /**
     * @var Context
     */
    protected $httpContext;
    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    protected $catalogProductVisibility;
    /**
     * Product collection factory
     *
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var Data
     */
    protected $urlHelper;
    protected $_atributtes = [
        'title' => '',
        'title_align' => 'center',
        'title_side_line' => false,
        'title_tag' => 'strong',
        'products_count' => 6,
        'columns_desktop' => 4,
        'columns_desktop_small' => 3,
        'columns_table' => 2,
        'columns_mobile' => 1,
        'loop' => false,
        'arrows' => false,
        'dots' => false,
        'nav_position' => 'left-right',
        'dots_align' => 'left',
        'show_title' => true,
        'autoplay' => false,
        'autoplay_time' => '5000',
        'pause_on_hover' => false,
        'show_addtocart' => true,
        'show_wishlist' => true,
        'show_compare' => true,
        'show_review' => true,
		'show_desc' => false,
        'show_in_stock' => true,
        'rewind' => false,
        'sort_order' => '',
		'quickview_position' => '',
		'products_layout_centered' => false,
        'show_swatches' => false,
		'review_count' => false,
    ];
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $json;

    /**
     * AbstractShortcode constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Visibility $catalogProductVisibility
     * @param Context $httpContext
     * @param Data $urlHelper
     * @param array $data
     * @param Json|null $json
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        CollectionFactory $productCollectionFactory,
        Visibility $catalogProductVisibility,
        Context $httpContext,
        Data $urlHelper,
        array $data = [],
        LayoutFactory $layoutFactory = null,
        Json $json = null
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->httpContext = $httpContext;
        $this->urlHelper = $urlHelper;
        $this->layoutFactory = $layoutFactory ?: ObjectManager::getInstance()->get(LayoutFactory::class);
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);

        parent::__construct($context, $data);
    }

    /**
     * @param array $newval
     * @return array
     */
    public function getCacheKeyInfo($newval = [])
    {
        return array_merge([
            'OLEGNAX_PRODUCTSLIDER_PRODUCTS_LIST_WIDGET',
            $this->getPriceCurrency()->getCurrency()->getCode(),
            $this->getStoreId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $this->json->serialize($this->getRequest()->getParams()),
            $this->json->serialize($this->getData()),
        ], $newval);
    }

    /**
     * @return PriceCurrencyInterface|mixed
     */
    private function getPriceCurrency()
    {
        if ($this->priceCurrency === null) {
            $this->priceCurrency = ObjectManager::getInstance()
                ->get(PriceCurrencyInterface::class);
        }
        return $this->priceCurrency;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __call($method, $args)
    {
        if ('get' === substr($method, 0, 3)) {
            $key = $this->_underscore(substr($method, 3));
            if (array_key_exists($key, $this->_atributtes)) {
                $value = $this->_atributtes[$key];
                if ($this->hasData($key)) {
                    $value = $this->getData($key);
                }

                return $value;
            }
        }

        return parent::__call($method, $args);
    }

    /**
     * @param Product $product
     * @param null $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductPriceHtml(
        Product $product,
        $priceType = null,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id']) ? $arguments['price_id'] : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container']) ? $arguments['include_container'] : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price']) ? $arguments['display_minimal_price'] : true;

        /** @var Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }
    /**
     * @inheritdoc
     */
    protected function getDetailsRendererList()
    {
        if (empty($this->rendererListBlock)) {
            /** @var $layout LayoutInterface */
            $layout = $this->layoutFactory->create(['cacheable' => false]);
            $layout->getUpdate()->addHandle('catalog_widget_product_list')->load();
            $layout->generateXml();
            $layout->generateElements();

            $this->rendererListBlock = $layout->getBlock('category.product.type.widget.details.renderers');
        }
        return $this->rendererListBlock;
    }
    /**
     * {@inheritdoc}
     */
//    protected function _beforeToHtml()
//    {
//        $this->setProductCollection($this->createCollection());
//        return parent::_beforeToHtml();
//    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getProductCollection()) {
            foreach ($this->getProductCollection() as $product) {
                if ($product instanceof IdentityInterface) {
                    $identities = array_merge($identities, $product->getIdentities());
                }
            }
        }

        return $identities ?: [Product::CACHE_TAG];
    }

    /**
     * @return Collection
     */
    public function getProductCollection()
    {
        /** @var $collection Collection */
        $visibleProducts = $this->catalogProductVisibility->getVisibleInCatalogIds();
        $collection = $this->productCollectionFactory->create()->setVisibility($visibleProducts);
        $collection->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect('*');
        if ($this->_scopeConfig->getValue(
                Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
                ScopeInterface::SCOPE_STORE
            ) &&
            $this->getShowInStock()) {
            $this->addInStockFilterToCollection($collection);
        }

        return $collection;
    }

    /**
     * @param $collection
     */
    public function addInStockFilterToCollection($collection)
    {
        $this->_loadObject(Stock::class)->addInStockFilterToCollection($collection);
    }

    protected function _loadObject($object)
    {
        return ObjectManager::getInstance()->get($object);
    }

    /**
     * @param Collection $collection
     * @param string $order_attribute
     * @param string $order_dir
     */
    public function addAttributeToSort(
        Collection $collection,
        $order_attribute = "",
        $order_dir = Collection::SORT_ORDER_ASC
    ) {
        $sortOrder = $this->getSortOrder();
        switch ($sortOrder) {
            case SortOrder::FIELD_PRICE_ASC:
                $order_attribute = 'price';
                $order_dir = Collection::SORT_ORDER_ASC;
                break;
            case SortOrder::FIELD_PRICE_DESC:
                $order_attribute = 'price';
                $order_dir = Collection::SORT_ORDER_DESC;
                break;
            case SortOrder::FIELD_CREATED:
                $order_attribute = $sortOrder;
                $order_dir = Collection::SORT_ORDER_DESC;
                break;
            case SortOrderCat::FIELD_POSITION:
            case SortOrder::FIELD_NAME:
                $order_attribute = $sortOrder;
                $order_dir = Collection::SORT_ORDER_ASC;
                break;

        }
        if (!empty($order_attribute)) {
            $collection->addAttributeToSort($order_attribute, $order_dir);
        }
    }

    /**
     * @param array $options
     * @param bool $json
     * @return array|bool|false|string
     */
    public function getCarouselOptions($options = [], $json = true)
    {
        $autoplayTime = (int)$this->getAutoplayTime();
        if (!$autoplayTime || $autoplayTime < 500) {
            $autoplayTime = 500;
        }
        $options['itemClass'] = 'product-item';
        $options['margin'] = (int)$this->getMargin();
        $options['loop'] = (bool)$this->getLoop();
        $options['dots'] = (bool)$this->getDots();
        $options['nav'] = (bool)$this->getNav();
        $options['items'] = (int)$this->getColumnsDesktop();
        $options['autoplay'] = (bool)$this->getAutoplay();
        $options['autoplayTimeout'] = $autoplayTime;
        $options['autoplayHoverPause'] = (bool)$this->getPauseOnHover();
        $options['lazyLoad'] = true;
        $options['rewind'] = (bool)$this->getRewind();
        $options['responsive'] = [
            '0' => [
                'items' => max(1, (int)$this->getColumnsMobile()),
            ],
            '640' => [
                'items' => max(1, (int)$this->getColumnsTablet()),
            ],
            '1025' => [
                'items' => max(1, (int)$this->getColumnsDesktopSmall()),
            ],
            '1160' => [
                'items' => max(1, (int)$this->getColumnsDesktop()),
            ],
        ];

        if ($json) {
            return $this->json->serialize($options);
        }

        return $options;
    }

    /**
     * @return string|string[]|null
     */
    public function getUnderlineNameInLayout()
    {
        $name = $this->getNameInLayout();
        $name = preg_replace('/[^a-zA-Z0-9_]/i', '_', $name);
        $name .= substr(md5(microtime()), -5);

        return $name;
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getMSRPHtml(Product $product)
    {
        if ($this->isMSRP($product)) {
            return $this->getMSRP();
        }

        return '';
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function isMSRP(Product $product)
    {
        $msrp = $product->getData('msrp');

        return null !== $msrp;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMSRP()
    {
        $content = '';
        $block = $this->getLayout()->getBlock('product.tooltip');
        if (!$block) {
            $block = $this->getLayout()->createBlock(
                Popup::class,
                'product.tooltip'
            )->setTemplate('Magento_Msrp::popup.phtml');
            $_block = $this->getLayout()->createBlock(
                PositionAfter::class,
                'map.shortcut.buttons'
            );
            $block->setChild($_block->getNameInLayout(), $_block);
            $content = $block->toHtml();
        }

        return $content;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * @param $product
     * @param $imageId
     * @param array $attributes
     * @return mixed
     */
    public function getLazyImage($product, $imageId, $attributes = [])
    {
        return $this->_loadObject(ProductImage::class)->getImage(
            $product,
            $imageId,
            'Olegnax_ProductSlider::product/image_with_borders.phtml',
            $attributes
        );
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->addData([
            'cache_lifetime' => 86400,
        ]);
        parent::_construct();
    }

}
