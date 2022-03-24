<?php

namespace Cminds\Marketplace\Controller\Reports;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Exportorders extends AbstractController
{
    protected $registry;
    protected $fileFactory;

    public function __construct(
        Context $context,
        Data $helper,
        Registry $registry,
        FileFactory $fileFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->registry = $registry;
        $this->fileFactory = $fileFactory;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $fileName = 'orders-' . gmdate('YmdHis') . '.csv';
        $grid = $this->_view
            ->getLayout()
            ->createBlock('Cminds\Marketplace\Block\Report\Order');
        $this->fileFactory->create($fileName, $grid->getCsvFileEnhanced());
    }
}
