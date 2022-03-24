<?php
namespace Indiaideas\Billdesk\Controller\Standard;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Cancel extends \Indiaideas\Billdesk\Controller\Billdesk implements CsrfAwareActionInterface
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
        $this->_cancelPayment();
        $this->_checkoutSession->restoreQuote();
        $this->getResponse()->setRedirect(
            $this->getBilldeskHelper()->getUrl('checkout')
        );
    }

}
