<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Form;

use Magento\Customer\Block\Form\Login as CustomerLoginForm;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\View\Element\Template\Context;

class Login extends CustomerLoginForm
{
    /**
     * @var int
     */
    private $_username = -1;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Url
     */
    protected $_customerUrl;

    protected $_urlBuilder;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Url     $customerUrl
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Url $customerUrl,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerUrl,
            $data
        );

        $this->_isScopePrivate = false;
        $this->_customerUrl = $customerUrl;
        $this->_customerSession = $customerSession;
        $this->_urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Supplier Login'));
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->_urlBuilder->getUrl('supplier/account/loginpost');
    }
}
