<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Settings;

use Cminds\Supplierfrontendproductuploader\Model\Config as ModuleConfig;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Cminds Supplierfrontendproductuploader notifications settings block.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Notifications extends Template
{
    /**
     * Customer session object.
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Customer factory object.
     *
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Module config object.
     *
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * Object constructor.
     *
     * @param Context         $context         Context object.
     * @param CustomerSession $customerSession Customer session object.
     * @param CustomerFactory $customerFactory Customer factory object.
     * @param ModuleConfig    $moduleConfig    Module config object.
     * @param array           $data            Data array.
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        ModuleConfig $moduleConfig,
        array $data
    ) {
        parent::__construct($context, $data);

        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Retrieve currently logged in customer object.
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * Return bool value if supplier can configure ordered products
     * notification.
     *
     * @return bool
     */
    public function canConfigureOrderedProductsNotification()
    {
        return $this->moduleConfig
            ->isSupplierOrderedProductsNotificationConfigurationEnabled();
    }
}
