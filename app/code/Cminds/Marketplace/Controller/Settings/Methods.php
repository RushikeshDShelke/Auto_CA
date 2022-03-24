<?php

namespace Cminds\Marketplace\Controller\Settings;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Marketplace\Model\MethodsFactory as MarketplaceMethods;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;

class Methods extends AbstractController
{
    /**
     * @var MarketplaceMethods
     */
    private $marketplaceMethods;

    /**
     * @var MarketplaceHelper
     */
    private $marketplaceHelper;

    /**
     * Methods constructor.
     *
     * @param Context               $context
     * @param Data                  $helper
     * @param MarketplaceMethods    $marketplaceMethods
     * @param MarketplaceHelper     $marketplaceHelper
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     */
    public function __construct(
        Context $context,
        Data $helper,
        MarketplaceMethods $marketplaceMethods,
        MarketplaceHelper $marketplaceHelper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->marketplaceMethods = $marketplaceMethods;
        $this->marketplaceHelper = $marketplaceHelper;
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $value = $this->scopeConfig->getValue(
            'configuration_marketplace/'
            . 'presentation/allow_supplier_manage_shipping_costs'
        );

        if (!$value) {
            $this->messageManager
                ->addErrorMessage(__('Managing shipping methods by supplier is currently disabled by admin.'));
            $this->_redirect('supplier');
        }

        $this->_view->loadLayout();
        $this->renderBlocks();
        $this->_view->renderLayout();
    }

    /**
     * Get saved methods.
     *
     * @return AbstractCollection
     */
    public function getSavedMethods()
    {
        $records = $this->marketplaceMethods->create()->getCollection();
        $records->addFilter(
            'supplier_id',
            $this->marketplaceHelper->getSupplierId()
        );

        return $records;
    }
}
