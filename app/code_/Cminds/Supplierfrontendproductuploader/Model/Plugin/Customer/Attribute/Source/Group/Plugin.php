<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Plugin\Customer\Attribute\Source\Group;

use Cminds\Supplierfrontendproductuploader\Observer\Adminhtml\CustomerNewPreDispatch;
use Magento\Customer\Model\Customer\Attribute\Source\Group;
use Magento\Framework\Registry as CoreRegistry;
use Cminds\Supplierfrontendproductuploader\Helper\Data as SupplierHelper;

/**
 * Cminds Marketplace customer group source model plugin.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotr@cminds.com>
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
     * @var SupplierHelper
     */
    private $supplierHelper;

    /**
     * Object constructor.
     *
     * @param CoreRegistry   $coreRegistry
     * @param SupplierHelper $supplierHelper
     */
    public function __construct(
        CoreRegistry $coreRegistry,
        SupplierHelper $supplierHelper
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->supplierHelper = $supplierHelper;
    }

    /**
     * @param Group $subject
     * @param array $options
     *
     * @return array
     */
    public function afterGetAllOptions( // @codingStandardsIgnoreLine
        Group $subject,
        array $options
    ) {
        $flag = $this->coreRegistry
            ->registry(CustomerNewPreDispatch::LOAD_SUPPLIER_GROUPS_FLAG);

        if ($flag) {
            $supplierGroupIds = $this->supplierHelper->getAllowedGroups();
            foreach ($options as $index => $option) {
                if (in_array($option['value'], $supplierGroupIds)) {
                    continue;
                }

                unset($options[$index]);
            }

            $options = array_values($options);
        }

        return $options;
    }
}
