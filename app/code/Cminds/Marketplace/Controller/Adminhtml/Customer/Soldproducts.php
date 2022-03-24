<?php

namespace Cminds\Marketplace\Controller\Adminhtml\Customer;

use Magento\Customer\Controller\Adminhtml\Index;

class Soldproducts extends Index
{
    /**
     * Customer orders grid
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();

        return $resultLayout;
    }
}
