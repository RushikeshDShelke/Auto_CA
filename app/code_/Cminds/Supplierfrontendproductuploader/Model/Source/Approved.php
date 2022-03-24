<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\ResourceModel\Helper;

class Approved extends AbstractSource
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DISAPPROVED = 2;
    const STATUS_NONACTIVE = 3;

    private $eavResourceHelper;

    public function __construct(Helper $eavResourceHelper)
    {
        $this->eavResourceHelper = $eavResourceHelper;
    }

    public function getAllOptions()
    {
        $this->_options = [
            ['label' => 'Pending', 'value' => self::STATUS_PENDING],
            ['label' => 'Approved', 'value' => self::STATUS_APPROVED],
            [
                'label' => 'Disapproved',
                'value' => self::STATUS_DISAPPROVED,
            ],
            ['label' => 'Not Active', 'value' => self::STATUS_NONACTIVE],
        ];

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
