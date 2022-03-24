<?php
namespace Kellton\Smsbulk\Block\Adminhtml\Smsbulk\Edit;

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
        $this->setId('smsbulk_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Smsbulk Information'));
    }
}