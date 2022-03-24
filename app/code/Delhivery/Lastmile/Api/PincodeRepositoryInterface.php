<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Api;

/**
 * @api
 */
interface PincodeRepositoryInterface
{
    /**
     * Save Manage Pincode.
     *
     * @param \Delhivery\Lastmile\Api\Data\PincodeInterface $pincode
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Delhivery\Lastmile\Api\Data\PincodeInterface $pincode);

    /**
     * Retrieve Manage Pincode
     *
     * @param int $pincodeId
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($pincodeId);

    /**
     * Retrieve Manage Pincodes matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Delhivery\Lastmile\Api\Data\PincodeSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Manage Pincode.
     *
     * @param \Delhivery\Lastmile\Api\Data\PincodeInterface $pincode
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Delhivery\Lastmile\Api\Data\PincodeInterface $pincode);

    /**
     * Delete Manage Pincode by ID.
     *
     * @param int $pincodeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($pincodeId);
}
