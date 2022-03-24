<?php

namespace Cminds\Supplierfrontendproductuploader\Block\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends Field
{
    const BUTTON_TEMPLATE = 'system/config/button/button.phtml';

    protected function _prepareLayout() // @codingStandardsIgnoreLine
    {
        parent::_prepareLayout();

        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }

        return $this;
    }

    public function render(AbstractElement $element)
    {
        $element
            ->unsScope()
            ->unsCanUseWebsiteValue()
            ->unsCanUseDefaultValue();

        return parent::render($element);
    }

    private function getButtonActionUrl()
    {
        return $this->getUrl(
            'supplier/supplier/setcategoriesavailableforsuppliers'
        );
    }

    protected function _getElementHtml(AbstractElement $element) // @codingStandardsIgnoreLine
    {
        $this->addData(
            [
                'id' => 'set_categories_availability_button',
                'button_label' => __('Run'),
                'onclick' => 'window.location.href="'
                    . $this->getButtonActionUrl()
                    . '"; return false;',
            ]
        );

        return $this->_toHtml();
    }
}
