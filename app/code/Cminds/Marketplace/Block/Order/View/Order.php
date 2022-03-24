<?php

namespace Cminds\Marketplace\Block\Order\View;

use Cminds\Marketplace\Helper\Data;
use Magento\Catalog\Model\ProductFactory as CatalogProduct;
use Magento\Directory\Model\Country;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\Address\Renderer;

class Order extends Template
{
    protected $marketplaceHelper;
    protected $resourceConnection;
    protected $salesOrderItem;
    protected $objectManager;
    protected $salesOrderStatus;
    protected $salesOrder;
    protected $currencyHelper;
    protected $profitsHelper;
    protected $registry;
    protected $product;
    protected $country;
    protected $renderer;

    public function __construct(
        Context $context,
        Registry $registry,
        SalesOrder $salesOrder,
        CatalogProduct $product,
        Data $marketplaceHelper,
        Country $country,
        CurrencyHelper $currencyHelper,
        Renderer $renderer
    ) {
        parent::__construct($context);

        $this->registry = $registry;
        $this->salesOrder = $salesOrder;
        $this->product = $product;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->country = $country;
        $this->currencyHelper = $currencyHelper;
        $this->renderer = $renderer;
    }

    public function getOrder()
    {
        $id = $this->registry->registry('order_id');

        return $this->salesOrder->load($id);
    }

    public function getItems()
    {
        $id = $this->registry->registry('order_id');
        $order = $this->salesOrder->load($id);
        $items = [];

        $supplierId = $this->marketplaceHelper->getSupplierId();

        foreach ($order->getAllItems() as $item) {
            $product = $this->product->create([])->load($item->getProductId());

            if ($product->getCreatorId() === $supplierId) {
                $items[] = $item;
            }
        }

        return $items;
    }

    public function getLoadByCountry($code)
    {
        return $this->country->load($code)->getName();
    }

    public function getCurrencyHelper()
    {
        return $this->currencyHelper;
    }

    public function getFormatedShippingAddress()
    {
        return $this->renderer->format(
            $this->getOrder()->getShippingAddress(),
            'html'
        );
    }

    public function getFormatedBillingAddress()
    {
        return $this->renderer->format(
            $this->getOrder()->getBillingAddress(),
            'html'
        );
    }
}
