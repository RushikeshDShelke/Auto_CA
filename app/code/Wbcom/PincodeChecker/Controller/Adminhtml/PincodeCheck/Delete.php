<?php

namespace Wbcom\PincodeChecker\Controller\Adminhtml\PincodeCheck;

use Wbcom\PincodeChecker\Model\PincodeCheckFactory;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var bool
     */
    protected $resultPageFactory = false;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Delete constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param PincodeCheckFactory $pincodeCheckFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PincodeCheckFactory $pincodeCheckFactory
    )
    {
        parent::__construct($context);
        $this->_resultFactory = $context->getResultFactory();
        $this->messageManager = $messageManager;
        $this->pincodeCheckFactory = $pincodeCheckFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->pincodeCheckFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__("Pincode availability check data delete successfully."));
            } catch (\Exception $e) {
                $this->messageManager->addError('Something went wrong '.$e->getMessage());
            }
        }else{
            $this->messageManager->addError('Pincode availability check data not found, please try once more.');
        }
        $resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('wbcompin/pincodecheck/check');
        return $resultRedirect;
    }
}
