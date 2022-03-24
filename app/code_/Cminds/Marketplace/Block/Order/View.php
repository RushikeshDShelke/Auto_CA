<?php

namespace Cminds\Marketplace\Block\Order;

use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Helper\Profits;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Cminds\Marketplace\Model\Methods as MethodsModel;
use Magento\Framework\Locale\CurrencyInterface;

class View extends Template
{
    /**
     * @var MarketplaceHelper
     */
    protected $marketplaceHelper;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var CurrencyHelper
     */
    protected $currencyHelper;

    /**
     * @var Profits
     */
    protected $profitsHelper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var MethodsModel
     */
    protected $methods;
    
    /**
     * @var CurrencyInterface
     */
    protected $currencyLocale;

    public function __construct(
        Context $context,
        MarketplaceHelper $marketplaceHelper,
        ResourceConnection $resourceConnection,
        OrderFactory $orderFactory,
        CurrencyHelper $currencyHelper,
        Profits $profits,
        Registry $registry,
        ProductFactory $productFactory,
        MethodsModel $methodsModel,
        CurrencyInterface $currencyLocale
    ) {
        parent::__construct($context);

        $this->marketplaceHelper = $marketplaceHelper;
        $this->resourceConnection = $resourceConnection;
        $this->orderFactory = $orderFactory;
        $this->currencyHelper = $currencyHelper;
        $this->profitsHelper = $profits;
        $this->registry = $registry;
        $this->productFactory = $productFactory;
        $this->methods = $methodsModel;
        $this->currencyLocale = $currencyLocale;
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
     * Get supplier shipping methods for current order.
     *
     * @return array
     */
    public function getSupplierShippingMethod()
    {
        $id = $this->registry->registry('order_id');
        $supplierId = $this->marketplaceHelper->getSupplierId();

        $order = $this->orderFactory->create()->load($id);
        $currencySymbol = $this->currencyLocale->getCurrency($order->getOrderCurrencyCode())->getSymbol();
        $data = [];

        foreach ($order->getAllItems() as $item) {
            $product = $this->productFactory->create()->load($item->getProductId());

            if ($product->getCreatorId() === $supplierId) {
                $data['price'] = $currencySymbol.$item->getShippingPrice();
                $method = $this->methods->load($item->getShippingMethodId());
                $data['method'] = $method->getName();
                break;
            }
        }        
        
        return $data;
    }

    /**
     * Get items for current order but only for logged supplier.
     *
     * @return array
     */
    public function getItems()
    {
        $id = $this->registry->registry('order_id');
        $order = $this->orderFactory->create()->load($id);
        $items = [];

        $supplierId = $this->marketplaceHelper->getSupplierId();

        foreach ($order->getAllItems() as $item) {
            $product = $this->productFactory->create()->load($item->getProductId());

            if ($product->getCreatorId() === $supplierId) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Return current open tab.
     *
     * @return string
     */
    public function getCurrentTab()
    {
        return $this->getRequest()->getParam('tab', 'products');
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
     * @return false|\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection
     */
    public function getShipments()
    {
        return $this->getOrder()->getShipmentsCollection();
    }

    /**
     * @return bool
     */
    public function canCreateShipment()
    {
        $items = $this->getItems();
        foreach ($items as $item) {
            if ($item->getQtyOrdered() - $item->getQtyShipped() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get additional tabs
     *
     * @param Order $order
     * @return mixed
     */
    public function getAdditionalTabs($order)
    {
        return $this->getChildBlock('view_additional_tabs')->setOrder($order)->toHtml();
    }

    /**
     * Get nav tabs
     *
     * @param Order $order
     * @return mixed
     */
    public function getAdditionalNavTabs($order)
    {
        return $this->getChildBlock('view_additional_nav_tabs')->setOrder($order)->toHtml();
    }
}
