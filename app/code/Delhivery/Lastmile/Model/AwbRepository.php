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
namespace Delhivery\Lastmile\Model;

class AwbRepository implements \Delhivery\Lastmile\Api\AwbRepositoryInterface
{
    /**
     * Cached instances
     * 
     * @var array
     */
    protected $instances = [];

    /**
     * Manage AWB resource model
     * 
     * @var \Delhivery\Lastmile\Model\ResourceModel\Awb
     */
    protected $resource;

    /**
     * Manage AWB collection factory
     * 
     * @var \Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory
     */
    protected $awbCollectionFactory;

    /**
     * Manage AWB interface factory
     * 
     * @var \Delhivery\Lastmile\Api\Data\AwbInterfaceFactory
     */
    protected $awbInterfaceFactory;

    /**
     * Data Object Helper
     * 
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     * 
     * @var \Delhivery\Lastmile\Api\Data\AwbSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * constructor
     * 
     * @param \Delhivery\Lastmile\Model\ResourceModel\Awb $resource
     * @param \Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $awbCollectionFactory
     * @param \Delhivery\Lastmile\Api\Data\AwbInterfaceFactory $awbInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Delhivery\Lastmile\Api\Data\AwbSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Delhivery\Lastmile\Model\ResourceModel\Awb $resource,
        \Delhivery\Lastmile\Model\ResourceModel\Awb\CollectionFactory $awbCollectionFactory,
        \Delhivery\Lastmile\Api\Data\AwbInterfaceFactory $awbInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Delhivery\Lastmile\Api\Data\AwbSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resource             = $resource;
        $this->awbCollectionFactory = $awbCollectionFactory;
        $this->awbInterfaceFactory  = $awbInterfaceFactory;
        $this->dataObjectHelper     = $dataObjectHelper;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Save Manage AWB.
     *
     * @param \Delhivery\Lastmile\Api\Data\AwbInterface $awb
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Delhivery\Lastmile\Api\Data\AwbInterface $awb)
    {
        /** @var \Delhivery\Lastmile\Api\Data\AwbInterface|\Magento\Framework\Model\AbstractModel $awb */
        try {
            $this->resource->save($awb);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save the Manage&#x20;AWB: %1',
                $exception->getMessage()
            ));
        }
        return $awb;
    }

    /**
     * Retrieve Manage AWB.
     *
     * @param int $awbId
     * @return \Delhivery\Lastmile\Api\Data\AwbInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($awbId)
    {
        if (!isset($this->instances[$awbId])) {
            /** @var \Delhivery\Lastmile\Api\Data\AwbInterface|\Magento\Framework\Model\AbstractModel $awb */
            $awb = $this->awbInterfaceFactory->create();
            $this->resource->load($awb, $awbId);
            if (!$awb->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested Manage&#x20;AWB doesn\'t exist'));
            }
            $this->instances[$awbId] = $awb;
        }
        return $this->instances[$awbId];
    }

    /**
     * Retrieve Manage AWBs matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Delhivery\Lastmile\Api\Data\AwbSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Delhivery\Lastmile\Api\Data\AwbSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Delhivery\Lastmile\Model\ResourceModel\Awb\Collection $collection */
        $collection = $this->awbCollectionFactory->create();

        //Add filters from root filter group to the collection
        /** @var \Magento\Framework\Api\Search\FilterGroup $group */
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }
        $sortOrders = $searchCriteria->getSortOrders();
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        if ($sortOrders) {
            foreach ($searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                $collection->addOrder(
                    $field,
                    ($sortOrder->getDirection() == \Magento\Framework\Api\SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        } else {
            // set a default sorting order since this method is used constantly in many
            // different blocks
            $field = 'awb_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Delhivery\Lastmile\Api\Data\AwbInterface[] $awbs */
        $awbs = [];
        /** @var \Delhivery\Lastmile\Model\Awb $awb */
        foreach ($collection as $awb) {
            /** @var \Delhivery\Lastmile\Api\Data\AwbInterface $awbDataObject */
            $awbDataObject = $this->awbInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $awbDataObject,
                $awb->getData(),
                \Delhivery\Lastmile\Api\Data\AwbInterface::class
            );
            $awbs[] = $awbDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($awbs);
    }

    /**
     * Delete Manage AWB.
     *
     * @param \Delhivery\Lastmile\Api\Data\AwbInterface $awb
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Delhivery\Lastmile\Api\Data\AwbInterface $awb)
    {
        /** @var \Delhivery\Lastmile\Api\Data\AwbInterface|\Magento\Framework\Model\AbstractModel $awb */
        $id = $awb->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($awb);
        } catch (\Magento\Framework\Exception\ValidatorException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Manage&#x20;AWB %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Manage AWB by ID.
     *
     * @param int $awbId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($awbId)
    {
        $awb = $this->getById($awbId);
        return $this->delete($awb);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Delhivery\Lastmile\Model\ResourceModel\Awb\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Delhivery\Lastmile\Model\ResourceModel\Awb\Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
        return $this;
    }
}
