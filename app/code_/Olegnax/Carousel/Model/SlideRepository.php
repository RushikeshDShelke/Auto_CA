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

namespace Olegnax\Carousel\Model;

use Exception;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\Carousel\Api\Data\SlideInterface;
use Olegnax\Carousel\Api\Data\SlideInterfaceFactory;
use Olegnax\Carousel\Api\Data\SlideSearchResultsInterfaceFactory;
use Olegnax\Carousel\Api\SlideRepositoryInterface;
use Olegnax\Carousel\Model\ResourceModel\Slide as ResourceSlide;
use Olegnax\Carousel\Model\ResourceModel\Slide\CollectionFactory as SlideCollectionFactory;


class SlideRepository implements SlideRepositoryInterface
{

    protected $extensionAttributesJoinProcessor;

    protected $dataSlideFactory;

    protected $searchResultsFactory;
    protected $dataObjectProcessor;
    protected $dataObjectHelper;
    protected $extensibleDataObjectConverter;
    protected $slideCollectionFactory;
    protected $resource;
    protected $slideFactory;
    private $storeManager;
    private $collectionProcessor;

    /**
     * @param ResourceSlide $resource
     * @param SlideFactory $slideFactory
     * @param SlideInterfaceFactory $dataSlideFactory
     * @param SlideCollectionFactory $slideCollectionFactory
     * @param SlideSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceSlide $resource,
        SlideFactory $slideFactory,
        SlideInterfaceFactory $dataSlideFactory,
        SlideCollectionFactory $slideCollectionFactory,
        SlideSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->slideFactory = $slideFactory;
        $this->slideCollectionFactory = $slideCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSlideFactory = $dataSlideFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        SlideInterface $slide
    ) {
        /* if (empty($slide->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $slide->setStoreId($storeId);
        } */

        $slideData = $this->extensibleDataObjectConverter->toNestedArray(
            $slide,
            [],
            SlideInterface::class
        );

        $slideModel = $this->slideFactory->create()->setData($slideData);

        try {
            $this->resource->save($slideModel);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the slide: %1',
                $exception->getMessage()
            ));
        }
        return $slideModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ) {
        $collection = $this->slideCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            SlideInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($slideId)
    {
        return $this->delete($this->get($slideId));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        SlideInterface $slide
    ) {
        try {
            $slideModel = $this->slideFactory->create();
            $this->resource->load($slideModel, $slide->getSlideId());
            $this->resource->delete($slideModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Slide: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($slideId)
    {
        $slide = $this->slideFactory->create();
        $this->resource->load($slide, $slideId);
        if (!$slide->getId()) {
            throw new NoSuchEntityException(__('Slide with id "%1" does not exist.', $slideId));
        }
        return $slide->getDataModel();
    }
}

