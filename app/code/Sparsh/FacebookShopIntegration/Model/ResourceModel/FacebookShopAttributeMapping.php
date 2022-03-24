<?php
/**
 * Class FacebookShopAttributeMapping
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookShopIntegration\Model\ResourceModel;

/**
 * Class FacebookShopAttributeMapping
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class FacebookShopAttributeMapping extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize ResourceModel
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sparsh_facebook_shop_attribute_mapping', 'entity_id');
    }
}
