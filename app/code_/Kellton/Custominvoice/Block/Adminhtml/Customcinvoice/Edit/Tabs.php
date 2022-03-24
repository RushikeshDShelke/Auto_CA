<?php
namespace Kellton\Custominvoice\Block\Adminhtml\Customcinvoice\Edit;

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
        $this->setId('customcinvoice_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Customcinvoice Information'));
    }
}