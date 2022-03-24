<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Template\Context;

class State extends \Magento\LayeredNavigation\Block\Navigation\State
{

    protected $_template = 'Olegnax_LayeredNavigation::layer/state.phtml';
    protected $_catalogLayer;

    public function __construct(
        Context $context,
        Resolver $layerResolver,
        array $data = []
    ) {
        $this->_catalogLayer = $layerResolver->get();
        parent::__construct($context, $layerResolver, $data);
    }

}
