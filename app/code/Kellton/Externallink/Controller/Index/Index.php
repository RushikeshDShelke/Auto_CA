<?php

namespace Kellton\Externallink\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
    	//echo 'external index index 111111111111111'; exit;
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}