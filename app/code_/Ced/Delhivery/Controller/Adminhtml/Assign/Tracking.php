<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Delhivery
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Delhivery\Controller\Adminhtml\Assign;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;

class Tracking extends \Magento\Backend\App\Action
{

    /**
     * Index action
     *
     * @return $this
     */
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ){
        $this->resultJsonFactory = $resultJsonFactory; 
        parent::__construct($context);

    }

    public function execute()
    {

        $resultJson = $this->resultJsonFactory->create();
        $orderId = $this->getRequest()->getParam('order_id');
        $json = array('success'=>0,'error_msg'=>'','awb'=>0);
        $resultJson =$this->_objectManager->get('Magento\Framework\Controller\Result\JsonFactory')->create();
        $order = $this->_objectManager->get("\Magento\Sales\Model\Order")->load($orderId);

        $zipcode = $order->getShippingAddress()->getPostcode();
        $available = $this->_objectManager->create('\Ced\Delhivery\Model\Pincode')->getCollection()->addFieldToFilter('pin',$zipcode);

        if(!count($available)){
            $json['error_msg'] = 'Zipcode not serviceable.';
        }
        $model = $this->_objectManager->create('\Ced\Delhivery\Model\Awb')->getCollection()->addFieldToFilter('state',array('eq'=>2));
        if(count($model->getData())){
            $json['success'] = 1;
           $json['awb'] = $model->getFirstItem()->getAwb();
      
    	}else{
    	    $json['error_msg'] = 'There is no unused AWB left. Download fresh then try.';
    	} 
        return $resultJson->setData(json_encode($json));
       
    }
}
