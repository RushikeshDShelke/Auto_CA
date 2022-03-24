<?php

namespace Meetanshi\Mobilelogin\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Meetanshi\Mobilelogin\Helper\Data;

/**
 * Class Create
 * @package Meetanshi\Mobilelogin\Controller\Account
 */
class Create extends Action
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * Create constructor.
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
        return $this->helper->createPost($this->getRequest()->getParams());
    }
}
