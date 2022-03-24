<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ShowProductName extends Action
{
    protected $jsonResultFactory;
    protected $cmindsHelper;
    protected $productCollectionFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        Data $helperCminds,
        CollectionFactory $productCollectionFactory
    ) {
        parent::__construct($context);

        $this->jsonResultFactory = $jsonResultFactory;
        $this->cmindsHelper = $helperCminds;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();

        try {
            $phrase = $this->getRequest()->getParam('phrase');
            $supplier_id = $this->cmindsHelper->getSupplierId();

            $collection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('sku', $phrase)
                ->addAttributeToFilter(
                    [
                        [
                            'attribute' => 'creator_id',
                            'eq' => $supplier_id,
                        ],
                    ]
                );

            $productName = '';
            $productId = '';
            foreach ($collection as $product) {
                $productName = $product->getName();
                $productId = $product->getId();
                $productSku = $product->getSku();
            }

            if ($productId == '') {
                return $result->setData(['error' => 'Product not found']);
            }

            return $result->setData(
                [
                    'product_name' => $productName,
                    'product_id' => $productId,
                    'product_sku' => $productSku,
                ]
            );
        } catch (\Exception $ex) {
            return $result->setData(['error' => $ex->getMessage()]);
        }
    }
}
