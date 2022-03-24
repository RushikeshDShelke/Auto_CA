<?php

namespace MagicToolbox\MagicZoomPlus\Block\Html;

use Magento\Framework\View\Element\Template\Context;
use MagicToolbox\MagicZoomPlus\Helper\Data;

/**
 * Head block
 */
class Head extends \Magento\Framework\View\Element\Template
{
    /**
     * Helper
     *
     * @var \MagicToolbox\MagicZoomPlus\Helper\Data
     */
    public $magicToolboxHelper = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Current page
     *
     * @var string
     */
    protected $currentPage = 'unknown';

    /**
     * Block visibility
     *
     * @var bool
     */
    protected $visibility = false;

    /**
     * @param Context $context
     * @param \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MagicToolbox\MagicZoomPlus\Helper\Data $magicToolboxHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->magicToolboxHelper = $magicToolboxHelper;
        $this->coreRegistry = $registry;
        $this->currentPage = isset($data['page']) ? $data['page'] : 'unknown';
        parent::__construct($context, $data);
    }

    /**
     * Preparing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $tool = $this->magicToolboxHelper->getToolObj();
        if ($tool->params->profileExists($this->currentPage)) {
            $this->visibility = !$tool->params->checkValue('enable-effect', 'No', $this->currentPage);
        }
        $this->visibility = $this->visibility || $tool->params->checkValue('include-headers-on-all-pages', 'Yes', 'default');

        return parent::_prepareLayout();
    }

    /**
     * Get page type
     *
     * @return string
     */
    public function getPageType()
    {
        return $this->currentPage;
    }

    /**
     * Checks whether to display Magic Scroll headers
     *
     * @return bool
     */
    public function doDisplayMagicScrollHeaders()
    {
        $doDisplay = false;
        if ($this->currentPage == 'product') {
            $data = $this->coreRegistry->registry('magictoolbox');
            if ($data) {
                $doDisplay = ($data['magicscroll'] == 'magiczoomplus');
            }
            $layout = $this->getLayout();
            if ($layout) {
                $magicscrollHeadBlock = $layout->getBlock('magicscroll-header');
                if ($magicscrollHeadBlock) {
                    $doDisplay = $doDisplay && !$magicscrollHeadBlock->isVisibile();
                }
            }
        }
        return $doDisplay;
    }

    /**
     * Check block visibility
     *
     * @return bool
     */
    public function isVisibile()
    {
        return $this->visibility;
    }
}
