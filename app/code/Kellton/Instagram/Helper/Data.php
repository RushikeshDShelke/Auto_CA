<?php

namespace Kellton\Instagram\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $storeManager;
    protected $objectManager;

    const XML_PATH_MODULE_GENERAL = 'instagram/general/';

    public function __construct(Context $context, ObjectManagerInterface $objectManager, StoreManagerInterface $storeManager)
    {
        $this->objectManager = $objectManager;
        $this->storeManager  = $storeManager;
        parent::__construct($context);
    }

    public function getConfigValue($field, $storeId = null)
    {

        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }


    public function isEnabled($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_MODULE_GENERAL  . $code, $storeId);
    }

     public function getUserId($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_MODULE_GENERAL  . $code, $storeId);
    }

    public function getToken($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_MODULE_GENERAL  . $code, $storeId);
    }

   public function getCount($code, $storeId = null)
    {

        return $this->getConfigValue(self::XML_PATH_MODULE_GENERAL  . $code, $storeId);
    }


     public function getFeeds($storeId = null)
	 {			
        $data = array();
		if($this->isEnabled('enable')){
		    $userid = $this->getUserId('user_id');
			
			$token = $this->getUserId('token');
		 	$count = $this->getUserId('items');
			
			
		 	 $url = "https://api.instagram.com/v1/users/".$userid."/media/recent?access_token=".$token."&count=".$count;
		 	//exit;
			
			$ch = curl_init($url); 

			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
			$json = curl_exec($ch); 		
			
			curl_close($ch);
			$result = json_decode($json);
			if($result->meta->code==400)
			{

				return $result->meta->error_message; exit;
			}else{

			$width=50;
			$width_height='150x150'; 
			
			foreach ($result->data as $post) {
				
				$data['images'][] = array(
						'title' => ($post->caption)? (($post->caption->text) ? $post->caption->text :'') : '',
						'link'  => $post->link,
						'image' => $post->images->standard_resolution->url,
						'tags' => ($post->tags)? $post->tags : '',
						'likes' => ($post->likes)? $post->likes->count : '',
					);
				}		
			
			
		 	return $data;

			}
			
		 }
		
	}

}