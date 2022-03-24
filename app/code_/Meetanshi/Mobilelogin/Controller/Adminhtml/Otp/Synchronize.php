<?php

namespace Meetanshi\Mobilelogin\Controller\Adminhtml\Otp;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Meetanshi\Mobilelogin\Helper\Data;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Synchronize
 * @package Meetanshi\Mobilelogin\Controller\Adminhtml\Otp
 */
class Synchronize extends Action
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Synchronize constructor.
     * @param Context $context
     * @param Data $helper
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, Data $helper, JsonFactory $resultJsonFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $rtn = $this->helper->synchronize();
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => true, 'responseText' => $rtn]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
