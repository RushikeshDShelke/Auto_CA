<?php
/*

*/

namespace Dotzotfront\Base\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Base observer
 */
class PredispathAdminActionControllerObserver implements ObserverInterface
{
    /**
     * @var \Dotzotfront\Base\Model\AdminNotificationFeedFactory
     */
    protected $_feedFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;
    /**
     * @param \Dotzotfront\Base\Model\AdminNotificationFeedFactory $feedFactory
     * @param \Magento\Backend\Model\Auth\Session                 $backendAuthSession
     */

    public function __construct(
        \Dotzotfront\Base\Model\AdminNotificationFeedFactory $feedFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession
    ) {
        $this->_feedFactory = $feedFactory;
        $this->_backendAuthSession = $backendAuthSession;
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
        if ($this->_backendAuthSession->isLoggedIn()) {
            $feedModel = $this->_feedFactory->create();
            /* @var $feedModel \Dotzotfront\Base\Model\AdminNotificationFeed */
            $feedModel->checkUpdate();
        }
    }
}