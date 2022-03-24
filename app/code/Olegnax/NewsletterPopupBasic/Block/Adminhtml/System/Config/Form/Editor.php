<?php /** @noinspection PhpDeprecationInspection */

namespace Olegnax\NewsletterPopupBasic\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Registry;

class Editor extends Field
{

    /**
     * @var  Registry
     */
    protected $_coreRegistry;
    /**
     * @var WysiwygConfig
     */
    protected $_wysiwygConfig;

    /**
     * @param Context $context
     * @param WysiwygConfig $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        WysiwygConfig $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @noinspection PhpUndefinedMethodInspection
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setWysiwyg(true);
        $config = $this->_wysiwygConfig->getConfig($element);
        $element->setConfig($config);
        return parent::_getElementHtml($element);
    }

}
