<?php

namespace Wbcom\PincodeChecker\Block\Adminhtml\Import;


class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * updating the button
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Upload Pincode'));
        $this->_objectId = 'pincode_id';
        $this->_blockGroup = 'Wbcom_PincodeChecker';
        $this->_controller = 'adminhtml_import';
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Import');
    }
}
