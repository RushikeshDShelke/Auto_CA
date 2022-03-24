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
 
class FetchPincode extends \Magento\Backend\App\Action
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
      $model = $this->_objectManager->create('Ced\Delhivery\Model\Pincode');
      $apiurl =  $this->scope->getValue('carriers/delhivery/pincode_url');
      $token = $this->scope->getValue('carriers/delhivery/license_key');
      if(count($model->getCollection()->getData())){
          $connection = $model->getCollection()->getConnection();
            $tableName = $model->getCollection()->getMainTable();
            $connection->truncateTable($tableName);
      }
        
        if($apiurl && $token)
        {
           
        $path = $apiurl.'json/?token='.$token.'&pre-paid=Y';
        
        $retValue = $this->_objectManager->create('Ced\Delhivery\Helper\Data')->Executecurl($path,'','');

            $codes = json_decode($retValue,true);
	/*if data is not proper then return*/
            if(!isset($codes['delivery_codes']) && !is_array($codes['delivery_codes'])){
                $this->messageManager->addErrorMessage(__('No Data Found, please try again later'));
                return $this->_redirect('delhivery/grid/index');
            }
            foreach ($codes['delivery_codes'] as $item) {
               try {
               $model = $this->_objectManager->create('Ced\Delhivery\Model\Pincode');
              
               if($model->load($item['postal_code']['pin'],'pin')->getData()){
	               	$data = array();
	               	$data['district'] = $item['postal_code']['district'];
	               	$data['pin'] = $item['postal_code']['pin'];
	               	$data['pre_paid'] = $item['postal_code']['pre_paid'];
	               	$data['cash'] = $item['postal_code']['cash'];
	               	$data['pickup'] = $item['postal_code']['pickup'];
	               	$data['cod'] = $item['postal_code']['cod'];
	               	$data['is_oda'] = $item['postal_code']['is_oda'];
	               	$data['state_code'] = $item['postal_code']['state_code'];
	               	$model->setData($data);
	               	$model->save();
               }
               else{
	               $data = array();
	               $data['district'] = $item['postal_code']['district'];
	               $data['pin'] = $item['postal_code']['pin'];
	               $data['pre_paid'] = $item['postal_code']['pre_paid'];
	               $data['cash'] = $item['postal_code']['cash'];
	               $data['pickup'] = $item['postal_code']['pickup'];
	               $data['cod'] = $item['postal_code']['cod'];
	               $data['is_oda'] = $item['postal_code']['is_oda'];
	               $data['state_code'] = $item['postal_code']['state_code'];
	               $model->setData($data);               
	               $model->save();  
               }
               } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e)); 
               }
           

            }

            $this->messageManager->addSuccessMessage(__('successfully uploaded'));
            $this->_redirect('delhivery/grid/index');
            return;
         }else
              {   
                $this->messageManager->addErrorMessage(__('Please Fill The Configuration First'));  
                return $this->_redirect('delhivery/grid/index');;
              }

      
     $this->_redirect('delhivery/grid/index');
         

   }

}
