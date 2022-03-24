<?php

namespace Meetanshi\Mobilelogin\Controller\Otp;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Meetanshi\Mobilelogin\Helper\Data;

/**
 * Class Verify
 * @package Meetanshi\Mobilelogin\Controller\Otp
 */
class Verify extends Action
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * Verify constructor.
     * @param Context $context
     * @param Data $helperData
     */
    public function __construct(Context $context, Data $helperData)
    {
        $this->helper = $helperData;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
        return $this->helper->otpVerify($this->getRequest()->getParams());
    }
}
