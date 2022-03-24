<?php

namespace Wbcom\PincodeChecker\Block\Adminhtml\Import\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * Intialize Construct
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pincode_import_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Pincode'));
    }

    /**
     * @return WidgetTabs
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'pincode_create_tabs',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    'Wbcom\PincodeChecker\Block\Adminhtml\Import\Edit\Tab\Info'
                )->toHtml(),
                'active' => true
            ]
        );
        return parent::_beforeToHtml();
    }
}
