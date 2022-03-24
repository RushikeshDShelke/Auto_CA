<?php

namespace Cminds\Marketplace\Observer;

use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;

class ProductNeedsApproval implements ObserverInterface
{
    const GENERAL_ADMIN_EMAIL = 'trans_email/ident_general/email';
    const GENERAL_ADMIN_NAME = 'trans_email/ident_general/name';
    const PRODUCT_APPROVAL_EMAIL_TEMPLATE = 'product_approval_notify_admin';

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var MarketplaceHelper
     */
    private $marketplaceHelper;

    /**
     * ProductNeedsApproval constructor.
     *
     * @param TransportBuilder            $transportBuilder
     * @param ScopeConfigInterface        $scopeConfig
     * @param StoreManagerInterface       $storeManager
     * @param ProductRepositoryInterface  $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param MarketplaceHelper           $marketplaceHelper
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        MarketplaceHelper $marketplaceHelper
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->marketplaceHelper = $marketplaceHelper;
    }

    /**
     * Observer execute method.
     *
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $store = $this->storeManager->getStore()->getId();
        $productId = $observer->getProductId();
        $product = $this->productRepository->getById($productId);
        $supplierId = $this->marketplaceHelper->getSupplierId();
        $supplier = $this->customerRepository->getById($supplierId);
        $productCreator = $supplier->getFirstname() . ' ' . $supplier->getLastname();

        $adminEmail = $this->scopeConfig->getValue(self::GENERAL_ADMIN_EMAIL);
        $adminName = $this->scopeConfig->getValue(self::GENERAL_ADMIN_NAME);

        $templateVars = [
            'admin_name' => $adminName,
            'product_id' => $product->getId(),
            'product_name' => $product->getName(),
            'product_sku' => $product->getSku(),
            'product_creator' => $productCreator,
        ];

        $this->sendEmail(
            $adminEmail,
            $templateVars,
            $store,
            self::PRODUCT_APPROVAL_EMAIL_TEMPLATE
        );
    }

    /**
     * Send email.
     *
     * @param $recipient
     * @param $templateVars
     * @param $store
     * @param $emailTemplate
     *
     * @throws MailException
     */
    private function sendEmail(
        $recipient,
        $templateVars,
        $store,
        $emailTemplate
    ) {
        $transport = $this->transportBuilder->setTemplateIdentifier($emailTemplate)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $store])
            ->setTemplateVars($templateVars)
            ->setFrom('general')
            ->addTo($recipient)
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch (MailException $e) {
            // @TODO: Logger.
        }
    }
}
