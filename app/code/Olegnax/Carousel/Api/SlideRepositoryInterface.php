<?php declare(strict_types=1);
/**
 * Copyright (c) 2021
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Olegnax\Carousel\Api;


use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Olegnax\Carousel\Api\Data\SlideInterface;
use Olegnax\Carousel\Api\Data\SlideSearchResultsInterface;

interface SlideRepositoryInterface
{

    /**
     * Save Slide
     * @param SlideInterface $slide
     * @return SlideInterface
     * @throws LocalizedException
     */
    public function save(
        SlideInterface $slide
    );

    /**
     * Retrieve Slide
     * @param string $slideId
     * @return SlideInterface
     * @throws LocalizedException
     */
    public function get($slideId);

    /**
     * Retrieve Slide matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return SlideSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Slide
     * @param SlideInterface $slide
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        SlideInterface $slide
    );

    /**
     * Delete Slide by ID
     * @param string $slideId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($slideId);
}

