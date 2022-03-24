<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\LayeredNavigation\Observer\Frontend\View;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Block\Product\ListProduct\Interceptor;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Element\AbstractBlock;

class BlockAbstractToHtmlAfter implements ObserverInterface
{

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        /** @var AbstractBlock $block */
        $block = $observer->getData('block');
        if (in_array($block->getNameInLayout(), [
            'category.products.list',
            'search_result_list',
        ])) {
            /** @var DataObject $transport */
            $transport = $observer->getData('transport');
            $html = $transport->getData('html');
            $html = '<div class="ox-layerednavigation-product-list-wrapper">' . $html . '</div>';
            $transport->setData('html', $html);
            $observer->setData('transport', $transport);
        }
    }
}