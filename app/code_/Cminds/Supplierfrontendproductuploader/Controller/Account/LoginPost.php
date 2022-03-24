<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Account;

use Cminds\Supplierfrontendproductuploader\Helper\Data as Helper;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;

class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
{
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Validator */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var
     */
    protected $_helper;

    /**
     * @param Context                    $context
     * @param Session                    $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl                $customerHelperData
     * @param Validator                  $formKeyValidator
     * @param AccountRedirect            $accountRedirect
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        Helper $helper
    ) {
        $this->session = $customerSession;
        $this->_helper = $helper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->messageManager = $context->getMessageManager();

        parent::__construct(
            $context,
            $customerSession,
            $customerAccountManagement,
            $customerHelperData,
            $formKeyValidator,
            $accountRedirect
        );
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {

        if ($this->session->isLoggedIn()
            || !$this->formKeyValidator->validate($this->getRequest())
        ) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('supplier');

            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate(
                        $login['username'],
                        $login['password']
                    );

                    if ($this->_helper->isSupplierNeedsToBeApproved()
                        && !$customer->getCustomAttribute('supplier_approve')
                    ) {
                        $this->messageManager->addError(__('Your account isn\'t approved yet.'));
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath('supplier');

                        return $resultRedirect;
                    }
                    $allowedGroups = $this->_helper->getAllowedGroups();
                    if (!in_array($customer->getGroupId(), $allowedGroups)) {
                        $this->messageManager->addError(__('Your are not supplier.'));
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath('supplier');

                        return $resultRedirect;
                    }

                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $this->session->regenerateId();
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('supplier');

                    return $resultRedirect;
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl
                        ->getEmailConfirmationUrl($login['username']);

                    $message = __(
                        'This account is not confirmed.' .
                        ' <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __('Invalid login or password.');
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('Invalid login or password.')
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('A login and a password are required.')
                );
            }
        }

        return $this->accountRedirect->getRedirect();
    }
}
