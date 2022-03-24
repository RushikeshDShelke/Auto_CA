<?php

namespace Cminds\Marketplace\Block\Order\View;

use Cminds\Marketplace\Block\Order\View;
use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Helper\Profits;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\Helper\Data as CurrencyHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\OrderFactory;
use Cminds\Marketplace\Model\Methods as MethodsModel;
use Magento\Framework\Locale\CurrencyInterface;

class Invoices extends View
{
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
        parent::__construct(
            $context,
            $marketplaceHelper,
            $resourceConnection,
            $orderFactory,
            $currencyHelper,
            $profits,
            $registry,
            $productFactory,
            $methodsModel,
            $currencyLocale
        );

        $this->currencyHelper = $currencyHelper;
    }

    /**
     * Get currency helper.
     *
     * @return CurrencyHelper
     */
    public function getCurrencyHelper()
    {
        return $this->currencyHelper;
    }

    /**
     * @return bool
     */
    public function canCreateInvoice()
    {
        $items = $this->getItems();
        foreach ($items as $item) {
            if ($item->getQtyOrdered() - $item->getQtyInvoiced() > 0) {
                return true;
            }
        }

        return false;
    }
}
