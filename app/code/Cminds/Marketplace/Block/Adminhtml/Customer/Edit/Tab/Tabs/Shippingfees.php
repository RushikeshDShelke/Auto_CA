<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Tabs;

use Magento\Backend\Block\Widget\Form\Container;

class Shippingfees extends Container
{
    public function _construct()
    {
        parent::_construct();

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_customer_edit_tab_tabs_shippingfees';
        $this->_blockGroup = 'Cminds_Marketplace';
        $this->removeButton('save');
        $this->removeButton('delete');
        $this->removeButton('back');
        $this->removeButton('reset');
    }

    public function getHeaderHtml()
    {
        return '';
    }

    public function getHeaderCssClass()
    {
        return 'icon-head head-cms-page';
    }
}
