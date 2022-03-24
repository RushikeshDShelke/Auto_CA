<?php
namespace Delhivery\Lastmile\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Newspost
 * @package MageArray\News\Block\Adminhtml
 */
class Location extends Container
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_location';
        $this->_blockGroup = 'Delhivery_Lastmile';
        $this->_headerText = __('Delhivery Location');
       $this->_addButtonLabel = __('Download Location');
		
        parent::_construct();
		//$this->buttonList->remove('add');
		
		
		//$this->_removeButton('add');
    }
}
