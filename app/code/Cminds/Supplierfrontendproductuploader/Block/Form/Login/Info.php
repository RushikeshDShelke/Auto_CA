<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Form\Login;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Url;
use Magento\Checkout\Helper\Data as CheckoutDataHelper;
use Magento\Framework\Url\Helper\Data as UrlDataHelper;

/**
 * Customer login info block
 */
class Info extends \Magento\Customer\Block\Form\Login\Info
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * Core url
     *
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $coreUrl;

    /**
     * Registration
     *
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    protected $_urlBuilder;

    /**
     * Object constructor.
     *
     * @param Context            $context
     * @param Registration       $registration
     * @param Url                $customerUrl
     * @param CheckoutDataHelper $checkoutData
     * @param UrlDataHelper      $coreUrl
     * @param array              $data
     */
    public function __construct(
        Context $context,
        Registration $registration,
        Url $customerUrl,
        CheckoutDataHelper $checkoutData,
        UrlDataHelper $coreUrl,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registration,
            $customerUrl,
            $checkoutData,
            $coreUrl,
            $data
        );

        $this->registration = $registration;
        $this->_customerUrl = $customerUrl;
        $this->checkoutData = $checkoutData;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->coreUrl = $coreUrl;
    }

    /**
     * Retrieve create new account url
     *
     * @return string
     */
    public function getCreateAccountUrl()
    {
        return $this->_urlBuilder->getUrl('supplier/account/create');
    }
}
