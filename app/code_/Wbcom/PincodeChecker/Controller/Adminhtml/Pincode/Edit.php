<?php

namespace Wbcom\PincodeChecker\Controller\Adminhtml\Pincode;

use Wbcom\PincodeChecker\Model\PincodeFactory;
use Magento\Framework\Registry;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param Registry $registry
     * @param PincodeFactory $pincodeFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Registry $registry,
        pincodeFactory $pincodeFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->pincodeFactory = $pincodeFactory;
        $this->_coreSession = $coreSession;
    }

    public function execute()
    {
        $pincode = $this->getRequest()->getParam('id');
        $model= $this->pincodeFactory->create();
        $model->load($pincode);
        $this->_coreRegistry->register('pincode', $model);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Wbcom_PincodeChecker::edit_pincode');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Pincode'));
        return $resultPage;
    }
}
