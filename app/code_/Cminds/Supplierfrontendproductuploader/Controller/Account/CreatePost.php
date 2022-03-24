<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Account;

use Cminds\Supplierfrontendproductuploader\Helper\Data as Helper;
use Cminds\Supplierfrontendproductuploader\Model\AccountManagement as CmindsAccountManagment;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\UrlFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Framework\Data\Form\FormKey\Validator;

class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    /** @var AccountManagementInterface */
    protected $accountManagement;

    /** @var Address */
    protected $addressHelper;

    /** @var FormFactory */
    protected $formFactory;

    /** @var SubscriberFactory */
    protected $subscriberFactory;

    /** @var RegionInterfaceFactory */
    protected $regionDataFactory;

    /** @var AddressInterfaceFactory */
    protected $addressDataFactory;

    /** @var Registration */
    protected $registration;

    /** @var CustomerInterfaceFactory */
    protected $customerDataFactory;

    /** @var CustomerUrl */
    protected $customerUrl;

    /** @var Escaper */
    protected $escaper;

    /** @var CustomerExtractor */
    protected $customerExtractor;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;

    /** @var DataObjectHelper */
    protected $dataObjectHelper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    protected $helper;

    /**
     * @param Context                    $context
     * @param Session                    $customerSession
     * @param ScopeConfigInterface       $scopeConfig
     * @param StoreManagerInterface      $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param Address                    $addressHelper
     * @param UrlFactory                 $urlFactory
     * @param FormFactory                $formFactory
     * @param SubscriberFactory          $subscriberFactory
     * @param RegionInterfaceFactory     $regionDataFactory
     * @param AddressInterfaceFactory    $addressDataFactory
     * @param CustomerInterfaceFactory   $customerDataFactory
     * @param CustomerUrl                $customerUrl
     * @param Registration               $registration
     * @param Escaper                    $escaper
     * @param CustomerExtractor          $customerExtractor
     * @param DataObjectHelper           $dataObjectHelper
     * @param AccountRedirect            $accountRedirect
     * @param CustomerRepository         $customerRepository
     * @param Validator                  $formKeyValidator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor,
        DataObjectHelper $dataObjectHelper,
        AccountRedirect $accountRedirect,
        CmindsAccountManagment $cmindsAccountManagment,
        Helper $helper,
        CustomerRepository $customerRepository,
        Validator $formKeyValidator = null
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $scopeConfig,
            $storeManager,
            $accountManagement,
            $addressHelper,
            $urlFactory,
            $formFactory,
            $subscriberFactory,
            $regionDataFactory,
            $addressDataFactory,
            $customerDataFactory,
            $customerUrl,
            $registration,
            $escaper,
            $customerExtractor,
            $dataObjectHelper,
            $accountRedirect,
            $customerRepository,
            $formKeyValidator
        );

        $this->session = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->accountManagement = $cmindsAccountManagment;

        $this->addressHelper = $addressHelper;
        $this->formFactory = $formFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerUrl = $customerUrl;
        $this->registration = $registration;
        $this->escaper = $escaper;
        $this->customerExtractor = $customerExtractor;
        $this->urlModel = $urlFactory->create();
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountRedirect = $accountRedirect;
        $this->helper = $helper;
    }

    /**
     * Create customer account action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) {
            $resultRedirect->setPath('*/*/');

            return $resultRedirect;
        }

        if (!$this->getRequest()->isPost()) {
            $url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->error($url));

            return $resultRedirect;
        }

        $this->session->regenerateId();

        try {
            $supplier_group_id = $this->scopeConfig->getValue(
                'configuration/registration_and_login/register_customer_group'
            );

            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];

            $customer = $this->customerExtractor->extract(
                'customer_account_create',
                $this->_request
            );
            $customer->setAddresses($addresses);
            $customer->setGroupId($supplier_group_id);

            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            $redirectUrl = $this->session->getBeforeAuthUrl();

            $this->checkPasswordConfirmation($password, $confirmation);

            $customer = $this->accountManagement
                ->createAccount($customer, $password, $redirectUrl);

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $this->subscriberFactory
                    ->create()
                    ->subscribeCustomerById($customer->getId());
            }

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $confirmationStatus = $this->accountManagement
                ->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $email = $this->customerUrl
                    ->getEmailConfirmationUrl($customer->getEmail());
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(
                    __(
                        'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                        $email
                    )
                );
                // @codingStandardsIgnoreEnd
                $url = $this->urlModel->getUrl();
                $resultRedirect->setUrl($this->_redirect->success($url));
            } else {
                if ($this->helper->isSupplierNeedsToBeApproved()) {
                    $this->messageManager->addSuccess(
                        __(
                            'Thank you for creating account on our store. '
                            . 'Your account must be approved by store admin. '
                            . 'When it will be done we will send you an email.'
                        )
                    );
                    $url = $this->urlModel->getUrl();
                } else {
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    $url = $this->urlModel->getUrl('supplier');
                }

                $this->messageManager->addSuccess($this->getSuccessMessage());

                $resultRedirect->setUrl($url);
            }

            return $resultRedirect;
        } catch (StateException $e) {
            $url = $this->urlModel->getUrl('customer/account/forgotpassword');
            // @codingStandardsIgnoreStart
            $message = __(
                'There is already an account with this email address. '
                . 'If you are sure that it is your email address, '
                . '<a href="%1">click here</a> to get your password '
                . 'and access your account.',
                $url
            );
            // @codingStandardsIgnoreEnd
            $this->messageManager->addError($message);
        } catch (InputException $e) {
            $this->messageManager->addError(
                $this->escaper->escapeHtml($e->getMessage())
            );
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError(
                    $this->escaper->escapeHtml($error->getMessage())
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('We can\'t save the customer.')
            );
        }

        $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        $defaultUrl = $this->urlModel->getUrl(
            '*/*/create',
            ['_secure' => true]
        );
        $resultRedirect->setUrl($this->_redirect->error($defaultUrl));

        return $resultRedirect;
    }
}
