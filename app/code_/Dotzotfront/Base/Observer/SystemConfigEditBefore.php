<?php
/*

*/

namespace Dotzotfront\Base\Observer;

/**
 * Base observer
 */
class SystemConfigEditBefore extends AbstractSystemConfig
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
        if (!$product->getSession()) {
            if ($s = $product->loadSession()) {
                $this->resourceModelConfig->saveConfig($product->getSessionKey(), $s, 'default', 0);

                // clear the config cache
                $this->cacheTypeList->cleanType('config');
                $this->eventManager->dispatch('adminhtml_cache_refresh_type', ['type' => 'config']);
            }
        } else {
            $product = $this->baseProductFactory->create()->load($product->getName());
            if (!$product->isInStock() || !$product->isCached()) {
                $product->checkStatus();
            }
        }
        if (!$product->isInStock()) {
            $product->disable();
        }
        if (!$product->isInStock()) {
            $this->messageManager->addError($product->getDescription());
        }
    }
}
