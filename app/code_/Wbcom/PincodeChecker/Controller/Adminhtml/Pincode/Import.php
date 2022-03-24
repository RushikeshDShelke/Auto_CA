<?php

namespace Wbcom\PincodeChecker\Controller\Adminhtml\Pincode;

class Import extends \Magento\Backend\App\Action
{
    /**
     * @var bool|\Magento\Framework\View\Result\PageFactory
     */
	protected $resultPageFactory = false;

    /**
     * Import constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Wbcom\PincodeChecker\Model\PincodeFactory $pincodeFactory
     * @param \Magento\Framework\File\Csv $csv
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
	public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Wbcom\PincodeChecker\Model\PincodeFactory $pincodeFactory,
        \Magento\Framework\File\Csv $csv,
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	)
	{
		parent::__construct($context);
        $this->csv = $csv;
        $this->request = $request;
        $this->pincodeFactory = $pincodeFactory;
		$this->resultPageFactory = $resultPageFactory;
	}

    public function execute()
    {	
        $postParams = $this->getRequest()->getParams();
        if ((!empty($postParams)) && (isset($postParams['form_key']))) {
            $files = $this->getRequest()->getFiles();
            $array = (array) $files;
            if ((empty($files)) && (!isset($files['pincode_file']))) {
                $this->_messageManager->addErrorMessage('Invalid file type.');
            }else{
                $response = [];
                $csvData = $this->csv->getData($files['pincode_file']['tmp_name']);
                foreach ($csvData as $row => $data) {
                    if (($row > 0) && (isset($data[1]))){
                        $response[$row][$row] = $this->uploadPincode($data);
                    }
                }
                $this->request->setPostValue('response',$response);
            }
        }
        $this->_view->loadLayout();
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Import Pincode'));
        $resultPage->addBreadcrumb(__('Import Pincode'), __('Import Pincode'));
        return $resultPage;
    }

    /**
     * @param $data
     * @return string
     * Uploading pincode to database
     */
    public function uploadPincode($data){
        $response = '';
        $pinCodeExist = [];
        if ((is_numeric($data[0])) && (is_numeric($data[1]))) {
            $model = $this->pincodeFactory->create();
            if(isset($data['1'])) {
                $modelData = $model->load($data['1'], 'pincode');
                $pinCodeExist = $modelData->getData();
            }
            if (!empty($pinCodeExist)) {
                $model = $modelData;
            }
            $model->setPincode($data['1']);
            $model->setCod($data['2']);
            $model->setDeliveryDays($data['3']);
            $model->setStatus($data['5']);
            $model->setCountryCode($data['4']);
            try{
                $model->save();
                if (!empty($pinCodeExist)) {
                    $response = 'This row updated succesfully';
                }else{
                    $response = 'This row added succesfully';
                }
            }catch(\Exception $e){
                $response = 'This row contain invalid data to add and update';
            }
        }else{
            $response = 'This row contain invalid data';
            return $response;
        }
        return $response;
    }
}
