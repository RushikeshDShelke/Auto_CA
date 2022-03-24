<?php
namespace Kellton\Ewaybillno\Block\Adminhtml\Ewaybillno\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('ewaybillno_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Ewaybillno Information'));
    }
}