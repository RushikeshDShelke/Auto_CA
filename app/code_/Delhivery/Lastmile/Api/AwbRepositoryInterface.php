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
interface AwbRepositoryInterface
{
    /**
     * Save Manage AWB.
     *
     * @param \Delhivery\Lastmile\Api\Data\AwbInterface $awb
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Delhivery\Lastmile\Api\Data\AwbInterface $awb);

    /**
     * Retrieve Manage AWB
     *
     * @param int $awbId
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($awbId);

    /**
     * Retrieve Manage AWBs matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Delhivery\Lastmile\Api\Data\AwbSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Manage AWB.
     *
     * @param \Delhivery\Lastmile\Api\Data\AwbInterface $awb
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Delhivery\Lastmile\Api\Data\AwbInterface $awb);

    /**
     * Delete Manage AWB by ID.
     *
     * @param int $awbId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($awbId);
}
