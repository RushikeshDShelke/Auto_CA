<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Catalog\Products\Grid\Renderer;

use Cminds\Supplierfrontendproductuploader\Model\Product as SupplierProduct;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

class Approve extends AbstractRenderer
{
    /**
     * Product repository object.
     *
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Object constructor.
     *
     * @param Context                    $context           Context object.
     * @param ProductRepositoryInterface $productRepository Product repository object.
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);

        $this->productRepository = $productRepository;
    }

    /**
     * Render approve column.
     *
     * @param DataObject $row Row object.
     *
     * @return string
     */
    protected function _getValue(DataObject $row) // @codingStandardsIgnoreLine
    {
        $product = $this->productRepository->getById($row->getId());
        $status = (int)$product->getData('frontendproduct_product_status');

        if ($status === SupplierProduct::STATUS_APPROVED) {
            $label = __('Disapprove');
            $action = 'disapprove';
        } else {
            $label = __('Approve');
            $action = 'approve';
        }

        $url = $this->getUrl(
            '*/*/' . $action,
            ['id' => $row->getId()]
        );

        return '<a href="' . $url . '">' . $label . '</a>';
    }
}
