<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Source;

use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\ResourceModel\Helper;

class Suppliers extends AbstractSource
{
    private $customerCollectionFactory;
    private $scopeConfig;
    private $eavResourceHelper;

    public function __construct(
        CollectionFactory $customerCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        Helper $eavResourceHelper
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->eavResourceHelper = $eavResourceHelper;
    }

    public function getAllOptions()
    {
        $supplierGroup = (int)$this->scopeConfig->getValue(
            'configuration/suppliers_group/supplier_group'
        );
        $supplierEditGroup = (int)$this->scopeConfig->getValue(
            'configuration/suppliers_group/suppliert_group_which_can_edit_own_products'
        );

        /** @var Collection $collection */
        $collection = $this->customerCollectionFactory->create();
        $collection
            ->addFieldToFilter(
                'group_id',
                $supplierEditGroup
            );
        $this->_options[] = ['label' => 'none', 'value' => null];

        foreach ($collection as $customer) {
            $fullName = $customer->getFirstname() . ' ' . $customer->getLastname();
            $this->_options[] = [
                'label' => $fullName,
                'value' => $customer->getId(),
            ];
        }

        if ($supplierGroup !== $supplierEditGroup) {
            $collection = $this->customerCollectionFactory->create();
            $collection
                ->addAttributeToSelect('*')
                ->addFieldToFilter(
                    'group_id',
                    $supplierGroup
                );

            foreach ($collection as $customer) {
                $fullName = $customer->getFirstname()
                    . ' ' . $customer->getLastname();
                $this->_options[] = [
                    'label' => $fullName,
                    'value' => $customer->getId(),
                ];
            }
        }

        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }

    /**
     * Retrieve flat column definition.
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $attributeType = $this->getAttribute()->getBackendType();

        return [
            $attributeCode => [
                'unsigned' => true,
                'default' => null,
                'extra' => null,
                'type' => $this->eavResourceHelper
                    ->getDdlTypeByColumnType($attributeType),
                'nullable' => true,
            ],
        ];
    }
}
