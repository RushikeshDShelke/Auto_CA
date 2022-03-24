<?php

namespace Cminds\Supplierfrontendproductuploader\Observer;

use Cminds\Supplierfrontendproductuploader\Helper\Data as Helper;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class OnCustomerLogin implements ObserverInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $_yesnoFactory;

    protected $_helper;

    private $_request;

    private $_session;

    protected $messageManager;

    protected $_loger;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
        Session $session,
        Helper $helper,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        LoggerInterface $loger
    ) {
        $this->messageManager = $messageManager;
        $this->scopeConfigInterface = $scopeConfig;
        $this->_request = $request;
        $this->_helper = $helper;
        $this->_session = $session;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->_loger = $loger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();

        /*
         * If logged user isn't supplier go away.
         */
        if (!$this->_helper->isSupplier($customer->getId())) {
            return;
        }

        /*
         *
         *
         * If supplier is'nt approved logout him.
         */
        if ($this->_helper->isSupplierNeedsToBeApproved()) {
            $approved = (bool)$customer->getSupplierApprove();

            if (!$approved) {
                $this->_session->setId(null)
                    ->setCustomerGroupId(0);

                $this->messageManager->addError(
                    __('Your account isn\'t approved yet.')
                );

                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath($this->_helper->getSupplierLoginPage());

                return $resultRedirect;
            }
        }
    }
}
