<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\SubscribeAtCheckout\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleList\Loader;
use Mageside\SubscribeAtCheckout\Helper\Config as Helper;

class Hint extends Template implements RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'Mageside_SubscribeAtCheckout::system/config/fieldset/hint.phtml';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_metaData;

    /**
     * @var \Magento\Framework\Module\ModuleList\Loader
     */
    protected $_loader;

    /**
     * @var \Mageside\SubscribeAtCheckout\Helper\Config
     */
    protected $_helper;

    /**
     * @param Context $context
     * @param ProductMetadataInterface $productMetaData
     * @param Loader $loader
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetaData,
        Loader $loader,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_metaData = $productMetaData;
        $this->_loader = $loader;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return mixed
     */
    public function render(AbstractElement $element)
    {
        return $this->toHtml();
    }

    /**
     * @return mixed|string
     */
    public function getModuleName()
    {
        return $this->_helper->getConfigModule('module_name');
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVersion()
    {
        $modules = $this->_loader->load();
        $v = "";
        if (isset($modules['Mageside_SubscribeAtCheckout'])) {
            $v = "v" . $modules['Mageside_SubscribeAtCheckout']['setup_version'];
        }
        return $v;
    }

    /**
     * @return mixed
     */
    public function getModulePage()
    {
        if ($this->_helper->getConfigModule('is_marketplace')) {
            return $this->_helper->getConfigModule('marketplace_link');
        }
        return $this->_helper->getConfigModule('module_page_link');
    }

    /**
     * @return mixed
     */
    public function getExtensionsPage()
    {
        if ($this->_helper->getConfigModule('is_marketplace')) {
            return $this->_helper->getConfigModule('marketplace_extensions_link');
        }
        return $this->_helper->getConfigModule('extensions_link');
    }

}
