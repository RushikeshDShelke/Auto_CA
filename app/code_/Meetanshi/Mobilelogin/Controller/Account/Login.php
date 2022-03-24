<?php

namespace Meetanshi\Mobilelogin\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Meetanshi\Mobilelogin\Helper\Data;

/**
 * Class Login
 * @package Meetanshi\Mobilelogin\Controller\Account
 */
class Login extends Action
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * Login constructor.
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
        return $this->helper->loginPost($this->getRequest()->getParams());
    }
}
