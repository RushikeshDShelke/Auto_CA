<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Grid;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class AllCategoriesCheckbox extends AbstractRenderer
{
    protected $_currencyHelper;
    protected $_registry;
    protected $_profits;

    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        $html = '<label class="data-grid-checkbox-cell-inner" ';
        $html .= ' for="id_' . $this->escapeHtml($value) . '">';
        $html .= '<input type="checkbox" ';
        $html .= 'name="' . $this->getColumn()->getFieldName() . '" ';
        $html .= 'value="' . $this->escapeHtml($value) . '" ';
        $html .= 'id="id_' . $this->escapeHtml($value) . '" ';
        $html .= 'data-form-part = "customer_form"';
        $html .= 'class="' .
            ($this->getColumn()->getInlineCss()
                ? $this->getColumn()->getInlineCss()
                : 'checkbox'
            ) .
            ' admin__control-checkbox' . '"';
        $html .= '/>';
        $html .= '<label for="id_' . $this->escapeHtml($value) . '"></label>';
        $html .= '</label>';

        return $html;

    }
}
