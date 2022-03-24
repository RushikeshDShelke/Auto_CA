<?php
namespace Kellton\Docket\Block\Adminhtml\Docket\Edit;

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
        $this->setId('docket_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Docket Information'));
    }
}