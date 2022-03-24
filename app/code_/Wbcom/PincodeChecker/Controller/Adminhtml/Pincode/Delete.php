<?php

namespace Wbcom\PincodeChecker\Controller\Adminhtml\Pincode;

use Wbcom\PincodeChecker\Model\PincodeFactory;
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

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PincodeFactory $pincodeFactory,
        PincodeCheckFactory $pincodeCheckFactory
    )
    {
        parent::__construct($context);
        $this->_resultFactory = $context->getResultFactory();
        $this->pincodeFactory = $pincodeFactory;
        $this->messageManager = $messageManager;
        $this->pincodeCheckFactory = $pincodeCheckFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->pincodeFactory->create();
                $model->load($id);
                $data = $model->getData();
                $model->delete();
                $this->removePincodeCheck($data);
                $this->messageManager->addSuccess(__("Pincode deleted successfully."));
            } catch (\Exception $e) {
                $this->messageManager->addError('Something went wrong '.$e->getMessage());
            }
        }else{
            $this->messageManager->addError('Pincode not found, please try once more.');
        }
        $resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('wbcompin/pincode/index');
        return $resultRedirect;
    }


    public function removePincodeCheck($data){
        $collection = $this->pincodeCheckFactory->create()
            ->getCollection()->addFieldToFilter('pincode', $data['pincode']);
        $pinCheckList = $collection->getData();
        if (!empty($pinCheckList)){
            foreach ($pinCheckList as $list){
                $model = $this->pincodeCheckFactory->create()->load($list['id']);
                $model->delete();
            }
        }
    }
}
