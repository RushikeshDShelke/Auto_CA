<?php

namespace Peakhour\Cdn\Controller\Adminhtml\Cdn;

use Peakhour\Cdn\Model\Config;
use Peakhour\Cdn\Model\Api;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class FlushAll extends Action
{
    /**
     * @var \Peakhour\Cdn\Model\Api
     */
    private $api;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Context $context,
        Api $api,
        Config $config
    ) {
        $this->api = $api;
        $this->config = $config;

        parent::__construct($context);

    }

    /**
     * Checking service details
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        if ($this->config->getType() == Config::PEAKHOUR && $this->config->isEnabled()) {

            $response = $this->api->purgeAll(API::FLUSH_ALL);

            if ($response['success']) {
                $this->messageManager->addSuccessMessage(
                    "You have successfully cleared the peakhour CDN"
                );
            }
        }

        return $this->_redirect('*/cache/index');
    }
}