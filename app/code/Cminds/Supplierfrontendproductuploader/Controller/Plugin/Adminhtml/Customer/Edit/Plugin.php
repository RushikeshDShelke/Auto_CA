<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Plugin\Adminhtml\Customer\Edit;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Adminhtml\Index\Edit;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry as CoreRegistry;
use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Cminds Marketplace admin customer edit controller plugin.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Plugin
{
    /**
     * Core registry object.
     *
     * @var CoreRegistry
     */
    private $coreRegistry;

    /**
     * Customer repository object.
     *
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Customer view helper object.
     *
     * @var CustomerViewHelper
     */
    private $customerViewHelper;

    /**
     * Request object.
     *
     * @var RequestInterface
     */
    private $request;

    /**
     * Object constructor.
     *
     * @param CoreRegistry                $coreRegistry       Core registry object.
     * @param CustomerRepositoryInterface $customerRepository Customer repository object.
     * @param CustomerViewHelper          $customerViewHelper Customer view helper object.
     * @param RequestInterface            $request            Request object.
     */
    public function __construct(
        CoreRegistry $coreRegistry,
        CustomerRepositoryInterface $customerRepository,
        CustomerViewHelper $customerViewHelper,
        RequestInterface $request
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->customerRepository = $customerRepository;
        $this->customerViewHelper = $customerViewHelper;
        $this->request = $request;
    }

    /**
     * After execute plugin.
     *
     * @param Edit $subject    Subject object.
     * @param AbstractResult $resultPage Redirect object.
     *
     * @return AbstractResult
     */
    public function afterExecute(
        Edit $subject,
        AbstractResult $resultPage
    ) {
        if ($resultPage instanceof Redirect) {
            return $resultPage;
        }

        if (!$this->request->getParam('supplier')) {
            return $resultPage;
        }

        $resultPage->setActiveMenu('Cminds_Supplierfrontendproductuploader::manage_suppliers');

        $customerId = $this->coreRegistry
            ->registry(RegistryConstants::CURRENT_CUSTOMER_ID);

        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);

            $resultPage
                ->getConfig()
                ->getTitle()
                ->prepend($this->customerViewHelper->getCustomerName($customer));
        } else {
            $resultPage
                ->getConfig()
                ->getTitle()
                ->prepend(__('New Supplier'));
        }

        return $resultPage;
    }
}
