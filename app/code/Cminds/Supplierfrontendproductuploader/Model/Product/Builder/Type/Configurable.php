<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Product\Builder\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Exception\LocalizedException;

class Configurable
{
    /**
     * Eav Config.
     *
     * @var Config
     */
    private $eavConfig;

    /**
     * Collection Factory.
     *
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Attribute Factory.
     *
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * Product Attribute Repository.
     *
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * Option Value Factory.
     *
     * @var OptionValueInterfaceFactory
     */
    private $optionValueFactory;

    /**
     * Configurable Product Type Object.
     * Model, that works more like Helper.
     *
     * @var ConfigurableType
     */
    private $configurableType;

    /**
     * Configurable constructor.
     *
     * @param Config $eavConfig
     * @param CollectionFactory $collectionFactory
     * @param AttributeFactory $attributeFactory
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param OptionValueInterfaceFactory $optionValueInterfaceFactory
     * @param ConfigurableType $configurableType
     */
    public function __construct(
        Config $eavConfig,
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        OptionValueInterfaceFactory $optionValueInterfaceFactory,
        ConfigurableType $configurableType
    )
    {
        $this->eavConfig = $eavConfig;
        $this->collectionFactory = $collectionFactory;
        $this->attributeFactory = $attributeFactory;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->optionValueFactory = $optionValueInterfaceFactory;
        $this->configurableType = $configurableType;
    }

    /**
     * Fill product with specified attributes, which should be configurable.
     *
     * @param ProductInterface $product
     * @param array $attributes
     *
     * @return ProductInterface
     */
    public function fillProductWithConfigurableAttributes(ProductInterface $product, array $attributes)
    {
        if (empty($attributes)) {
            return $product;
        }

        $configurable = $product;
        $configurableAttributesData = [];

        foreach ($attributes as $attribute) {
            $configurableAttribute = $this->eavConfig->getAttribute(
                'catalog_product',
                $attribute
            );

            $optionCollection = $this->collectionFactory->create()
                ->setAttributeFilter($configurableAttribute->getId());

            $options = $optionCollection->getData();
            $label = $configurableAttribute->getDefaultFrontendLabel();

            $values = [];
            foreach ($options as $option) {
                $values[] = ['value_index' => $option['option_id']];
            }

            $configurableAttributesData[] = [
                'attribute_id' => $configurableAttribute->getId(),
                'code' => $configurableAttribute->getAttributeCode(),
                'label' => $label,
                'position' => '0',
                'values' => $values,
            ];
        }

        if (empty($configurableAttributesData)) {
            return $configurable;
        }

        $options = $this->createOptionEntity($configurableAttributesData);

        $extensionConfigurableAttributes = $configurable->getExtensionAttributes();
        $extensionConfigurableAttributes->setConfigurableProductOptions($options);

        $configurable
            ->setExtensionAttributes($extensionConfigurableAttributes)
            ->setCanSaveConfigurableAttributes(true)
            ->setConfigurableAttributesData(
                $configurableAttributesData
            );

        return $configurable;
    }

    /**
     * Create option entity for the configurable attribute.
     *
     * @param array $attributesData
     *
     * @return array
     * @throws LocalizedException
     */
    public function createOptionEntity(array $attributesData)
    {
        $options = [];

        foreach ($attributesData as $item) {
            $attribute = $this->attributeFactory->create();
            $eavAttribute = $this->productAttributeRepository->get($item['attribute_id']);

            if (!$this->configurableType->canUseAttribute($eavAttribute)) {
                throw new LocalizedException(__('Provided attribute can not be used with configurable product.'));
            }

            $attribute = $this->updateAttributeData($attribute, $item);
            $options[] = $attribute;
        }

        return $options;
    }

    /**
     * Update Attribute Data.
     *
     * @param OptionInterface $attribute
     * @param array $item
     *
     * @return OptionInterface
     */
    private function updateAttributeData(OptionInterface $attribute, array $item)
    {
        $values = [];
        foreach ($item['values'] as $value) {
            $option = $this->optionValueFactory->create();
            $option->setValueIndex($value['value_index']);
            $values[] = $option;
        }

        $attribute->setData(
            array_replace_recursive(
                (array)$attribute->getData(),
                $item
            )
        );

        $attribute->setValues($values);

        return $attribute;
    }

    /**
     * Link product as associated to the configurable.
     *
     * @param ProductInterface $configurable
     * @param ProductInterface $candidate
     *
     * @return ProductInterface
     * @throws LocalizedException
     */
    public function addNewLink(ProductInterface $configurable, ProductInterface $candidate)
    {
        try {
            if (!$candidate->getId()) {
                throw new LocalizedException(__('Associated product doesn\'t have id'));
            }

            $attributeCodes = [];
            $attributes = $this->configurableType->getConfigurableAttributes($configurable);
            foreach ($attributes as $attribute) {
                $attributeCodes[] = $attribute
                    ->getProductAttribute()
                    ->getAttributeCode();
            }

            if (empty($attributeCodes)) {
                throw new LocalizedException(__('Can not link associated product to the configurable product.'));
            }

            $configurable = $this->fillProductWithConfigurableAttributes($configurable, $attributeCodes);

            $extensionConfigurableAttributes = $configurable->getExtensionAttributes();
            $associatedProductIds = $extensionConfigurableAttributes->getConfigurableProductLinks() ?: [];
            $associatedProductIds[] = (int)$candidate->getId();

            $extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);

            $configurable->setExtensionAttributes($extensionConfigurableAttributes);

            $configurable->save();
        } catch (\Exception $exception) {
            throw new LocalizedException(__($exception->getMessage()));
        }

        return $configurable;
    }
}
