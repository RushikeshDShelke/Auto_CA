<?php
/*

Dotzot Shipping


*/

namespace Dotzotfront\Base\Block\Adminhtml\System\Config\Form;

class Editor extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Cms\Model\Wysiwyg\Config       $wysiwygConfig
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $config = $this->_wysiwygConfig->getConfig();
           $element->setConfig($config);

        return $element->getElementHtml() . $this->_getJs();
    }

    /**
     * Receive js html
     *
     * @return string
     */
    protected function _getJs()
    {
        return "<script type='text/javascript'>
                    require(['Magento_Variable/variables']);
                </script>";
    }
}