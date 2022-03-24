<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Block\LayeredNavigation;

use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Helper\Media;
use Olegnax\LayeredNavigation\Helper\Helper;
use Olegnax\LayeredNavigation\Model\Layer\Filter;

class RenderLayered extends \Magento\Swatches\Block\LayeredNavigation\RenderLayered
{

    protected $_url;
    protected $_helper;
    protected $_filter;
    protected $_template = 'Olegnax_LayeredNavigation::product/layered/renderer.phtml';

    public function __construct(
        Context $context,
        Attribute $eavAttribute,
        AttributeFactory $layerAttribute,
        Data $swatchHelper,
        Media $mediaHelper,
        UrlInterface $url,
        Helper $helper,
        Filter $filter,
        array $data = []
    ) {
        $this->_url = $url;
        $this->_helper = $helper;
        $this->_filter = $filter;
        parent::__construct($context, $eavAttribute, $layerAttribute, $swatchHelper, $mediaHelper, $data);
    }

    protected function getOptionViewData(FilterItem $item, Option $swatchOption)
    {
        $result = parent::getOptionViewData($item, $swatchOption);

        if (!$this->_helper->isEnabled() || $this->isOptionDisabled($item)) {
            return $result;
        }
        $filter = $item->getFilter();
        $itemValue = $item->getValue();
        $value = [is_array($itemValue) ? implode('-', $itemValue) : $itemValue];
        if ($this->_filter->isMultiselect($filter)) {
            $value = array_merge($this->_filter->getFilterValue($filter), $value);
            $value = array_unique($value);
        }

        $query = [
            $filter->getRequestVar() => implode(',', $value),
        ];

        $result['link'] = $this->_url->getUrl(
            '*/*/*',
            [
                '_current' => true,
                '_use_rewrite' => true,
                '_query' => $query
            ]
        );

        return $result;
    }

}
