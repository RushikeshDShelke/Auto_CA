<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Block\Adminhtml\System\Config;

class Image extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $addonHtml = '<img src="%s" id="' . $element->getHtmlId() . '" height="44" width="44" 
            class="small-image-preview v-middle" />';

        $id = explode('_', $element->getHtmlId());
        $imgUrl = $this->getViewFileUrl('Dotzotfront_ShippingTracking::images/icons/' . end($id) . '.png');
        
        return sprintf($addonHtml, $imgUrl);
    }
}