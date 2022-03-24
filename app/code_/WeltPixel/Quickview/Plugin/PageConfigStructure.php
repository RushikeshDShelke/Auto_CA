<?php
namespace WeltPixel\Quickview\Plugin;

class PageConfigStructure {

    /**
     * @var \WeltPixel\Quickview\Helper\Data
     */
    protected $_helper;

    /**
     * PageConfigStructure constructor.
     * @param \WeltPixel\Quickview\Helper\Data $helper
     */
    public function __construct(
        \WeltPixel\Quickview\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Modify the hardcoded breakpoint for styles-menu.css
     * @param \Magento\Framework\View\Page\Config\Structure $subject
     * @param string $name
     * @param array $attributes
     * @return $this
     */
    public function beforeAddAssets(
        \Magento\Framework\View\Page\Config\Structure
        $subject, $name, $attributes
    )
    {
        $isQuickviewEnabled = $this->_helper->isEnabled();

        if (!$isQuickviewEnabled) {
            switch ($name) {
                case 'WeltPixel_Quickview::css/magnific-popup.css':
                    $name = '';
                    break;
            }
        }

        return [$name, $attributes];
    }
}