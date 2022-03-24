<?php

namespace Kellton\SupportTheArtisan\Controller\Index;

class Artisans extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}