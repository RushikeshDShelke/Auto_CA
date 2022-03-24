<?php

namespace Cminds\Supplierfrontendproductuploader\Observer\Product;

use Cminds\Supplierfrontendproductuploader\Model\Config as ModuleConfig;
use Cminds\Supplierfrontendproductuploader\Model\Service\ProductService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Cminds\Supplierfrontendproductuploader\Model\Product as SupplierProduct;

/**
 * Cminds Supplierfrontendproductuploader after product save observer.
 * Will be executed on "product_save_after" event.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class SaveAfter implements ObserverInterface
{
    /**
     * Module config object.
     *
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * Product service object.
     *
     * @var ProductService
     */
    private $productService;

    /**
     * Object constructor.
     *
     * @param ModuleConfig   $moduleConfig   Module config object.
     * @param ProductService $productService Product service object.
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        ProductService $productService
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->productService = $productService;
    }

    /**
     * Reset product approval flag after being edited by supplier.
     *
     * @param Observer $observer Observer object.
     *
     * @return SaveAfter
     */
    public function execute(Observer $observer)
    {
        if ($this->moduleConfig->isEnabled() === false) {
            return $this;
        }
        if ($this->moduleConfig->isProductsAutoApprovalEnabled() === true) {
            return $this;
        }
        if ($this->moduleConfig->isProductsApprovalResetEnabled() === false) {
            return $this;
        }

        $product = $observer->getProduct();

        if ($product->isObjectNew() === true) {
            return $this;
        }
        if ($product->isDataChanged() === false) {
            return $this;
        }

        $approvedFlag = (int)$product->getFrontendproductProductStatus();

        if ($approvedFlag === SupplierProduct::STATUS_APPROVED) {
            $this->productService->disapproveProduct($product->getId());
        }

        $product->setFrontendproductProductStatus(SupplierProduct::STATUS_PENDING);

        return $this;
    }
}
