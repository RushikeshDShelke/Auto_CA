<?php

namespace Meetanshi\Mobilelogin\Plugin\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Meetanshi\Mobilelogin\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Helper\Data as supplierhelper;

/**
 * Class Loginpost
 * @package Meetanshi\Mobilelogin\Plugin\Customer
 */
class Loginpost
{
    /**
     * @var Http
     */
    private $request;
    /**
     * @var Data
     */
    private $mobileHelper;
    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var Validator
     */
    private $formKeyValidator;
    /**
     * @var AccountRedirect
     */
    private $accountRedirect;
    /**
     * @var Redirect
     */
    private $resultRedirect;
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    protected $supplierhelper;

    /**
     * Loginpost constructor.
     * @param Http $request
     * @param Data $helper
     * @param AccountManagementInterface $customerAccountManagement
     * @param Session $customerSession
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     * @param Redirect $redirect
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Http $request,
        Data $helper,
        AccountManagementInterface $customerAccountManagement,
        Session $customerSession,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        Redirect $redirect,
        ManagerInterface $messageManager,
        supplierhelper $supplierhelper
    )
    {

        $this->mobileHelper = $helper;
        $this->request = $request;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->session = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->resultRedirect = $redirect;
        $this->messageManager = $messageManager;
        $this->_supplierhelper = $supplierhelper;
    }

    /**
     * @return Redirect
     */
    public function aroundExecute()
    {
        try {
            $param = $this->request->getParams();
            if ($this->session->isLoggedIn()) {
                if ($this->_supplierhelper->isSupplier($customer->getId())) {
                    $this->resultRedirect->setPath('supplier');
                } else{
                    $this->resultRedirect->setPath('customer/account');
                }
                return $this->resultRedirect;
            } else {
                $login = $param['login'];
                $username = $login['username'];
                
                if (is_numeric($login['username'])) {
                    $username = $this->mobileHelper->getCustomerCollectionMobile($username)->getEmail();
                }
                if (!empty($login['username']) && !empty($login['password'])) {
                    $customer = $this->customerAccountManagement->authenticate($username, $login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $this->session->regenerateId();
                }
                if ($this->_supplierhelper->isSupplier($customer->getId())) {
                    $this->resultRedirect->setPath('supplier');
                } else{
                    $this->resultRedirect->setPath('customer/account');
                }
                return $this->resultRedirect;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e."Invalid Username or Password"));
            $this->resultRedirect->setPath('*/*/');
            return $this->resultRedirect;
        }
    }
}
