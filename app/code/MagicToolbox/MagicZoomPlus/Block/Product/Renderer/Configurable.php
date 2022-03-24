<?php

namespace MagicToolbox\MagicZoomPlus\Block\Product\Renderer;

/**
 * Swatch renderer block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    /**
     * Action name for ajax request
     */
    const MAGICTOOLBOX_MEDIA_CALLBACK_ACTION = 'magiczoomplus/ajax/media';

    /**
     * @var \MagicToolbox\MagicZoomPlus\Helper\ConfigurableData
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager = null;

    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        //NOTICE: replace via xml ?
        $this->helper = $objectManager->get(\MagicToolbox\MagicZoomPlus\Helper\ConfigurableData::class);
        $this->moduleManager = $objectManager->get(\Magento\Framework\Module\Manager::class);
    }

    /**
     * Returns additional values for js config
     *
     * @return array
     */
    protected function _getAdditionalConfig()
    {
        $config = parent::_getAdditionalConfig();
        $data = $this->helper->getRegistry()->registry('magictoolbox');
        if ($data && $data['current'] != 'product.info.media.image') {
            $standaloneMode = isset($data['standalone-mode']) && $data['standalone-mode'];
            $config['magictoolbox'] = [
                'useOriginalGallery' => $this->helper->useOriginalGallery(),
                'galleryData' => $this->helper->getGalleryData(),
                'standaloneMode' => $standaloneMode
            ];
        }
        return $config;
    }

    /**
     * @return string
     */
    public function getMediaCallback()
    {
        $data = $this->helper->getRegistry()->registry('magictoolbox');
        $url = self::MEDIA_CALLBACK_ACTION;
        if ($data && $data['current'] != 'product.info.media.image') {
            $url = self::MAGICTOOLBOX_MEDIA_CALLBACK_ACTION;
        }
        return $this->getUrl($url, ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->moduleManager->isEnabled('Magento_Swatches')) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Processing block html after rendering
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        if (!$this->moduleManager->isEnabled('Magento_Swatches')) {
            return '';
        }
        return parent::_afterToHtml($html);
    }
}
