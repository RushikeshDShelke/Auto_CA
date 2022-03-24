<?php

namespace Cminds\Marketplace\Block\Shipment;

use Cminds\Marketplace\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory as TrackCollectionFactory;
use Magento\Shipping\Model\Config as ShippingConfig;

class View extends Template
{
    /**
     * Array with carriers.
     *
     * @var array
     */
    private $carriers;

    /**
     * @var Data
     */
    protected $marketplaceHelper;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Shipment
     */
    protected $shipment;

    /**
     * @var ShippingConfig
     */
    protected $shippingConfig;

    /**
     * @var TrackCollectionFactory
     */
    protected $trackCollectionFactory;

    /**
     * View constructor.
     *
     * @param Context                $context
     * @param Registry               $registry
     * @param OrderFactory           $orderFactory
     * @param ProductFactory         $productFactory
     * @param Data                   $marketplaceHelper
     * @param Renderer               $renderer
     * @param Shipment               $shipment
     * @param ShippingConfig         $shippingConfig
     * @param TrackCollectionFactory $trackCollectionFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OrderFactory $orderFactory,
        ProductFactory $productFactory,
        Data $marketplaceHelper,
        Renderer $renderer,
        Shipment $shipment,
        ShippingConfig $shippingConfig,
        TrackCollectionFactory $trackCollectionFactory
    ) {
        parent::__construct($context);

        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
        $this->productFactory = $productFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->renderer = $renderer;
        $this->shipment = $shipment;
        $this->shippingConfig = $shippingConfig;
        $this->trackCollectionFactory = $trackCollectionFactory;

        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Get current shipment.
     *
     * @return mixed
     */
    public function getShipment()
    {
        $id = $this->registry->registry('shipment_id');

        return $this->shipment->load($id);
    }

    /**
     * Get items from current shipment.
     *
     * @return array
     */
    public function getItems()
    {
        $order = $this->getShipment();
        $items = [];

        foreach ($order->getAllItems() as $item) {
            if ($this->checkProductOwn($item->getProductId())) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Check product owner by product id.
     *
     * @param int $id
     *
     * @return bool
     */
    public function checkProductOwn($id)
    {
        $isOwner = $this->marketplaceHelper
            ->isOwner($id);
        if (!$isOwner) {
            return false;
        }

        return true;
    }

    /**
     * Get carriers.
     *
     * @return array
     */
    private function getCarriers()
    {
        if ($this->carriers) {
            $this->carriers = $this->shippingConfig->getActiveCarriers();

            if (!$this->carriers) {
                $this->carriers = [];
            }
        }

        return $this->carriers;
    }

    /**
     * Return carrier name by code.
     *
     * @param $carrierCode
     *
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        $carriers = [
            'custom' => 'Custom',
            'dhl' => 'DHL (Deprecated)',
            'fedex' => 'Federal Express',
            'ups' => 'United Parcel Service',
            'usps' => 'United States Postal Service',
            'dhlint' => 'DHL',
        ];
        if (isset($carriers[$carrierCode])) {
            return $carriers[$carrierCode];
        }

        return '';
    }

    /**
     * Get order.
     *
     * @param int $id
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder($id)
    {
        return $this->orderFactory->create()->load($id);
    }

    /**
     * Get track collection.
     *
     * @param int $id
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\Collection
     */
    public function getTrackResourceCollection($id)
    {
        return $this->trackCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter(
                'parent_id',
                $id
            );
    }

    /**
     * Get shipping address.
     *
     * @param int $orderId
     *
     * @return false|string
     */
    public function getFormatedShippingAddress($orderId)
    {
        if ($this->getOrder($orderId)->getShippingAddress() !== null) {
            return $this->renderer->format(
                $this->getOrder($orderId)->getShippingAddress(),
                'html'
            );
        }

        return false;
    }

    /**
     * Get product.
     *
     * @param int $id
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($id)
    {
        return $this->productFactory->create()->load($id);
    }

    /**
     * Get billing address.
     *
     * @param int $orderId
     *
     * @return null|string
     */
    public function getFormatedBillingAddress($orderId)
    {
        return $this->renderer->format(
            $this->getOrder($orderId)->getBillingAddress(),
            'html'
        );
    }

    /**
     * Get marketplace helper.
     *
     * @return Data
     */
    public function getMarketplaceHelper()
    {
        return $this->marketplaceHelper;
    }
}
