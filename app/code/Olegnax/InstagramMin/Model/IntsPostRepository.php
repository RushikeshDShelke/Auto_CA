<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Model;

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
use Olegnax\InstagramMin\Api\Data\IntsPostInterface;
use Olegnax\InstagramMin\Api\Data\IntsPostInterfaceFactory;
use Olegnax\InstagramMin\Api\Data\IntsPostSearchResultsInterfaceFactory;
use Olegnax\InstagramMin\Api\IntsPostRepositoryInterface;
use Olegnax\InstagramMin\Model\ResourceModel\IntsPost as ResourceIntsPost;
use Olegnax\InstagramMin\Model\ResourceModel\IntsPost\CollectionFactory as IntsPostCollectionFactory;

class IntsPostRepository implements IntsPostRepositoryInterface
{

    protected $extensionAttributesJoinProcessor;

    protected $dataObjectProcessor;

    protected $dataIntsPostFactory;
    protected $intsPostFactory;
    protected $intsPostCollectionFactory;
    protected $searchResultsFactory;
    protected $dataObjectHelper;
    protected $resource;
    protected $extensibleDataObjectConverter;
    private $storeManager;
    private $collectionProcessor;

    /**
     * @param ResourceIntsPost $resource
     * @param IntsPostFactory $intsPostFactory
     * @param IntsPostInterfaceFactory $dataIntsPostFactory
     * @param IntsPostCollectionFactory $intsPostCollectionFactory
     * @param IntsPostSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceIntsPost $resource,
        IntsPostFactory $intsPostFactory,
        IntsPostInterfaceFactory $dataIntsPostFactory,
        IntsPostCollectionFactory $intsPostCollectionFactory,
        IntsPostSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->intsPostFactory = $intsPostFactory;
        $this->intsPostCollectionFactory = $intsPostCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataIntsPostFactory = $dataIntsPostFactory;
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
        IntsPostInterface $intsPost
    ) {
        /* if (empty($intsPost->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $intsPost->setStoreId($storeId);
        } */

        $intsPostData = $this->extensibleDataObjectConverter->toNestedArray(
            $intsPost,
            [],
            IntsPostInterface::class
        );

        $intsPostModel = $this->intsPostFactory->create()->setData($intsPostData);

        try {
            $this->resource->save($intsPostModel);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the intsPost: %1',
                $exception->getMessage()
            ));
        }
        return $intsPostModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        SearchCriteriaInterface $criteria
    ) {
        $collection = $this->intsPostCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            IntsPostInterface::class
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
    public function deleteById($intsPostId)
    {
        return $this->delete($this->get($intsPostId));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        IntsPostInterface $intsPost
    ) {
        try {
            $intsPostModel = $this->intsPostFactory->create();
            $this->resource->load($intsPostModel, $intsPost->getIntspostId());
            $this->resource->delete($intsPostModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the IntsPost: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($intsPostId)
    {
        $intsPost = $this->intsPostFactory->create();
        $this->resource->load($intsPost, $intsPostId);
        if (!$intsPost->getId()) {
            throw new NoSuchEntityException(__('IntsPost with id "%1" does not exist.', $intsPostId));
        }
        return $intsPost->getDataModel();
    }
}

