<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Sources;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data as CmindsHelper;
use Cminds\Supplierfrontendproductuploader\Model\SourcesFactory as SourcesFactory;
use Cminds\Supplierfrontendproductuploader\Model\ResourceModel\Sources\Collection  as SourcesCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\Store;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Response\Http\Interceptor;
use Psr\Log\LoggerInterface;
use Cminds\Supplierfrontendproductuploader\Model\Product\Inventory as ProductUploaderInventory;

class SaveSource extends AbstractController
{
    /**
     * @var SourcesFactory
     */
    protected $_sourcesFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var SourcesCollection
     */
    protected $_sourcesCollection;

    /**
     * @var ProductUploaderInventory
     */
    private $productUploaderInventory;

    /**
     * @var Interceptor
     */
    protected $interceptor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        CmindsHelper $helper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        SourcesFactory $sourcesFactory,
        CustomerSession $customerSession,
        SourcesCollection $sourcesCollection,
        ProductUploaderInventory $productUploaderInventory,
        Interceptor $interceptor,
        LoggerInterface $logger
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_sourcesFactory = $sourcesFactory;
        $this->customerSession = $customerSession;
        $this->_sourcesCollection = $sourcesCollection;
        $this->productUploaderInventory = $productUploaderInventory;
        $this->interceptor = $interceptor;
        $this->logger = $logger;

        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $value = $this->scopeConfig->getValue(
            'configuration/configure/source_suggestion'
        );
        if (!$value) {
            $this->interceptor->setHeader('HTTP/1.1', '404 Not Found');
            $this->interceptor->setHeader('Status', '404 File not found');
            $this->_forward('defaultNoRoute');
        }

        $redirectTarget = '*/*/suggestsource';

        $this->currentStore = $this->getStoreManager()->getStore();

        $this->getStoreManager()
            ->setCurrentStore(Store::DEFAULT_STORE_ID);

        if ($this->getRequest()->getParams()) {
            $postData = $this->getRequest()->getParams();

            $dataCodes = [
                'name' => 'name',
                'code' => 'source_code',
                'description' => 'description',
                'latitude' => 'latitude',
                'longitude' => 'longitude',
                'contact-name' => 'contact_name',
                'email' => 'email',
                'phone' => 'phone',
                'fax' => 'fax',
                'country_id' => 'country_id',
                'region' => 'region',
                'region_id' => 'region_id',
                'city' => 'city',
                'street' => 'street',
                'postcode' => 'postcode',
            ];

            if ($this->_validateFields($postData)) {
                // check for existing codes
                // from user suggested sources
                // or from system sources
                $customerSourcesByCode = $this->_sourcesCollection->addFieldToFilter(
                    'source_code',
                    $this->getRequest()->getParam('code')
                );
                if ($customerSourcesByCode->count() > 0
                    || $this->productUploaderInventory->isSourceCodeUsed($this->getRequest()->getParam('code'))
                ) {
                    $this->messageManager->addErrorMessage(__('Such source code exists'));
                    return $this->_redirect($redirectTarget);
                }

                $suggestion = $this->_sourcesFactory->create();


                foreach ($dataCodes as $key => $value) {
                    if (isset($postData[$key])) {
                        $suggestion->setData($value, $this->getRequest()->getParam($key));
                    }
                }

                // add additional data
                $suggestion->setCustomerEmail($this->customerSession->getCustomer()->getEmail());
                $suggestion->setCustomerId($this->customerSession->getCustomer()->getEntityId());
                $suggestion->setWebsiteId($this->customerSession->getCustomer()->getWebsiteId());

                try {
                    $suggestion->save();
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('Action failed.')
                    );
                    $this->logger->critical($e);
                }

                $this->messageManager->addSuccessMessage(__('Your data was sent'));
            }
        }
        return $this->_redirect($redirectTarget);
    }


    protected function _validateFields(array $data)
    {
        $pass = true;
        $requiredFields = ['name', 'code', 'postcode', 'country_id'];
        foreach ($requiredFields as $value) {
            if ('code' == $value) {
                $code = $this->getRequest()->getParam('code');
                if (count(explode(' ', $code)) > 1) {
                    $this->getRequest()->setParam(
                        'code',
                        str_replace(' ', '_', trim($code))
                    );
                }
            }

            if (!isset($data[$value]) || !$data[$value]) {
                $pass = false;
            }
        }

        if (false === $pass) {
            $this->messageManager->addErrorMessage(__('Not all required fields vere populated.'));
        }
        return $pass;
    }
}
