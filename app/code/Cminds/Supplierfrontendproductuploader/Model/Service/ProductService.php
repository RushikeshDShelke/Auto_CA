<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Service;

use Cminds\Supplierfrontendproductuploader\Model\Product as SupplierProduct;
use Cminds\Supplierfrontendproductuploader\Model\Product\Builder as ProductBuilder;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DB\TransactionFactory;
use Magento\Catalog\Model\Product\Attribute\Management as AttributeManagement;

/**
 * Cminds Supplierfrontendproductuploader product service model.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
final class ProductService
{
    /**
     * Transaction factory object.
     *
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * Product factory object.
     *
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var AttributeManagement
     */
    private $attributeManagement;

    /**
     * @var ProductBuilder
     */
    private $productBuilder;

    /**
     * @var Type\Configurable
     */
    private $typeConfigurable;

    /**
     * Object constructor.
     *
     * @param TransactionFactory    $transactionFactory Transaction factory object.
     * @param ProductFactory        $productFactory     Product factory object.
     * @param AttributeManagement   $attributeManagement
     * @param ProductBuilder        $productBuilder
     * @param Configurable $typeConfigurable 
     */
    public function __construct(
        TransactionFactory $transactionFactory,        
        ProductFactory $productFactory,
        AttributeManagement $attributeManagement,
        ProductBuilder $productBuilder,
        Configurable $typeConfigurable
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->productFactory = $productFactory;
        $this->attributeManagement = $attributeManagement;
        $this->productBuilder = $productBuilder;
        $this->typeConfigurable = $typeConfigurable;
    }

    /**
     * Approve product with provided id.
     *
     * @param int $productId Product id.
     *
     * @return ProductService
     */
    public function approveProduct($productId)
    {
        $productData =  [
                            'stock_data' => [
                                'is_in_stock' => 1,
                            ],
                            'supplier_actived_product' => 1,
                            'frontendproduct_product_status' => SupplierProduct::STATUS_APPROVED,
                        ];
        
        $parentProductIds = $this->typeConfigurable->getParentIdsByChild($productId);
        if (count($parentProductIds) <= 0) {
            $productData['visibility'] = ProductVisibility::VISIBILITY_BOTH;
        }
        
        $this->updateProduct(
            $productId,
            $productData
        );

        // TODO: Dispatch event.

        return $this;
    }

    /**
     * Disapprove product with provided id.
     *
     * @param int $productId Product id.
     *
     * @return ProductService
     */
    public function disapproveProduct($productId)
    {
        $productData =  [
                            'stock_data' => [
                                'is_in_stock' => 1,
                            ],
                            'supplier_actived_product' => 1,
                            'frontendproduct_product_status' => SupplierProduct::STATUS_DISAPPROVED,
                        ];
        
        $parentProductIds = $this->typeConfigurable->getParentIdsByChild($productId);
        if (count($parentProductIds) <= 0) {
            $productData['visibility'] = ProductVisibility::VISIBILITY_NOT_VISIBLE;
        }
        
        $this->updateProduct(
            $productId,
            $productData
        );

        // TODO: Dispatch event.

        return $this;
    }

    /**
     * Update product data on all scopes.
     *
     * @param int   $productId Product id.
     * @param array $data      Product data array.
     *
     * @return ProductService
     * @throws \Exception
     */
    private function updateProduct($productId, array $data)
    {
        $transaction = $this->transactionFactory->create();

        $storeProduct = $this->productFactory
            ->create()
            ->setStoreId(0)
            ->load($productId);

        foreach ($data as $key => $value) {
            $storeProduct->setData($key, $value);
        }

        if ($storeProduct->getTypeId() === Configurable::TYPE_CODE) {
            $storeProduct = $this->setAttributes($storeProduct);
        }

        $transaction
                ->addObject($storeProduct)
                ->save();        

        return $this;
    }

    /**
     * Set configurable product attributes before save.
     *
     * @param $product
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function setAttributes($product)
    {
        $assignedAttributes = $product->getTypeInstance()->getConfigurableOptions($product);
        $configAttributeSet = $product->getAttributeSetId();
        $attributes = $this->attributeManagement->getAttributes($configAttributeSet);
        $attributesToSave = [];

        foreach ($assignedAttributes as $attributeId => $value) {
            $attributesToSave[] = $attributes[$attributeId]->getAttributeCode();
        }

        $product = $this->productBuilder
            ->fillProductWithConfigurableAttributes($product, $attributesToSave);

        return $product;
    }
}
