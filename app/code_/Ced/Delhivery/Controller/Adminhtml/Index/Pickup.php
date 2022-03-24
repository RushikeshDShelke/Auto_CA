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
namespace Ced\Delhivery\Controller\Adminhtml\Index;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class Pickup extends \Magento\Backend\App\Action
{
    /**
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_scopeConfig;

    /**
     *
     * @param \Magento\Framework\App\Action\Context $context            
     * @param
     *          \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct ( $context );
        $this->resultPageFactory = $resultPageFactory;
    }
 
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        
        $url = "https://track.delhivery.com/fm/request/new/";
        $shipment = array();
        $shipment['pickup_time'] = $this->getRequest()->getPost('pickup_time').':00';
        $shipment['pickup_date'] = str_replace('-','',$this->getRequest()->getPost('pickup_date'));
        $shipment['pickup_location'] = $this->getRequest()->getPost('location');
        $shipment['expected_package_count'] = $this->getRequest()->getPost('package_count');
        $shipment = json_encode($shipment);
	$result = $this->_objectManager->get('Ced\Delhivery\Helper\Data')->PickupRequest($url,'POST',$shipment);
//	print_r($result['error']);die;
        
        if(!$result['error'] && $result['pickup_id']){
            if(isset($result['pr_exist']) && $result['pr_exist']==1){
                $this->messageManager->addErrorMessage(__($result['data']['message']));
            }else{
                $this->messageManager->addSuccessMessage(__('Pickup Request is Submitted Successfully With Pickup Id - '.$result['pickup_id']));
            }
        }else{
            $this->messageManager->addErrorMessage(__('Pickup Is Not Submitted. Please fill correct credentials at Stores>Configuration>Sales>Shipping Methods>Delhivery'));
        }
        $this->_redirect('delhivery/grid/awb');
    }    

}
