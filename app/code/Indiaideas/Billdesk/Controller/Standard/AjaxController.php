<?php

namespace Indiaideas\Billdesk\Controller\Standard;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class AjaxController extends \Indiaideas\Billdesk\Controller\Billdesk implements CsrfAwareActionInterface
{

    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $order = $this->getOrder();
        $response = ['msg' => $this->getBilldeskModel()->buildParams($order)];
        return $resultJson->setData($response);
    }
}