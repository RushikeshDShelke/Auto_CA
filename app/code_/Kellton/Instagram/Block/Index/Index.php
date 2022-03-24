<?php

namespace Kellton\Instagram\Block\Index;

    use \Magento\Framework\View\Element\Template\Context;
    use \Magento\Store\Model\StoreManagerInterface;
    use \Kellton\Instagram\Helper\Data;



class Index extends \Magento\Framework\View\Element\Template {

	 public function __construct(Context $context, StoreManagerInterface $storeManager, Data $helperData)
        {        
            $this->_storeManager = $storeManager;
            $this->_helperData = $helperData;
            parent::__construct($context);
        }
   
    
  public function isEnabled($code)
       {

        return $this->_helperData->isEnabled($code);
        }

  public function getFeeds()
       {

        return $this->_helperData->getFeeds();
       }
    
}