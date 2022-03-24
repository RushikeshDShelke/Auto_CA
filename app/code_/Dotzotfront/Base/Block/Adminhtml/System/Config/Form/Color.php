<?php
/*

Dotzot Shipping

*/

namespace Dotzotfront\Base\Block\Adminhtml\System\Config\Form;

class Color extends \Magento\Backend\Block\Template implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'Dotzotfront_Base::system/config/color.phtml';

    /**
     * @var string
     */
    protected $_pathId;

    /**
     * Render fieldset html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->_pathId = str_replace('__', '_', $element->getId());
        return $this->toHtml();
    }

    /**
     * Receive path id
     *
     * @return string
     */
    public function getPathId()
    {
        return $this->_pathId;
    }
}
