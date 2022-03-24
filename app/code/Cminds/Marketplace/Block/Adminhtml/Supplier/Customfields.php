<?php

namespace Cminds\Marketplace\Block\Adminhtml\Supplier;

class Customfields extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_supplier_customfields';
        $this->_blockGroup = 'Cminds_Marketplace';
        $this->_headerText = __('Supplier - Custom Profile Fields');
        $this->_addButtonLabel = __('Add New');

        parent::_construct();
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/*/createcustomfield');
    }
}
