<?php
/**
 * Class FacebookAttribute
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
 * Class FacebookAttribute
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class FacebookAttribute implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            ['label' => 'id', 'value' => 'id'],
            ['label' => 'title', 'value' => 'title'],
            ['label' => 'description', 'value' => 'description'],
            ['label' => 'condition', 'value' => 'condition'],
            ['label' => 'link', 'value' => 'link'],
            ['label' => 'image_link', 'value' => 'image_link'],
            ['label' => 'brand', 'value' => 'brand'],
            ['label' => 'additional_image_link', 'value' => 'additional_image_link'],
            ['label' => 'age_group', 'value' => 'age_group'],
            ['label' => 'color', 'value' => 'color'],
            ['label' => 'gender', 'value' => 'gender'],
            ['label' => 'google_product_category', 'value' => 'google_product_category'],
            ['label' => 'material', 'value' => 'material'],
            ['label' => 'pattern', 'value' => 'pattern'],
            ['label' => 'product_type', 'value' => 'product_type'],
            ['label' => 'sale_price', 'value' => 'sale_price'],
            ['label' => 'sale_price_effective_date', 'value' => 'sale_price_effective_date'],
            ['label' => 'shipping', 'value' => 'shipping'],
            ['label' => 'shipping_weight', 'value' => 'shipping_weight'],
            ['label' => 'size', 'value' => 'size'],
            ['label' => 'custom_option', 'value' => 'custom_option'],
        ];
        return $options;
    }
}
