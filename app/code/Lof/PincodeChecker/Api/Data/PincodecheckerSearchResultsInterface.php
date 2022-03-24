<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Lof
 * @package   Lof\PincodeChecker
 * @author    Landofcoder <landofcoder@gmail.com>
 * @copyright 2020 Landofcoder
 * @license   https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\PincodeChecker\Api\Data;

interface PincodecheckerSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get pincodechecker list.
     * @return \Lof\PincodeChecker\Api\Data\PincodecheckerInterface[]
     */
    
    public function getItems();

    /**
     * Set pincode list.
     * @param \Lof\PincodeChecker\Api\Data\PincodecheckerInterface[] $items
     * @return $this
     */
    
    public function setItems(array $items);
}
