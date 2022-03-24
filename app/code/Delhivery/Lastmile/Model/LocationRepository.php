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

class LocationRepository implements \Delhivery\Lastmile\Api\LocationRepositoryInterface
{
    /**
     * Cached instances
     * 
     * @var array
     */
    protected $instances = [];

    /**
     * Manage Location resource model
     * 
     * @var \Delhivery\Lastmile\Model\ResourceModel\Location
     */
    protected $resource;

    /**
     * Manage Location collection factory
     * 
     * @var \Delhivery\Lastmile\Model\ResourceModel\Location\CollectionFactory
     */
    protected $locationCollectionFactory;

    /**
     * Manage Location interface factory
     * 
     * @var \Delhivery\Lastmile\Api\Data\LocationInterfaceFactory
     */
    protected $locationInterfaceFactory;

    /**
     * Data Object Helper
     * 
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     * 
     * @var \Delhivery\Lastmile\Api\Data\LocationSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * constructor
     * 
     * @param \Delhivery\Lastmile\Model\ResourceModel\Location $resource
     * @param \Delhivery\Lastmile\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory
     * @param \Delhivery\Lastmile\Api\Data\LocationInterfaceFactory $locationInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Delhivery\Lastmile\Api\Data\LocationSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Delhivery\Lastmile\Model\ResourceModel\Location $resource,
        \Delhivery\Lastmile\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
        \Delhivery\Lastmile\Api\Data\LocationInterfaceFactory $locationInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Delhivery\Lastmile\Api\Data\LocationSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resource                  = $resource;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->locationInterfaceFactory  = $locationInterfaceFactory;
        $this->dataObjectHelper          = $dataObjectHelper;
        $this->searchResultsFactory      = $searchResultsFactory;
    }

    /**
     * Save Manage Location.
     *
     * @param \Delhivery\Lastmile\Api\Data\LocationInterface $location
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Delhivery\Lastmile\Api\Data\LocationInterface $location)
    {
        /** @var \Delhivery\Lastmile\Api\Data\LocationInterface|\Magento\Framework\Model\AbstractModel $location */
        try {
            $this->resource->save($location);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save the Manage&#x20;Location: %1',
                $exception->getMessage()
            ));
        }
        return $location;
    }

    /**
     * Retrieve Manage Location.
     *
     * @param int $locationId
     * @return \Delhivery\Lastmile\Api\Data\LocationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($locationId)
    {
        if (!isset($this->instances[$locationId])) {
            /** @var \Delhivery\Lastmile\Api\Data\LocationInterface|\Magento\Framework\Model\AbstractModel $location */
            $location = $this->locationInterfaceFactory->create();
            $this->resource->load($location, $locationId);
            if (!$location->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested Manage&#x20;Location doesn\'t exist'));
            }
            $this->instances[$locationId] = $location;
        }
        return $this->instances[$locationId];
    }

    /**
     * Retrieve Manage Locations matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Delhivery\Lastmile\Api\Data\LocationSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Delhivery\Lastmile\Api\Data\LocationSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Delhivery\Lastmile\Model\ResourceModel\Location\Collection $collection */
        $collection = $this->locationCollectionFactory->create();

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
            $field = 'location_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Delhivery\Lastmile\Api\Data\LocationInterface[] $locations */
        $locations = [];
        /** @var \Delhivery\Lastmile\Model\Location $location */
        foreach ($collection as $location) {
            /** @var \Delhivery\Lastmile\Api\Data\LocationInterface $locationDataObject */
            $locationDataObject = $this->locationInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $locationDataObject,
                $location->getData(),
                \Delhivery\Lastmile\Api\Data\LocationInterface::class
            );
            $locations[] = $locationDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($locations);
    }

    /**
     * Delete Manage Location.
     *
     * @param \Delhivery\Lastmile\Api\Data\LocationInterface $location
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Delhivery\Lastmile\Api\Data\LocationInterface $location)
    {
        /** @var \Delhivery\Lastmile\Api\Data\LocationInterface|\Magento\Framework\Model\AbstractModel $location */
        $id = $location->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($location);
        } catch (\Magento\Framework\Exception\ValidatorException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Manage&#x20;Location %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Manage Location by ID.
     *
     * @param int $locationId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($locationId)
    {
        $location = $this->getById($locationId);
        return $this->delete($location);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Delhivery\Lastmile\Model\ResourceModel\Location\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Delhivery\Lastmile\Model\ResourceModel\Location\Collection $collection
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
