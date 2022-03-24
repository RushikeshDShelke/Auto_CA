<?php
/**
 * Class Mapping
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookShopIntegration\Block\Adminhtml;

/**
 * Class Mapping
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Mapping extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_facebook_shop_integration_mapping';
        $this->_blockGroup = 'Sparsh_FacebookShopIntegration';
        $this->_headerText = __('Facebbok Shop Attribute Mapping');
        $this->_addButtonLabel = __('Create New Attribute Mapping');
        parent::_construct();
    }
}
