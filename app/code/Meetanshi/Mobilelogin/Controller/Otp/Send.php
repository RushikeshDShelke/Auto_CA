<?php

namespace Meetanshi\Mobilelogin\Controller\Otp;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Meetanshi\Mobilelogin\Helper\Data;

/**
 * Class Send
 * @package Meetanshi\Mobilelogin\Controller\Otp
 */
class Send extends Action
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * Send constructor.
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(Context $context, Data $helper
    )
    {
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
        return $this->helper->otpSave($this->getRequest()->getParams());
    }
}
