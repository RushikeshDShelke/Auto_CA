<?php


namespace Olegnax\ProductSlider\Block;


use Magento\Catalog\Model\ResourceModel\Product\Collection;

class ProductsByIds extends AbstractShortcodeByIds
{
    public function addAttributeToSort(
        Collection $collection,
        $order_attribute = "",
        $order_dir = Collection::SORT_ORDER_ASC
    ) {
        $sortOrder = $this->getSortOrder();
        if (empty($sortOrder)) {
            $collection->getSelect()->order(
                'FIELD(e.entity_id, ' . implode(
                    ",",
                    array_filter($this->getLoadedProductIds())
                ) . ')'
            );
        } else {
            parent::addAttributeToSort($collection, $order_attribute, $order_dir);
        }
    }
}