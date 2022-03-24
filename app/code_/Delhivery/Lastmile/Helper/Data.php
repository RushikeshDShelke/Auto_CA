<?php
namespace Delhivery\Lastmile\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
 
class Data extends AbstractHelper
{
       public function RandomFunc()
       {
               return "This is Helper in Magento 2";
       }
	   public function Executecurl($url, $type, $params){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "$url");
			curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			if($type == 'post'):
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($params));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			endif;	
			$retValue = curl_exec($ch);
			if (curl_error($ch)) {
				//echo $error_msg = curl_error($ch);
			}
			curl_close($ch);
			return $retValue;
		}
		
		public function cancelPackageExecuteUrl($url,$params,$token)
		{
			$curll = curl_init($url);
			$headr = array();
			$headr[] = 'Authorization: Token '.$token.'';
			$headr[] = 'Content-Type: application/json';
			$headr[] = 'Accept: application/json';
			
			curl_setopt($curll, CURLOPT_FAILONERROR, 1);
			curl_setopt($curll, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curll, CURLOPT_TIMEOUT, 60);
			//curl_setopt($curll, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curll, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curll, CURLOPT_POST, true);
			curl_setopt($curll, CURLOPT_POSTFIELDS, json_encode($params));
			curl_setopt($curll, CURLOPT_HTTPHEADER, $headr);
			
			$curl_responsee = curl_exec($curll);
			curl_close($curll);
			
			return $curl_responsee;
		}
		public function saveUpdateCurl($url,$params,$token)
		{
			$datas=$params;
			$curll = curl_init($url);						
			$headr = array();
			$headr[] = 'Authorization: Token '.$token;
			$headr[] = 'Content-Type: application/json';
			$headr[] = 'Accept: application/json';
			curl_setopt($curll, CURLOPT_FAILONERROR, 1);
			curl_setopt($curll, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curll, CURLOPT_TIMEOUT, 60);
			curl_setopt($curll, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curll, CURLOPT_POST, true);
			//curl_setopt($curll, CURLOPT_POSTFIELDS, json_encode($datas));
			curl_setopt($curll, CURLOPT_POSTFIELDS, $datas);
			curl_setopt($curll, CURLOPT_HTTPHEADER, $headr);
			$curl_responsee = curl_exec($curll);
			curl_close($curll);
			return $curl_responsee;
		}
		public function getScopeConfig($configPath)
		 { 
		  return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		 }
		
	   public function getApiUrl($type){
		$modeIs=$this->getScopeConfig('delhivery_lastmile/general/is_production');
		$url='';
		switch ($type) {
			case 'fetchAWB':
				if($modeIs){
					$url='https://track.delhivery.com/waybill/api/bulk/';
				}else{
					$url='http://test.delhivery.com/waybill/api/bulk/';
				}
				break;
			case 'syncAWB':
				if($modeIs){
					$url='https://track.delhivery.com/api/v1/packages/';
				}else{
					$url='http://test.delhivery.com/api/v1/packages/';
				}
				break;
			case 'syncAWB_Preload':
				if($modeIs){
					$url='http://test.delhivery.com/api/cmu/pull';
				}else{
					$url='http://test.delhivery.com/api/cmu/pull';
				}
				break;
			case 'manifestAWB':
				if($modeIs){
					$url='https://track.delhivery.com/';
				}else{
					$url='http://test.delhivery.com/';
				}
				break;
			case 'cancelAWB':
				if($modeIs){
					$url='https://track.delhivery.com/api/p/edit';
				}else{
					$url='http://test.delhivery.com/api/p/edit';
				}
				break;
			case 'editsaveAWB':
				if($modeIs){
					$url='https://track.delhivery.com/api/p/edit';
				}else{
					$url='http://test.delhivery.com/api/p/edit';
				}
				break;
			case 'fetchPIN':
				if($modeIs){
					$url='https://track.delhivery.com/c/api/pin-codes/';
				}else{
					$url='http://test.delhivery.com/c/api/pin-codes/';
				}
				break;
			case 'fetchLOC':
				if($modeIs){
					$url='https://track.delhivery.com/client/warehouses/eslist.json';
				}else{
					$url='https://staging-express.delhivery.com/client/warehouses/eslist.json';
				}
				break;
			case 'createpickupLOC':
				if($modeIs){
					$url='https://track.delhivery.com/fm/request/new/';
				}else{
					$url='http://test.delhivery.com/fm/request/new/';
				}
				break;
				case 'shippinglabelAWB':
				if($modeIs){
					$url='https://track.delhivery.com/api/p/packing_slip';
				}else{
					$url='http://test.delhivery.com/api/p/packing_slip';
				}
				break;
		}
		return $url;
	}
}