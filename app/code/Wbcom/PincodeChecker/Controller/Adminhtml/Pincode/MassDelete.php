<?php
namespace Wbcom\PincodeChecker\Controller\Adminhtml\Pincode;
use Wbcom\PincodeChecker\Model\PincodeCheckFactory;

class MassDelete extends \Magento\Backend\App\Action {
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;
    /**
     * @var \Wbcom\PincodeChecker\Model\ResourceModel\Pincode\CollectionFactory
     */
    protected $_collectionFactory;

    public function __construct(
        PincodeCheckFactory $pincodeCheckFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Wbcom\PincodeChecker\Model\ResourceModel\Pincode\CollectionFactory $collectionFactory,
        \Magento\Backend\App\Action\Context $context
        ) {
        $this->pincodeCheckFactory = $pincodeCheckFactory;
        $this->_filter            = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute() {
        try{ 

            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $itemsDelete = 0;
            foreach ($collection as $item) {
                $data = $item->getData();
                $item->delete();
                $itemsDelete++;
                $this->removePincodeCheck($data);
            }
            $this->messageManager->addSuccess(__('A total of %1 Pincode(s) were deleted successfully.', $itemsDelete));
        }catch(Exception $e){
            $this->messageManager->addError('Something went wrong while deleting the pincode '.$e->getMessage());
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('wbcompin/pincode/index');
    }
    

    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Wbcom_PincodeChecker::view');
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