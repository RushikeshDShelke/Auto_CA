<?php

namespace Cminds\Marketplace\Block\Invoice;

use Cminds\Marketplace\Helper\Data as DataHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;
use Magento\Sales\Model\OrderFactory;

class Create extends Template
{
    /**
     * Helper object.
     *
     * @var DataHelper
     */
    protected $helper;

    /**
     * Order factory object.
     *
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * Registry object.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Product factory object.
     *
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Address renderer object.
     *
     * @var AddressRenderer
     */
    protected $addressRenderer;

    /**
     * Scope config object.
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Create constructor.
     *
     * @param Context         $context         Context object.
     * @param Registry        $registry        Registry object.
     * @param OrderFactory    $orderFactory    Order factory object.
     * @param ProductFactory  $productFactory  Product factory object.
     * @param DataHelper      $helper          Helper object.
     * @param AddressRenderer $addressRenderer Address renderer object.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        OrderFactory $orderFactory,
        ProductFactory $productFactory,
        DataHelper $helper,
        AddressRenderer $addressRenderer
    ) {
        parent::__construct($context);

        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
        $this->productFactory = $productFactory;
        $this->helper = $helper;
        $this->addressRenderer = $addressRenderer;

        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Get current order.
     *
     * @return Order
     */
    public function getOrder()
    {
        $id = $this->registry->registry('order_id');

        return $this->orderFactory->create()->load($id);
    }

    /**
     * Get product object by id.
     *
     * @param int $id
     *
     * @return Product
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

        $supplierId = (int)$this->helper->getSupplierId();

        foreach ($order->getAllItems() as $item) {
            $product = $this->getProduct($item->getProductId());

            if ((int)$product->getCreatorId() == $supplierId) {
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
     * Get config for send emails to supplier when invoice is create.
     *
     * @return string
     */
    public function getSendEmail()
    {
        return $this->getStoreConfig(
            'configuration_marketplace/presentation/'
            . 'allow_send_emails_to_customers_if_supplier_create_invoice'
        );
    }

    /**
     * Get shipping address.
     *
     * @return false|string
     */
    public function getFormattedShippingAddress()
    {
        if ($this->getOrder()->getShippingAddress() !== null) {
            return $this->addressRenderer->format(
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
        return $this->addressRenderer->format(
            $this->getOrder()->getBillingAddress(),
            'html'
        );
    }
}
