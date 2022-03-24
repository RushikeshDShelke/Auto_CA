<?php


namespace Wbcom\PincodeChecker\Plugin\Payment\Method\CashOnDelivery;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Backend\Model\Auth\Session as BackendSession;
use Magento\OfflinePayments\Model\Cashondelivery;

class Available
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;
    /**
     * @var BackendSession
     */
    protected $backendSession;

    protected $helper;

    public function __construct(
        CustomerSession $customerSession,
        BackendSession $backendSession,
        \Magento\Checkout\Model\Cart $cart,
        \Wbcom\PincodeChecker\Model\PincodeFactory $pincodeFactory,
        \Wbcom\PincodeChecker\Helper\Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->backendSession = $backendSession;
        $this->cart = $cart;
        $this->pincodeFactory = $pincodeFactory;
        $this->helper = $helper;
    }

    public function afterIsAvailable(Cashondelivery $subject, $result)
    {
        $postCode = $this->cart->getQuote()->getShippingAddress()->getPostcode();
        if ($this->backendSession->isLoggedIn()) {
            return $result;
        }

        $enable = $this->helper->getModuleStatus();
        if(($enable) && ($enable != 0)){
            if (!empty($postCode)) {
                $model = $this->pincodeFactory->create()
                    ->load($postCode,'pincode');
                $pincodeData = $model->getData();
                if ((empty($pincodeData)) || ($pincodeData['cod'] != 'Delivered')) {
                    return false;
                }
            }
        }
        return $result;
    }
}