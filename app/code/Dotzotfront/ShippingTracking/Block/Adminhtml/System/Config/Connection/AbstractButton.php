<?php
/**
 * Dotzot Shipping
 */

namespace Dotzotfront\ShippingTracking\Block\Adminhtml\System\Config\Connection;

class AbstractButton extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Template path
     */
    private $template = "system/config/button.phtml";

    /**
     * @return $this|\Magento\Config\Block\System\Config\Form\Field
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate($this->template);
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        $html = parent::render($element);
        return $html;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'button_label' => __('Test Connection'),
                'html_id'      => $element->getHtmlId(),
                'onclick'      => $this->getOnclick($element->getHtmlId())
            ]
        );

        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getOnclick($htmlId = null)
    {
        return 'return false;';
    }
}