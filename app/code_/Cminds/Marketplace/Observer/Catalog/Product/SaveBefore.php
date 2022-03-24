<?php

namespace Cminds\Marketplace\Observer\Catalog\Product;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class SaveBefore implements ObserverInterface
{
    const IS_PRODUCT_NEW = 'cminds_marketplace_is_product_new';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * SaveBefore constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @todo refactor the method. Avoid using of Registry object.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->registry->registry(self::IS_PRODUCT_NEW)) {
            $this->registry->unregister(self::IS_PRODUCT_NEW);
        }
        
        $product = $observer->getProduct();
        $isProductNew = $product->isObjectNew();

        if ($isProductNew === true) {
            $this->registry->register(self::IS_PRODUCT_NEW, true);
        }
    }
}
