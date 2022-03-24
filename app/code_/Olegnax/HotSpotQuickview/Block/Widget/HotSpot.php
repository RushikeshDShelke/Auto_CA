<?php
/**
 * @author      Olegnax
 * @package     Olegnax_HotSpotQuickview
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\HotSpotQuickview\Block\Widget;

use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Widget\Block\BlockInterface;
use Olegnax\HotSpotQuickview\Helper\Helper;
use RuntimeException;

class HotSpot extends Template implements BlockInterface
{
    protected $_template = "widget/hotspot.phtml";
    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;
    /**
     * @var AbstractResource|null
     */
    protected $_entityResource;
    /**
     * @var string
     */
    protected $_href;
    /**
     * @var UrlInterface
     */
    protected $urlInterface;
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * HotSpot constructor.
     * @param Context $context
     * @param UrlInterface $urlInterface
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlInterface $urlInterface,
        Helper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->urlInterface = $urlInterface;
        $this->helper = $helper;
    }

    /**
     * Prepare url using passed id path and return it
     *
     * @return string|false if path was not found in url rewrites.
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws RuntimeException
     */
    public function getHref()
    {
        if ($this->_href === null) {
            if (!$this->getData('id_path')) {
                return;
            }
            $rewriteData = $this->parseIdPath($this->getData('id_path'));

            $href = $this->urlInterface->getUrl('ox_quickview/catalog_product/view', ['id' => $rewriteData[1]]);

            $this->_href = $href;
        }
        return $this->_href;
    }

    /**
     * Parse id_path
     *
     * @param string $idPath
     * @return array
     * @throws RuntimeException
     */
    protected function parseIdPath($idPath)
    {
        $rewriteData = explode('/', $idPath);

        if (!isset($rewriteData[0]) || !isset($rewriteData[1])) {
            throw new RuntimeException('Wrong id_path structure.');
        }
        return $rewriteData;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * @return string
     */
    public function getWidgetId()
    {
        return 'ox_hotspotquickview_' . substr(md5(microtime()), -5);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->setData('position_left', abs((float)$this->getData('position_left')));
        $this->setData('position_top', abs((float)$this->getData('position_top')));
		$this->setData('border-radius', abs((int)$this->getData('border-radius')));
		$this->setData('border', abs((int)$this->getData('border')));
        $this->setData('rel_nofollow', (bool)$this->getData('rel_nofollow'));
        parent::_construct();
    }
}
