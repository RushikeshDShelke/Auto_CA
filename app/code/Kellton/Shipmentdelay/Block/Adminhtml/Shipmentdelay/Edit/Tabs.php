<?php
namespace Kellton\Shipmentdelay\Block\Adminhtml\Shipmentdelay\Edit;

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
        $this->setId('shipmentdelay_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Shipmentdelay Information'));
    }
}