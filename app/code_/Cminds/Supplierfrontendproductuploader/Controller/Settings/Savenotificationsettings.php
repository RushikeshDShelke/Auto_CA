<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Settings;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Cminds Supplierfrontendproductuploader save notifications settings controller.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Savenotificationsettings extends AbstractController
{
    protected $session;
    protected $customerFactory;

    public function __construct(
        Context $context,
        Data $helper,
        CustomerSession $session,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->session = $session;
        $this->customerFactory = $customerFactory;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $postData = $this->getRequest()->getParams();

        try {
            $loggedUser = $this->session;
            $customer = $this->customerFactory->create()
                ->load($loggedUser->getCustomer()->getEntityId());

            $customer->setData('notification_product_ordered', 0);
            if (isset($postData['send_notification_after_product_purchased'])
                && (int)$postData['send_notification_after_product_purchased'] === 1
            ) {
                $customer->setData('notification_product_ordered', 1);
            }

            $customer->setData('notification_product_approved', 0);
            if (isset($postData['send_notification_when_product_approved'])
                && (int)$postData['send_notification_when_product_approved'] === 1
            ) {
                $customer->setData('notification_product_approved', 1);
            }

            $customer->save();
            $this->_redirect('*/*/notifications/');
        } catch (\Exception $e) {
            $this->_redirect('*/*/notifications/');
        }
    }
}
