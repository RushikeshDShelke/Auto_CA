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

namespace Lof\PincodeChecker\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PincodecheckerRepositoryInterface
{


    /**
     * Save pincodechecker
     * @param \Lof\PincodeChecker\Api\Data\PincodecheckerInterface $pincodechecker
     * @return \Lof\PincodeChecker\Api\Data\PincodecheckerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function save(
        \Lof\PincodeChecker\Api\Data\PincodecheckerInterface $pincodechecker
    );

    /**
     * Retrieve pincodechecker
     * @param string $pincodecheckerId
     * @return \Lof\PincodeChecker\Api\Data\PincodecheckerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function getById($pincodecheckerId);

    /**
     * Retrieve pincodechecker matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\PincodeChecker\Api\Data\PincodecheckerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete pincodechecker
     * @param \Lof\PincodeChecker\Api\Data\PincodecheckerInterface $pincodechecker
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function delete(
        \Lof\PincodeChecker\Api\Data\PincodecheckerInterface $pincodechecker
    );

    /**
     * Delete pincodechecker by ID
     * @param string $pincodecheckerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function deleteById($pincodecheckerId);

    /**
     * Check Pincode By Product Sku
     * @param string $sku
     * @param string $pincode
     * @return string $message
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function checkPincode($sku,$pincode);
}
