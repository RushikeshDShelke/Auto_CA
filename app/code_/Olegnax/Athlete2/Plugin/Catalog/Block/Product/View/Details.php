<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Olegnax\Athlete2\Plugin\Catalog\Block\Product\View;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Olegnax\Athlete2\Helper\Helper;

/**
 * Product details block.
 *
 * Holds a group of blocks to show as tabs.
 *
 * @api
 * @since 103.0.1
 */
class Details extends \Magento\Catalog\Block\Product\View\Details
{
    /**
     * @var Helper
     */
    protected $_helper;

    public function __construct(
        Context $context,
        Helper $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get sorted child block names.
     *
     * @param string $groupName
     * @param string $callback
     * @return array
     * @throws LocalizedException
     *
     * @since 103.0.1
     */
    public function getGroupSortedChildNames(string $groupName, string $callback): array
    {
        if (!$this->_helper->isEnabled()) {
            return parent::getGroupSortedChildNames($groupName, $callback);
        }
        $groupChildNames = $this->getGroupChildNames($groupName, $callback);
        $layout = $this->getLayout();

        $childNamesSortOrder = [];

        foreach ($groupChildNames as $childName) {
            $alias = $layout->getElementAlias($childName);
            $sortOrder = (int)$this->getChildData($alias, 'sort_order') ?? 0;

            $childNamesSortOrder[$childName] = $sortOrder;
        }

        asort($childNamesSortOrder, SORT_NUMERIC);
        $childNamesSortOrder = array_keys($childNamesSortOrder);

        return $childNamesSortOrder;
    }
}
