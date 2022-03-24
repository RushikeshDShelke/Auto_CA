<?php

namespace Indiaideas\Billdesk\Controller\Standard;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Redirect extends \Indiaideas\Billdesk\Controller\Billdesk implements CsrfAwareActionInterface
{
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $order = $this->getOrder();
        if ($order->getBillingAddress())
        {
            $order->setState("pending_payment")->setStatus("pending_payment");
            $order->addStatusToHistory($order->getStatus(), "Customer was redirected to billdesk.");
            $order->save();
            $this->getResponse()->setRedirect(
                $this->getBilldeskModel()->buildBilldeskRequest($order)
            );
        }
        else
        {
            $this->_cancelPayment();
            $this->_billdeskSession->restoreQuote();
            $this->getResponse()->setRedirect(
                $this->getBilldeskHelper()->getUrl('checkout')
            );
        }
    }
}