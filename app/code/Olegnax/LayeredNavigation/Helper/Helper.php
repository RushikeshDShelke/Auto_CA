<?php

/**
 * Olegnax LayeredNavigation
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\LayeredNavigation\Helper;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Olegnax\Core\Helper\Helper as CoreHelperHelper;

class Helper extends CoreHelperHelper
{

    const CONFIG_MODULE = 'olegnax_layered_navigation';

    protected $_currentEngine = '';

    public function isEnabled()
    {
        return $this->getModuleConfig('general/enable');

    }

    public function isPriceSlider()
    {
        return $this->getModuleConfig('price_slider/price_slider');

    }

    public function isElasticSearchEngine()
    {
        if (!$this->_currentEngine) {
            $this->_currentEngine = $this->getSystemValue(EngineInterface::CONFIG_ENGINE_PATH);
        }
        if ($this->_currentEngine == 'elasticsearch' || $this->_currentEngine == 'elasticsearch5') {
            return true;
        }

        return false;
    }

    public function AjaxEnabled()
    {
        return true;
    }

}
