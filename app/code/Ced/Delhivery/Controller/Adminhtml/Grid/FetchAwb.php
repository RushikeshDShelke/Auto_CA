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
namespace Ced\Delhivery\Controller\Adminhtml\Grid;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class FetchAwb extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
 
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scope
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scope=$scope;
    }
 
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
      $apiurl =  $this->scope->getValue('carriers/delhivery/awb_url');
      $token = $this->scope->getValue('carriers/delhivery/license_key');
      $cl=$this->scope->getValue('carriers/delhivery/client_id');
     
        if($apiurl && $token && $cl)
        {
       		$path = $apiurl.'json/?token='.$token.'&count=50&cl='.urlencode($cl);
       		 $retValue = $this->_objectManager->create('Ced\Delhivery\Helper\Data')->Executecurl($path,'','');

             $codes = json_decode($retValue,true);
             $model = $this->_objectManager->create('Ced\Delhivery\Model\Awb');
             
            $awbs = explode(',',$codes);
            /*if data is not proper then return*/
            if (!isset($awbs) && !is_array($awbs)) {
                $this->messageManager->addErrorMessage(__('No Data Found, please try again later'));
                return $this->_redirect('delhivery/grid/awb');
            }
            foreach ($awbs as $awb) {          
                   $data = array();
                   $data['awb'] = $awb;
                   $data['state'] = 2;
                   $model->setData($data);
                   $model->save();  
                }
                $this->messageManager->addSuccessMessage(__('Downloaded Successfully'));
                $this->_redirect('delhivery/grid/awb');
                return;
         }
         else{
         	$this->messageManager->addErrorMessage(__('Please Fill The Configuration First'));
         	$this->_redirect('delhivery/grid/awb');
         	return;
         	
         }
    $this->_redirect('delhivery/grid/awb');
     

   }

}
