<?php
namespace Kellton\Advancecontacts\Block\Adminhtml\Advancecontact\Edit;

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
        $this->setId('advancecontact_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Advancecontact Information'));
    }
}