<?php

namespace Indiaideas\Billdesk\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\UrlInterface as UrlInterface;

class BilldeskConfigProvider implements ConfigProviderInterface
{
    protected $methodCode = "billdesk";

    protected $method;
    
    protected $urlBuilder;

    public function __construct(PaymentHelper $paymentHelper, UrlInterface $urlBuilder) {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->urlBuilder = $urlBuilder;
    }

    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'billdesk' => [
                    'redirectUrl' => $this->urlBuilder->getUrl('billdesk/Standard/Redirect', ['_secure' => true]),
                    'ajaxController'=>$this->urlBuilder->getUrl('billdesk/Standard/AjaxController'),
                    'transactionUrl'=>$this->method->getConfigData("transaction_url"),
                    'secret_key'=>$this->method->getConfigData("secret_key"),
                ]
            ]
        ] : [];
    }

    protected function getRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('indiaideas/billdesk/');
    }
    
    protected function getFormData()
    {
        return $this->method->getRedirectUrl();
    }
}
