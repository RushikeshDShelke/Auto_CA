<?php

namespace Cminds\Marketplace\Block\Shipment;

use Cminds\Marketplace\Helper\Data;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\OrderFactory;

class Create extends Template
{
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
     * Create constructor.
     *
     * @param Context                    $context
     * @param Registry                   $registry
     * @param OrderFactory               $orderFactory
     * @param ProductFactory             $productFactory
     * @param Data                       $marketplaceHelper
     * @param Renderer                   $renderer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OrderFactory $orderFactory,
        ProductFactory $productFactory,
        Data $marketplaceHelper,
        Renderer $renderer
    ) {
        parent::__construct($context);

        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
        $this->productFactory = $productFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->renderer = $renderer;

        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Get current order.
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        $id = $this->registry->registry('order_id');

        return $this->orderFactory->create()->load($id);
    }

    /**
     * Get product.
     *
     * @param integer $id
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($id)
    {
        return $this->productFactory->create()->load($id);
    }

    /**
     * Get items from current order.
     *
     * @return array
     */
    public function getItems()
    {
        $order = $this->getOrder();
        $items = [];

        $supplierId = (int)$this->marketplaceHelper->getSupplierId();

        foreach ($order->getAllItems() as $item) {
            $product = $this->getProduct($item->getProductId());
            $productSupplierId = (int)$product->getCreatorId();

            if ($productSupplierId === $supplierId) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Get config value by key.
     *
     * @param string $key
     *
     * @return string
     */
    public function getStoreConfig($key)
    {
        return $this->scopeConfig->getValue($key);
    }

    /**
     * Get shipping address.
     *
     * @return false|string|null
     */
    public function getFormattedShippingAddress()
    {
        if ($this->getOrder()->getShippingAddress() !== null) {
            return $this->renderer->format(
                $this->getOrder()->getShippingAddress(),
                'html'
            );
        }

        return false;
    }

    /**
     * Get billing address.
     *
     * @return null|string
     */
    public function getFormattedBillingAddress()
    {
        return $this->renderer->format(
            $this->getOrder()->getBillingAddress(),
            'html'
        );
    }

    /**
     * Return bool flat if notification about new shipment is enabled or not.
     *
     * @return bool
     */
    public function isNotificationEnabled()
    {
        return (bool)$this->getStoreConfig(
            'configuration_marketplace/presentation/'
            . 'allow_send_emails_to_customers_if_supplier_create_shipment'
        );
    }
}
