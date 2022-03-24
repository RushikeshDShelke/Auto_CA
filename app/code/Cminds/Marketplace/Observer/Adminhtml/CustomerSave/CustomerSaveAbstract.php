<?php

namespace Cminds\Marketplace\Observer\Adminhtml\CustomerSave;

use Cminds\Marketplace\Helper\Data as DataHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Context;

abstract class CustomerSaveAbstract implements ObserverInterface
{
    protected $request;
    protected $helper;
    protected $objectManager;

    public function __construct(
        DataHelper $helper,
        Context $context
    ) {
        $this->request = $context->getRequest();
        $this->helper = $helper;
        $this->objectManager = $context->getObjectManager();
    }

    public function execute(Observer $observer)
    {

    }
}
