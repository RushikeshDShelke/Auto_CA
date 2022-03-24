<?php

namespace Cminds\Marketplace\Controller\Settings;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Marketplace\Model\Fields;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\FileUploaderFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http\Interceptor;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cminds Marketplace supplier profile save controller.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 */
class Profilesave extends AbstractController
{
    /**
     * Interceptor object.
     *
     * @var Interceptor
     */
    protected $interceptor;

    /**
     * Transaction object.
     *
     * @var Transaction
     */
    protected $transaction;

    /**
     * Session object.
     *
     * @var Session
     */
    protected $customerSession;

    /**
     * Customer object.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Filesystem object.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Customer object.
     *
     * @var Customer
     */
    protected $customerRepositoryInterface;

    /**
     * Customer factory object.
     *
     * @var Customer
     */
    protected $customerFactory;

    /**
     * Uploader factory object.
     *
     * @var FileUploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * Directory list object.
     *
     * @var DirectoryList
     */
    protected $dir;

    /**
     * Fields object.
     *
     * @var Fields
     */
    protected $fields;

    /**
     * Object constructor.
     *
     * @param Context                     $context       Context object.
     * @param Data                        $helper        Data helper object.
     * @param Interceptor                 $interceptor   Interceptor object.
     * @param Transaction                 $transaction   Transaction object.
     * @param Session                     $session       Session object.
     * @param Customer                    $customer      Customer object.
     * @param CustomerFactory             $customerFactory
     * @param DirectoryList               $directoryList Directory list object.
     * @param Filesystem                  $filesystem
     * @param Fields                      $fields        Fields object.
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param UploaderFactory             $uploaderFactory
     * @param StoreManagerInterface       $storeManager
     * @param ScopeConfigInterface        $scopeConfig
     */
    public function __construct(
        Context $context,
        Data $helper,
        Interceptor $interceptor,
        Transaction $transaction,
        Session $session,
        Customer $customer,
        CustomerFactory $customerFactory,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        Fields $fields,
        CustomerRepositoryInterface $customerRepositoryInterface,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->interceptor = $interceptor;
        $this->transaction = $transaction;
        $this->customerSession = $session;
        $this->customer = $customer;
        $this->customerFactory = $customerFactory;
        $this->dir = $directoryList;
        $this->fileUploaderFactory = $uploaderFactory;
        $this->fields = $fields;
        $this->filesystem = $filesystem;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Execute main controller logic.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }
        $postData = $this->getRequest()->getParams();

        $value = $this->scopeConfig->getValue(
            'configuration_marketplace/configure/enable_supplier_pages'
        );
        if (!$value) {
            $this->interceptor->setHeader('HTTP/1.1', '404 Not Found');
            $this->interceptor->setHeader('Status', '404 File not found');
            $this->_forward('defaultNoRoute');
        }

