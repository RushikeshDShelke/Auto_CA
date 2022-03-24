<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Catalog;

class Products extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_catalog_products';
        $this->_blockGroup = 'Cminds_Supplierfrontendproductuploader';
        $this->_headerText = __('Supplier Product List');

        parent::_construct();

        $this->removeButton('add');
    }
}
