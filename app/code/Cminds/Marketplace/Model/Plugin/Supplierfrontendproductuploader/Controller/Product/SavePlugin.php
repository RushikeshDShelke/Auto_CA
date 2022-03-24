<?php

namespace Cminds\Marketplace\Model\Plugin\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\Product\Save;
use Magento\Framework\Registry;

class SavePlugin
{
    const SUPPLIER_SAVING_PRODUCT = 'cminds_marketplace_supplier_saving_product';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * SavePlugin constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Save $subject
     */
    public function beforeExecute(Save $subject)
    {
        $this->registry->register(self::SUPPLIER_SAVING_PRODUCT, true);
    }
}
