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
interface LocationRepositoryInterface
{
    /**
     * Save Manage Location.
     *
     * @param \Delhivery\Lastmile\Api\Data\LocationInterface $location
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Delhivery\Lastmile\Api\Data\LocationInterface $location);

    /**
     * Retrieve Manage Location
     *
     * @param int $locationId
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($locationId);

    /**
     * Retrieve Manage Locations matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Delhivery\Lastmile\Api\Data\LocationSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Manage Location.
     *
     * @param \Delhivery\Lastmile\Api\Data\LocationInterface $location
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Delhivery\Lastmile\Api\Data\LocationInterface $location);

    /**
     * Delete Manage Location by ID.
     *
     * @param int $locationId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($locationId);
}
