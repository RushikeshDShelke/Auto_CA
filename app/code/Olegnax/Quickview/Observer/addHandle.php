<?php

namespace Olegnax\Quickview\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class addHandle implements ObserverInterface
{
    protected $scopeConfig;

    protected $request;

    protected $storeManager;

    protected $productRepository;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Http $request,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer)
    {
        $layout = $observer->getData('layout');
        $fullActionName = $observer->getData('full_action_name');

        if ($fullActionName != 'ox_quickview_catalog_product_view') {
            return $this;
        }

        $productId = $this->request->getParam('id');
        if (isset($productId)) {
            try {
                $product = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
            } catch (NoSuchEntityException $e) {
                return false;
            }

            $productType = $product->getTypeId();

            $layout->getUpdate()->addHandle('ox_quickview_catalog_product_view_type_' . $productType);
            $tabsInInfo = $this->getConfig('athlete2_settings/product/product_tabs_position');
            /*
			$reviewsInTab = $this->getConfig('athlete2_settings/product/reviews_position');
			if ($reviewsInTab == 'oxbottom' && $reviewsInTab == 'bottom') {
                $layout->getUpdate()->addHandle('ox_quickview_catalog_product_view_move_review');
            }*/
            if ($tabsInInfo != 'info') {
                $layout->getUpdate()->addHandle('ox_quickview_catalog_product_view_remove_tabs');
            }
        }

        return $this;
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->getSystemValue($path, $storeCode);
    }

    public function getSystemValue($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }
}
