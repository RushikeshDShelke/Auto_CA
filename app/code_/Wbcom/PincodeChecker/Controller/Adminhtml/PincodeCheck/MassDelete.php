<?php

namespace Wbcom\PincodeChecker\Controller\Adminhtml\PincodeCheck;

class MassDelete extends \Magento\Backend\App\Action {
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;
    /**
     * @var \Wbcom\PincodeChecker\Model\ResourceModel\PincodeCheck\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * MassDelete constructor.
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Wbcom\PincodeChecker\Model\ResourceModel\PincodeCheck\CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Wbcom\PincodeChecker\Model\ResourceModel\PincodeCheck\CollectionFactory $collectionFactory,
        \Magento\Backend\App\Action\Context $context
        ) {
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute() {
        try{
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $itemsDelete = 0;
            foreach ($collection as $item) {
                $item->delete();
                $itemsDelete++;
            }
            $this->messageManager->addSuccess(__('A total of %1 Pincode(s) were deleted successfully.', $itemsDelete));
        }catch(Exception $e){
            $this->messageManager->addError('Something went wrong while deleting the pincode '.$e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('wbcompin/pincodecheck/check');
    }
    

    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Wbcom_PincodeChecker::view');
    }
}