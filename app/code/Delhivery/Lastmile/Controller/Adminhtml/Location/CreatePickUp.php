<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Controller\Adminhtml\Location;

class CreatePickUp extends \Magento\Backend\App\Action
{
    /**
     * Redirect result factory
     * 
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;
	protected $helper;
    /**
     * constructor
     * 
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
		\Delhivery\Lastmile\Helper\Data $helper
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
		$this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * forward to edit
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
		//echo "<pre>";
		//print_r($this->getRequest()->getParams());
		try {
		if($this->getRequest()->getParam('location')) {
				$apiCreatepkprurl = $this->helper->getApiUrl('createpickupLOC');
				$token = trim($this->getScopeConfig('delhivery_lastmile/general/license_key'));
				if($apiCreatepkprurl && $token)
				{
					$dateTime=strtotime($this->getRequest()->getParam('pickup_date_time'));
					//echo $dateTime;die;
					$pickupDate=date('Y-m-d',$dateTime);
					//$pickupTime=date('H:i:s',$dateTime);
					$pickupTime=$this->getRequest()->getParam('pickup_hours').':'.$this->getRequest()->getParam('pickup_minute').':00';
					
					$i=0;
					$successWaybills=0;
					$unsuccessWaybil=0;
					$reqIds=array();
					foreach($this->getRequest()->getParam('location') as $location){
						$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
						$loadLocations = $this->_objectManager->create('Delhivery\Lastmile\Model\Location')->load($location);
						
						if($loadLocations->getExpectedPackageCount())
						{
							$pickup_time=$pickupTime;
							$pickup_date=$pickupDate;
							//echo $pickup_date;die;
							$expected_package_count=$loadLocations->getExpectedPackageCount();
							$pickup_location=$loadLocations->getName();
							$postData=array(
											'pickup_time'=>$pickup_time,
											'pickup_date'=>$pickup_date,
											'pickup_location'=>$pickup_location,
											'expected_package_count'=>$expected_package_count
											);
							//print_r($postData);
							//echo "<br>";
							$url = $apiCreatepkprurl;
							$curls = curl_init($url);
					
							$headr = array();
							$headr[] = 'Authorization: Token '.$token;
							$headr[] = 'Accept: application/json';
							
							curl_setopt($curls, CURLOPT_FAILONERROR, 0);
							curl_setopt($curls, CURLOPT_CUSTOMREQUEST, 'POST');
							//curl_setopt($curls, CURLOPT_TIMEOUT, 60);
							//curl_setopt($curls, CURLOPT_SSL_VERIFYPEER, false);
							curl_setopt($curls, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($curls, CURLOPT_POST, true);
							curl_setopt($curls, CURLOPT_POSTFIELDS, http_build_query($postData));
							curl_setopt($curls, CURLOPT_HTTPHEADER, $headr);
							$curl_responsee = curl_exec($curls);
							//echo $curl_responsee;
							//die;
							curl_close($curls);
							$curl_response = json_decode($curl_responsee);
							//echo "<pre>";
							$objArray=get_object_vars($curl_response);
							
							/*if($curl_response->pickup_date === "Pickup date cannot be in past")
							{
								$unsuccessWaybil++;
							}else*/
							if(array_key_exists("pickup_id",$objArray)){
								$reqIds[]=$curl_response->pickup_id;
								$loadLocations->setExpectedPackageCount(0);
								$loadLocations->save();
								$successWaybills++;
							}else{
								$errMsg='';
								if(array_key_exists("pickup_time",$objArray))
								$this->messageManager->addErrorMessage(__($objArray['pickup_time'])); 
								
								if(array_key_exists("pickup_date",$objArray))
								$this->messageManager->addErrorMessage(__($objArray['pickup_date'])); 
								
								if(array_key_exists("pickup_location",$objArray))
								$this->messageManager->addErrorMessage(__($objArray['pickup_location'])); 
	
								$unsuccessWaybil++;
							}
							
						}
						else{
								//$this->messageManager->addErrorMessage(__('Unable to process the pickup request if Expected Package Count 0 . ')); 
								$unsuccessWaybil++;
							}
					$i++;
					}
					if($successWaybills){
							$this->messageManager->addSuccessMessage(__('Order Request Pickup # is : '.implode(',',$reqIds)));
						}
					if($unsuccessWaybil){
						$this->messageManager->addErrorMessage(__($unsuccessWaybil.' Order Pickup Request Creation Failed.')); 
					}
				}else
				{
					$this->messageManager->addErrorMessage(__('Please add valid License Key and Gateway URL in plugin configuration')); 
				}
				
			}
		} catch (Exception $e) {
			$this->messageManager->addErrorMessage(__($e->getMessage())); 
		}
		
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('delhivery_lastmile/location');
		return $resultRedirect;
	}
	public function getScopeConfig($configPath)
	 { 
	  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	 }
}
