<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Supplier;

use Magento\Backend\Block\Widget\Grid\Container;

class Supplierlist extends Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_supplier_supplierlist';
        $this->_blockGroup = 'Cminds_Supplierfrontendproductuploader';

        $this->_addButtonLabel = __('Add New Supplier');
        $this->_headerText = __('Manage Suppliers');

        parent::_construct();
    }

    /**
     * Return create new supplier url.
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl(
            'customer/index/new',
            ['supplier' => true]
        );
    }
}
