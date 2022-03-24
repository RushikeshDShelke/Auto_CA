<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Customer\Account\Navigation;

class Portal extends \Magento\Framework\View\Element\Html\Link\Current
{
    protected $_defaultPath;
    protected $_helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface      $defaultPath
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Cminds\Supplierfrontendproductuploader\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath);
        $this->_defaultPath = $defaultPath;
        $this->_helper = $helper;
    }


    protected function _toHtml()
    {
        if (!$this->_helper->canAccess()) {
            return '';
        }

        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        $highlight = '';

        if ($this->getIsHighlighted()) {
            $highlight = ' current';
        }

        if ($this->isCurrent()) {
            $html = '<li class="nav item current">';
            $html .= '<strong>'
                . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getVendorLabel()))
                . '</strong>';
            $html .= '</li>';
        } else {
            $html = '<li class="nav item' . $highlight . '"><a href="' . $this->escapeHtml($this->getHref()) . '"';
            $html .= $this->getTitle()
                ? ' title="' . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getTitle())) . '"'
                : '';
            $html .= $this->getAttributesHtml() . '>';

            if ($this->getIsHighlighted()) {
                $html .= '<strong>';
            }

            $html .= $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getVendorLabel()));

            if ($this->getIsHighlighted()) {
                $html .= '</strong>';
            }

            $html .= '</a></li>';
        }

        return $html;
    }

    protected function getVendorLabel()
    {
        $vandorLabel = $this->_helper
            ->getStoreConfig('configuration/presentation/link_label');

        if (isset($vandorLabel) && $vandorLabel) {
            return $vandorLabel;
        } else {
            return $this->getLabel();
        }
    }
}
