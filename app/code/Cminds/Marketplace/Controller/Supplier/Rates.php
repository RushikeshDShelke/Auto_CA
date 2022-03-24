<?php

namespace Cminds\Marketplace\Controller\Supplier;

use Cminds\Marketplace\Model\Rating;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;

class Rates extends \Magento\Framework\App\Action\Action
{
    protected $_registry;
    protected $_customerSession;
    protected $_storeManagerInterface;
    protected $_customer;
    protected $_transaction;
    protected $_rating;

    public function __construct(
        Context $context,
        Data $helper,
        Registry $registry,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        Customer $customer,
        Transaction $transaction,
        Rating $rating
    ) {
        parent::__construct($context);

        $this->_registry = $registry;
        $this->_customerSession = $customerSession;
        $this->_storeManagerInterface = $storeManager;
        $this->_customer = $customer;
        $this->_transaction = $transaction;
        $this->_rating = $rating;
    }

    public function execute()
    {
        $request = $this->_request;

        if ($request->getParams()) {
            $transaction = $this->_transaction;
            $postData = $request->getParams();

            try {
                $customerId = $this->_customerSession->getId();
                foreach ($postData['ratings'] as $rate_id => $rate) {
                    $rating = $this->_rating->load($rate_id);

                    if ($rating->getCustomerId() == $customerId) {
                        $rating->setRate($rate);
                    } else {
                        throw new LocalizedException(
                            __('You can not change rating which does not belongs to you')
                        );
                    }

                    $transaction->addObject($rating);
                }
                $transaction->save();
                $this->messageManager->addSuccess(__("Rating has been changed"));
            } catch (LocalizedException $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
