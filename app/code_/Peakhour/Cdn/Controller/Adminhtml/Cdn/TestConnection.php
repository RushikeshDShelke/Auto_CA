<?php

namespace Peakhour\Cdn\Controller\Adminhtml\Cdn;

use Peakhour\Cdn\Model\Api;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class TestConnection extends Action
{

    /**
     * @var \Peakhour\Cdn\Model\Api
     */
    private $api;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(
        Context $context,
        Api $api,
        JsonFactory $resultJsonFactory
    ) {
        $this->api = $api;
        $this->resultJsonFactory = $resultJsonFactory;


        parent::__construct($context);

    }

    /**
     * Checking service details
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $apiKey = $this->getRequest()->getParam('api_key');
        $domain = $this->getRequest()->getParam('domain');

        $response = $this->api->testConnection($apiKey, $domain);

        $result = $this->resultJsonFactory->create();

        return $result->setData([
            $response
        ]);
    }
}
