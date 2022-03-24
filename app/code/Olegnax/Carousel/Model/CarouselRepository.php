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
use Olegnax\Carousel\Api\CarouselRepositoryInterface;
use Olegnax\Carousel\Api\Data\CarouselInterface;
use Olegnax\Carousel\Api\Data\CarouselInterfaceFactory;
use Olegnax\Carousel\Api\Data\CarouselSearchResultsInterfaceFactory;
use Olegnax\Carousel\Model\ResourceModel\Carousel as ResourceCarousel;
use Olegnax\Carousel\Model\ResourceModel\Carousel\CollectionFactory as CarouselCollectionFactory;


class CarouselRepository implements CarouselRepositoryInterface
{

    protected $extensionAttributesJoinProcessor;

    protected $dataCarouselFactory;

    protected $searchResultsFactory;
    protected $dataObjectProcessor;
    protected $dataObjectHelper;
    protected $carouselCollectionFactory;
    protected $extensibleDataObjectConverter;
    protected $resource;
    protected $carouselFactory;
    private $storeManager;
    private $collectionProcessor;


    /**
     * @param ResourceCarousel $resource
     * @param CarouselFactory $carouselFactory
     * @param CarouselInterfaceFactory $dataCarouselFactory
     * @param CarouselCollectionFactory $carouselCollectionFactory
     * @param CarouselSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceCarousel $resource,
        CarouselFactory $carouselFactory,
        CarouselInterfaceFactory $dataCarouselFactory,
        CarouselCollectionFactory $carouselCollectionFactory,
        CarouselSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->carouselFactory = $carouselFactory;
        $this->carouselCollectionFactory = $carouselCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCarouselFactory = $dataCarouselFactory;
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
        CarouselInterface $carousel
    ) {
        /* if (empty($carousel->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $carousel->setStoreId($storeId);
        } */

        $carouselData = $this->extensibleDataObjectConverter->toNestedArray(
            $carousel,
            [],
            CarouselInterface::class
        );

        $carouselModel = $this->carouselFactory->create()->setData($carouselData);

        try {
            $this->resource->save($carouselModel);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the carousel: %1',
                $exception->getMessage()
            ));
        }
        return $carouselModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ) {
        $collection = $this->carouselCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            CarouselInterface::class
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
    public function deleteById($carouselId)
    {
        return $this->delete($this->get($carouselId));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        CarouselInterface $carousel
    ) {
        try {
            $carouselModel = $this->carouselFactory->create();
            $this->resource->load($carouselModel, $carousel->getCarouselId());
            $this->resource->delete($carouselModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Carousel: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($carouselId)
    {
        $carousel = $this->carouselFactory->create();
        $this->resource->load($carousel, $carouselId);
        if (!$carousel->getId()) {
            throw new NoSuchEntityException(__('Carousel with id "%1" does not exist.', $carouselId));
        }
        return $carousel->getDataModel();
    }
}

