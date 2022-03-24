<?php

namespace Cminds\Supplierfrontendproductuploader\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class Inventory extends AbstractHelper
{
    /**
     * Product metadata
     *
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Module Manager
     *
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * Inventory constructor.
     *
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleManager $moduleManager
     * @param Context $context
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        ModuleManager $moduleManager,
        Context $context
    ) {
        parent::__construct($context);

        $this->productMetadata = $productMetadata;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Check version for MSI
     *
     * @return bool
     */
    public function msiFunctionalitySupported()
    {
        $result = false;
        $currentVersion = $this->productMetadata->getVersion();

        if( $currentVersion ){
            $currentVersion = explode('.', $currentVersion);
            // MSI is added in 2.3 and greater
            if( $currentVersion[1] >= 3 ) 
                $result = true;

                // check if some of the used modules were not disabled through console
                $modulesToCheck = [
                    'Magento_InventoryApi', 
                    'Magento_Inventory',
                    'Magento_InventoryCatalogAdminUi', 
                    'Magento_InventorySalesApi'
                ];
                foreach ( $modulesToCheck as $moduleName) {
                    $result = $result && $this->moduleManager->isEnabled($moduleName);
                }
        }

        return $result;
    }
}