<?php

namespace Wbcom\PincodeChecker\Controller\Pincode;

class Validate extends \Magento\Framework\App\Action\Action
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
     * Validate constructor.
     * @param \Wbcom\PincodeChecker\Model\PincodeFactory $pincodeFactory
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Wbcom\PincodeChecker\Helper\Data $helper
     */
    public function __construct(
        \Wbcom\PincodeChecker\Model\PincodeFactory $pincodeFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Wbcom\PincodeChecker\Helper\Data $helper
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->pincodeFactory = $pincodeFactory;
        $this->helper = $helper;
        return parent::__construct($context);
    }

    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $message = $this->helper->getMessages();
        $response = [];
        $enable = $this->helper->getModuleStatus();
        if (($enable) && ($enable != 0)) {
            if ((!empty($postData)) && (array_key_exists('pincode', $postData))) {
                $model = $this->pincodeFactory->create()
                    ->load($postData['pincode'],'pincode');
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
                }else{
                    $response['response'] = 'false';
                    $response['message'] = $message['not_available'];
                }
            }else{
                $response['response'] = 'false';
                $response['message'] = $message['not_available'];
            }
        }else{
            $response['response'] = 'true';
            $response['message'] = '';
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }
}