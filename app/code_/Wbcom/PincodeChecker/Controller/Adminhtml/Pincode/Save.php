<?php

namespace Wbcom\PincodeChecker\Controller\Adminhtml\Pincode;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var bool
     */
    protected $resultPageFactory = false;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Wbcom\PincodeChecker\Model\PincodeFactory $pincodeFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Wbcom\PincodeChecker\Model\PincodeFactory $pincodeFactory
    )
    {
        parent::__construct($context);
        $this->_messageManager = $context->getMessageManager();
        $this->_resultFactory = $context->getResultFactory();
        $this->pincodeFactory = $pincodeFactory;
    }
    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $model = $this->pincodeFactory->create();
        if(isset($postData['id'])) {
            $model = $model->load($postData['id']);
        }
        try {
            $model->setPincode($postData['pincode']);
            $model->setDeliveryDays($postData['delivery_days']);
            $model->setStatus($postData['status']);
            $model->setCod($postData['cod']);
            $model->setCountryCode($postData['country_code']);
            try{
                $model->save();
                $this->_messageManager->addSuccessMessage('Pincode added succesfully.');
            }catch(\Exception $e){
                $this->_messageManager->addErrorMessage('Something went wrong while saving pincode');
            }
        } catch (Exception $e) {
            $this->_messageManager->addErrorMessage('Something went wrong '.$e->getMessage());
        }
        $resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('wbcompin/pincode/index');
        return $resultRedirect;
    }
}
