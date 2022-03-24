<?php
namespace Craft\Sorter\Plugin\Catalog\Block;

class Toolbar
{

    /**
    * Plugin
    *
    * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
    * @param \Closure $proceed
    * @param \Magento\Framework\Data\Collection $collection
    * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
    */
    public function aroundSetCollection(
    \Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
    \Closure $proceed,
    $collection
    ) {
    $currentOrder = $subject->getCurrentOrder();
    $result = $proceed($collection);

    if ($currentOrder) {
        if ($currentOrder == 'high_to_low') {
            $subject->getCollection()->setOrder('price', 'desc');
        } elseif ($currentOrder == 'low_to_high') {
            $subject->getCollection()->setOrder('price', 'asc');
        }
    }

    return $result;
    }

}