        try {
            $customerModel = null;
            if ($this->customerSession->isLoggedIn()) {
                $customerData = $this->customerFactory->create()
                    ->load($this->customerSession->getId());
                $customerModel = $this->customerRepositoryInterface
                    ->getById($this->customerSession->getId());
            }

            if (!$customerModel) {
                throw new LocalizedException(__('Supplier does not exists'));
            }

            $waitingForApproval = false;

            if (isset($postData['submit'])) {
                $changed = false;
                $forceChange = false;

                if (!isset($postData['name']) || $postData['name'] === '') {
                    throw new LocalizedException(__('The name for the supplier should be specified'));
                }

                $converted = htmlentities(
                    $postData['name'],
                    ENT_QUOTES,
                    'UTF-8'
                );
                if ($converted !== $customerData->getSupplierName()) {
                    $changed = true;
                    $forceChange = true;
                    $customerModel->setCustomAttribute(
                        'supplier_name_new',
                        $converted
                    );
                }

                $path = $this->dir->getUrlPath('media') . '/supplier_logos/';

                if (isset($postData['remove_logo'])) {
                    $s = $customerData->getSupplierLogo();

                    if (file_exists($path . $s)) {
                        unlink($path . $s);
                    }

                    $customerModel->setCustomAttribute('supplier_logo', null);
                }

                $files = $this->getRequest()->getFiles();

                if (isset($files['logo']['name'])
                    && $files['logo']['tmp_name'] !== null
                    && $files['logo']['name'] !== ''
                ) {
                    $uploader = $this->fileUploaderFactory->create(['fileId' => 'logo']);
                    $uploader->setAllowedExtensions([
                        'jpg',
                        'jpeg',
                        'gif',
                        'png',
                    ]);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $path = $this->filesystem->getDirectoryRead(UrlInterface::URL_TYPE_MEDIA)
                        ->getAbsolutePath('supplier_logos/');
                    $nameSplit = explode('.', $files['logo']['name']);
                    $ext = $nameSplit[count($nameSplit) - 1];
                    $newName = md5($files['logo']['name'] . time()) . '.' . $ext;
                    $uploader->save($path, $newName);
                    $customerModel->setCustomAttribute(
                        'supplier_logo',
                        $newName
                    );
                }

                $stripped = strip_tags(
                    $postData['description'],
                    '<ol><li><b><span><a><i><u><p><br><h1><h2><h3><h4><h5><div>'
                );

                if (!$stripped) {
                    throw new LocalizedException(__('Description for supplier is not set'));
                }

                if ($stripped !== $customerData->getSupplierDescription()
                    || $forceChange
                ) {
                    $customerModel->setCustomAttribute(
                        'supplier_description_new',
                        $stripped
                    );

                    if (!$changed) {
                        $customerModel->setCustomAttribute(
                            'supplier_name_new',
                            htmlentities(
                                $postData['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            )
                        );
                    }

                    $changed = true;
                }

                if (isset($postData['profile_enabled'])) {
                    $customerModel->setCustomAttribute(
                        'supplier_profile_visible',
                        1
                    );
                } else {
                    $customerModel->setCustomAttribute(
                        'supplier_profile_visible',
                        0
                    );
                }

                if ($changed) {
                    $customerModel->setCustomAttribute(
                        'rejected_notfication_seen',
                        2
                    );
                    $waitingForApproval = true;
                }

                $customFieldsCollection = $this->fields->getCollection();
                $customFieldsValues = [];
                $oldCustomFieldsValues = unserialize(
                    $customerData->getCustomFieldsValues()
                );

                foreach ($customFieldsCollection as $field) {
                    if (isset($postData[$field->getName()])) {
                        if ($field->getIsRequired()
                            && $postData[$field->getName()] === ''
                        ) {
                            throw new LocalizedException(
                                __('Field ' . $field->getName() . ' is required')
                            );
                        }

                        if ($field->getType() === 'date'
                            && !strtotime($postData[$field->getName()])
                        ) {
                            throw new LocalizedException(
                                __('Field ' . $field->getName() . ' is not valid date')
                            );
                        }

                        $oldValue = $this->findValue(
                            $field->getName(),
                            $oldCustomFieldsValues
                        );

                        if ($oldValue !== $postData[$field->getName()]
                            && $field->getMustBeApproved()
                        ) {
                            $waitingForApproval = true;
                        }

                        $customFieldsValues[] = [
                            'name' => $field->getName(),
                            'value' => $postData[$field->getName()],
                        ];
                    }
                }

                if ($waitingForApproval) {
                    $customerModel->setCustomAttribute(
                        'new_custom_fields_values',
                        serialize($customFieldsValues)
                    );
                } else {
                    $customerModel->setCustomAttribute(
                        'custom_fields_values',
                        serialize($customFieldsValues)
                    );
                }

            } elseif (isset($postData['clear'])) {
                $customerModel->setCustomAttribute(
                    'supplier_name_new',
                    null
                );
                $customerModel->setCustomAttribute(
                    'supplier_description_new',
                    null
                );
                $customerModel->setCustomAttribute(
                    'new_custom_fields_values',
                    null
                );
            }

            $this->customerRepositoryInterface->save($customerModel);

            if ($waitingForApproval) {
                $this->messageManager->addSuccess(
                    __('Profile was changed and waiting for admin approval')
                );
            } else {
                $this->messageManager->addSuccess(
                    __('Your profile was changed')
                );
            }

            $this->_redirect('marketplace/settings/profile/');
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('marketplace/settings/profile/');
        }
    }

    /**
     * Search if value exists in array, return found value
     * or false when not found.
     *
     * @param string $name Name.
     * @param array  $data Data array.
     *
     * @return bool|mixed
     */
    private function findValue($name, $data)
    {
        if (!is_array($data)) {
            return false;
        }

        foreach ($data as $value) {
            if ($value['name'] === $name) {
                return $value['value'];
            }
        }

        return false;
    }
}
