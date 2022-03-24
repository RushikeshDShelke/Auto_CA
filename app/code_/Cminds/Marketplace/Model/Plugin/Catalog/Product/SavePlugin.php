<?php

namespace Cminds\Marketplace\Model\Plugin\Catalog\Product;

use Cminds\Marketplace\Model\Plugin\Supplierfrontendproductuploader\Controller\Product\SavePlugin as ProductSavePlugin;
use Cminds\Marketplace\Observer\Catalog\Product\SaveBefore;
use Cminds\Supplierfrontendproductuploader\Model\Config as SupplierModuleConfig;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Registry;

class SavePlugin
{
    const MARKETPLACE_PRODUCT_NEEDS_APPROVAL = 'cminds_marketplace_product_needs_approval';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var SupplierModuleConfig
     */
    private $supplierModuleConfig;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * SavePlugin constructor.
     *
     * @param Registry             $registry
     * @param SupplierModuleConfig $supplierModuleConfig
     * @param EventManager         $eventManager
     */
    public function __construct(
        Registry $registry,
        SupplierModuleConfig $supplierModuleConfig,
        EventManager $eventManager
    ) {
        $this->supplierModuleConfig = $supplierModuleConfig;
        $this->registry = $registry;
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subject
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function afterAfterSave(\Magento\Catalog\Model\Product $subject)
    {
        if ($this->registry->registry('dispatched') === true) {
            return $subject;
        }

        $isSupplierSavingProduct = $this->registry->registry(ProductSavePlugin::SUPPLIER_SAVING_PRODUCT);
        $isAutoApprovalEnabled = $this->supplierModuleConfig->isProductsAutoApprovalEnabled();
        $isProductApprovalResetEnabled = $this->supplierModuleConfig->isProductsApprovalResetEnabled();
        $isObjectNew = $this->registry->registry(SaveBefore::IS_PRODUCT_NEW);

        if ($isAutoApprovalEnabled === true || $isSupplierSavingProduct === false) {
            return $subject;
        }

        if ($isObjectNew === true) {
            $this->eventManager->dispatch(
                self::MARKETPLACE_PRODUCT_NEEDS_APPROVAL,
                [
                    'product_id' => $subject->getId()
                ]
            );
        } elseif ($isProductApprovalResetEnabled === true) {
            $this->eventManager->dispatch(
                self::MARKETPLACE_PRODUCT_NEEDS_APPROVAL,
                [
                    'product_id' => $subject->getId()
                ]
            );
        }

        $this->registry->register('dispatched', true);
    }
}
