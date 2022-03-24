<?php

namespace Cminds\Supplierfrontendproductuploader\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

class Attribute extends AbstractHelper
{
    const SUPPLIER_PRODUCT_DEFAULT_ATTRIBUTE_SET = 'products_settings/adding_products/attributes_set';

    /**
     * Product Attrbiute Repository.
     *
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * Configurable Product Type Object.
     *
     * @var ConfigurableType
     */
    private $configurableType;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * Attribute constructor.
     *
     * @param Context $context
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ConfigurableType $configurableType
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Context $context,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        ConfigurableType $configurableType,
        AttributeSetFactory $attributeSetFactory       
    ) {
        parent::__construct($context);

        $this->productAttributeRepository = $productAttributeRepository;
        $this->configurableType = $configurableType;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * Check if current attribute can be used as configurable.
     *
     * @param EavAttribute $attribute
     *
     * @return bool
     */
    public function canUseAttributeAsConfigurable(EavAttribute $attribute)
    {
        $eavAttribute = $this->productAttributeRepository->get($attribute->getAttributeCode());

        return $this->configurableType->canUseAttribute($eavAttribute);
    }

    /**
     * Get available attributes sets for suppliers.
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    public function getAvailableAttributeSets()
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $sets */
        $sets = $this->attributeSetFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'available_for_supplier',
                1
            );

        return $sets;
    }

    /**
     * Get array with available attribute sets ids
     * @return array
     */
    public function getValidSetsIds() {
        $result = array();
        foreach ($this->getAvailableAttributeSets() as $set) {
            $result[] = $set->getId();
        }
        return $result;
    }

    /**
     * Get default set config key
     * @return string
     */
    public function getDefaultSetConfigKey() {
        return self::SUPPLIER_PRODUCT_DEFAULT_ATTRIBUTE_SET;
    }
}
