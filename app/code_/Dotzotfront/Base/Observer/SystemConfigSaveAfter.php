<?php
/*

*/

namespace Dotzotfront\Base\Observer;

/**
 * Base observer
 */
class SystemConfigSaveAfter extends AbstractSystemConfig
{
    /**
     * Predispath admin action controller
     *
     * @param                                         \Magento\Framework\Event\Observer $observer
     * @return                                        void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $section = $this->_getSection($observer);
        if (!$section) {
            return;
        }

        $product = $this->_getProductBySection($section);

        $product->checkStatus();
        if (!$product->isInStock()) {
            $product->disable();
        }


    }
}