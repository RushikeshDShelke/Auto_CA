<?php

namespace Wbcom\PincodeChecker\Controller\Pincode;

class Check extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Wbcom\PincodeChecker\Helper\Data
     */
    protected $helper;
    /**
     * @var \Wbcom\PincodeChecker\Model\PincodeCheckFactory
     */
    protected $pincodeCheckFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Check constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Wbcom\PincodeChecker\Helper\Data $helper
     * @param \Wbcom\PincodeChecker\Model\PincodeFactory $pincodeFactory
     * @param \Wbcom\PincodeChecker\Model\PincodeCheckFactory $pincodeCheckFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Wbcom\PincodeChecker\Helper\Data $helper,
        \Wbcom\PincodeChecker\Model\PincodeFactory $pincodeFactory,
        \Wbcom\PincodeChecker\Model\PincodeCheckFactory $pincodeCheckFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->_logger = $logger;
        $this->_pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->pincodeFactory = $pincodeFactory;
        $this->helper = $helper;
        $this->pincodeCheckFactory = $pincodeCheckFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $message = $this->helper->getMessages();
        $response = [];
        if ((!empty($postData)) && (array_key_exists('zipcode', $postData))) {
            $model = $this->pincodeFactory->create()
                ->load($postData['zipcode'],'pincode');
            $pincodeData = $model->getData();
            if ((!empty($pincodeData)) && ($pincodeData['status'] == 'Active')) {
                $response['response'] = 'true';
                if ($pincodeData['cod'] != 'Delivered') {
                    $msgWithoutCod = $message['available_not_cod'];
                    $msgWithoutCod = str_replace('%s',$pincodeData['delivery_days'], $msgWithoutCod);
                    $response['message'] = $msgWithoutCod;
                }else{
                    $msgWithCod = $message['available_cod'];
                    $msgWithCod = str_replace('%s',$pincodeData['delivery_days'], $msgWithCod);
                    $response['message'] = $msgWithCod;
                }
                $this->updateExistPincodeCount($postData);
            }else{
                $response['response'] = 'false';
                $response['message'] = $message['not_available'];
                $this->updateNotExistPincodeCount($postData);
            }
        }else{
            $response['response'] = 'false';
            $response['message'] = $message['not_available'];
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

    public function updateExistPincodeCount($postData){
        $modelCheckAvail = $this->pincodeCheckFactory->create();
        $collection = $this->pincodeCheckFactory->create()
            ->getCollection()
            ->addFieldToFilter('pincode',$postData['zipcode'])
            ->addFieldToFilter('sku',$postData['sku']);
        $existData = $collection->getData();
        if(!empty($existData)){
            $id = $existData[0]['id'];
            $model = $modelCheckAvail->load($id);
            $availableCount = $existData[0]['available_count']+1;
            $model->setAvailableCount($availableCount);
            $model->setAvailability(1);
            try{
                $model->save();
            }catch(\Exception $e){
                $this->_logger->error('Pincode Search Error: '.$e->getMessage());
            }
        }else{
            $model = $modelCheckAvail;
            $model->setPincode($postData['zipcode']);
            $model->setSku($postData['sku']);
            $model->setAvailableCount(1);
            $model->setAvailability(1);
            try{
                $model->save();
            }catch(\Exception $e){
                $this->_logger->error('Pincode Search Error: '.$e->getMessage());
            }
        }
    }

    public function updateNotExistPincodeCount($postData){
        $modelCheckAvail = $this->pincodeCheckFactory->create();
        $collection = $this->pincodeCheckFactory->create()
            ->getCollection()
            ->addFieldToFilter('pincode',$postData['zipcode'])
            ->addFieldToFilter('sku',$postData['sku']);
        $existData = $collection->getData();
        if(!empty($existData)){
            $id = $existData[0]['id'];
            $model = $modelCheckAvail->load($id);
            $availableCount = $existData[0]['not_available_count']+1;
            $model->setNotAvailableCount($availableCount);
            $model->setAvailability(0);
            try{
                $model->save();
            }catch(\Exception $e){
                $this->_logger->error('Pincode Search Error: '.$e->getMessage());
            }
        }else{
            $model = $modelCheckAvail;
            $model->setPincode($postData['zipcode']);
            $model->setSku($postData['sku']);
            $model->setNotAvailableCount(1);
            $model->setAvailability(0);
            try{
                $model->save();
            }catch(\Exception $e){
                $this->_logger->error('Pincode Search Error: '.$e->getMessage());
            }
        }
    }
}