<?php

namespace Cminds\Supplierfrontendproductuploader\Model;

use Magento\Eav\Model\Entity\Attribute as AttributesCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Product extends AbstractModel
{
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_DISAPPROVED = 2;
    const STATUS_NONACTIVE = 3;

    /**
     * Valid Flag.
     *
     * @var bool
     */
    protected $valid;

    /**
     * Attributes Collection.
     *
     * @var AttributesCollection
     */
    protected $attributesCollection;

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param AttributesCollection $attributesCollection
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AttributesCollection $attributesCollection
    ) {
        parent::__construct($context, $registry);

        $this->attributesCollection = $attributesCollection;
    }

    /**
     * Validate Product, which is saved. Check if all required fields are not empty.
     *
     * @param bool $isConfigurable
     *
     * @throws \Exception
     */
    public function validate($isConfigurable = false)
    {
        $this->setIsValid(true);

        $data = $this->getData();
        $error = '';
        if (!\Zend_Validate::is($data['name'], 'NotEmpty')) {
            $error = 'Name is empty';
        }
        if (!\Zend_Validate::is($data['description'], 'NotEmpty')) {
            $error = 'Description is empty';
        }
        if ($isConfigurable
            && ((isset($data['weight'])
            && !\Zend_Validate::is($data['weight'], 'NotEmpty')))
        ) {
            $error = 'Weight is empty';
        }
        if ($isConfigurable
            && ((isset($data['qty'])
            && !\Zend_Validate::is($data['qty'], 'NotEmpty')))
        ) {
            $error = 'Qty is empty';
        }

        foreach ($data as $attributeCode => $value) {
            $required = $this->getRequiredAttributes();
            if (in_array($attributeCode, $required, true)) {
                continue;
            }
        }

        if (!isset($data['category']) || count($data['category']) < 1) {
            $error = 'Please select category';
        }

        if ($error != '') {
            $this->setIsValid(false);

            throw new \Exception($error);
        }
    }

    /**
     * Get Valid Flag.
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Set Valid Flag value.
     *
     * @param bool $flag
     *
     * @return Product
     * @throws LocalizedException
     */
    public function setIsValid($flag)
    {
        if (!is_bool($flag)) {
            throw new LocalizedException(__('Valid flag must contain boolean type'));
        }

        $this->valid = $flag;

        return $this;
    }

    /**
     * Get Required Attributes to fill in.
     *
     * @return array
     */
    public function getRequiredAttributes()
    {
        return [
            'name',
            'attribute_set_id',
            'short_description',
            'special_price',
            'description',
            'price',
            'gty',
            'weight',
            'submit',
            'category',
            'image',
            'sku',
        ];
    }
}
