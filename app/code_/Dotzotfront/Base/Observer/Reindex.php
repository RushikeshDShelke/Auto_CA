<?php
/*


*/

namespace Dotzotfront\Base\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Base observer
 */
class Reindex implements ObserverInterface
{
    /**
     * @var \Dotzotfront\Base\Model\AdminNotificationFeedFactory
     */
    protected $product;

    /**
     * @param \Dotzotfront\Base\Model\AdminNotificationFeedFactory $feedFactory
     * @param \Magento\Backend\Model\Auth\Session                 $backendAuthSession
     */
    public function __construct(
        \Dotzotfront\Base\Model\Product $product
    ) {
        $this->product = $product;
    }

    /**
     * Predispath admin action controller
     *
     * @param                                         \Magento\Framework\Event\Observer $observer
     * @return                                        void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->product->reindex();
    }
}
