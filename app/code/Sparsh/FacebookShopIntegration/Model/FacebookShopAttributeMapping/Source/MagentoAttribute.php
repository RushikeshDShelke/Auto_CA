<?php
/**
 * Class MagentoAttribute
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookShopIntegration\Model\FacebookShopAttributeMapping\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class MagentoAttribute
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class MagentoAttribute implements OptionSourceInterface
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $entity;

    /**
     * MagentoAttribute constructor.
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $eavAttribute
     * @param \Magento\Eav\Model\Entity $entity
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $eavAttribute,
        \Magento\Eav\Model\Entity $entity
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->entity = $entity;
    }
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributeCollection = $this->eavAttribute->create();
        $attributeCollection->addFieldToSelect('attribute_code')->addFieldToFilter(
            'entity_type_id',
            $this->entity->setType('catalog_product')->getTypeId()
        );
        $options = [];
        foreach ($attributeCollection as $attribute) {
            $options[] = [
                'label' => $attribute->getAttributeCode(),
                'value' => $attribute->getAttributeCode()
            ];
        }
        return $options;
    }
}
