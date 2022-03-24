<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data as DataHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Request as RequestHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cminds Supplierfrontendproductuploader product preview controller.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 */
class Preview extends AbstractController
{
    /**
     * Request helper object.
     *
     * @var RequestHelper
     */
    protected $requestHelper;

    /**
     * Preview constructor.
     *
     * @param Context               $context Context object.
     * @param DataHelper            $helper Data helper object.
     * @param RequestHelper         $requestHelper Request helper object.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     */
    public function __construct(
        Context $context,
        DataHelper $helper,
        RequestHelper $requestHelper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->requestHelper = $requestHelper;
    }

    /**
     * Execute controller main logic.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        return $this->redirectToProduct();
    }

    /**
     * Return result redirect object.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function redirectToProduct()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->requestHelper->getRedirectUrl());

        return $resultRedirect;
    }
}
