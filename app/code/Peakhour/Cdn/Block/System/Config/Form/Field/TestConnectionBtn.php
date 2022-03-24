<?php

namespace Peakhour\Cdn\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestConnectionBtn extends Field
{
    protected function _construct()
    {
        $this->_template = 'Peakhour_Cdn::system/config/form/field/testConnectionBtn.phtml';
        parent::_construct();
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('adminhtml/cdn/testConnection');
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData([
                    'id' => 'peakhour_test_connection_button',
                    'label' => 'Test connection1',
                ]);
        return $button->toHtml();
    }
}
