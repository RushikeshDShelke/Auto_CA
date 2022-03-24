<?php
/**
 * Class FbProductCondition
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookShopIntegration\Model\Attribute\Source;

/**
 * Class FbProductCondition
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class FbProductCondition extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('New'), 'value' => 'new'],
                ['label' => __('Refurbished'), 'value' => 'refurbished'],
                ['label' => __('Used (Fair)'), 'value' => 'used_fair'],
                ['label' => __('Used (Good)'), 'value' => 'used_good'],
                ['label' => __('Used (Like New)'), 'value' => 'used_like_new'],
            ];
        }
        return $this->_options;
    }
}
