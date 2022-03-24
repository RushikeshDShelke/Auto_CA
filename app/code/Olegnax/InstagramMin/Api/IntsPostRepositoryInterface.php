<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Olegnax\InstagramMin\Api\Data\IntsPostInterface;
use Olegnax\InstagramMin\Api\Data\IntsPostSearchResultsInterface;

interface IntsPostRepositoryInterface
{

    /**
     * Save IntsPost
     * @param IntsPostInterface $intsPost
     * @return IntsPostInterface
     * @throws LocalizedException
     */
    public function save(
        IntsPostInterface $intsPost
    );

    /**
     * Retrieve IntsPost
     * @param string $intspostId
     * @return IntsPostInterface
     * @throws LocalizedException
     */
    public function get($intspostId);

    /**
     * Retrieve IntsPost matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return IntsPostSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete IntsPost
     * @param IntsPostInterface $intsPost
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        IntsPostInterface $intsPost
    );

    /**
     * Delete IntsPost by ID
     * @param string $intspostId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($intspostId);
}

