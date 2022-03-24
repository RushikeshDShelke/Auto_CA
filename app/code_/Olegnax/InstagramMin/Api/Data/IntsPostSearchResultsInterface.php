<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface IntsPostSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get IntsPost list.
     * @return IntsPostInterface[]
     */
    public function getItems();

    /**
     * Set owner list.
     * @param IntsPostInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

