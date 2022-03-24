<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Plugin\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\Exception\LocalizedException;

class FilterRenderer
{

    protected $filterPrice;
    protected $currencySymbol;
    protected $storeManager;

    /**
     * @param \Magento\LayeredNavigation\Block\Navigation\FilterRenderer $subject
     * @param FilterInterface $filter
     * @return array
     * @throws LocalizedException
     */
    public function beforeRender($subject, $filter)
    {
        $attribute_id = 0;
        if ('cat' != $filter->getRequestVar()) {
            $attribute_id = $filter->getAttributeModel()->getAttributeId();
        }
        $subject->setData('attribute_id', $attribute_id);
        $subject->assign('filter', $filter);

        return [$filter];
    }

}
