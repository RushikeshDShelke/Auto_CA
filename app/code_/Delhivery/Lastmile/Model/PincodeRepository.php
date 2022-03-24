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

class PincodeRepository implements \Delhivery\Lastmile\Api\PincodeRepositoryInterface
{
    /**
     * Cached instances
     * 
     * @var array
     */
    protected $instances = [];

    /**
     * Manage Pincode resource model
     * 
     * @var \Delhivery\Lastmile\Model\ResourceModel\Pincode
     */
    protected $resource;

    /**
     * Manage Pincode collection factory
     * 
     * @var \Delhivery\Lastmile\Model\ResourceModel\Pincode\CollectionFactory
     */
    protected $pincodeCollectionFactory;

    /**
     * Manage Pincode interface factory
     * 
     * @var \Delhivery\Lastmile\Api\Data\PincodeInterfaceFactory
     */
    protected $pincodeInterfaceFactory;

    /**
     * Data Object Helper
     * 
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Search result factory
     * 
     * @var \Delhivery\Lastmile\Api\Data\PincodeSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * constructor
     * 
     * @param \Delhivery\Lastmile\Model\ResourceModel\Pincode $resource
     * @param \Delhivery\Lastmile\Model\ResourceModel\Pincode\CollectionFactory $pincodeCollectionFactory
     * @param \Delhivery\Lastmile\Api\Data\PincodeInterfaceFactory $pincodeInterfaceFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Delhivery\Lastmile\Api\Data\PincodeSearchResultInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Delhivery\Lastmile\Model\ResourceModel\Pincode $resource,
        \Delhivery\Lastmile\Model\ResourceModel\Pincode\CollectionFactory $pincodeCollectionFactory,
        \Delhivery\Lastmile\Api\Data\PincodeInterfaceFactory $pincodeInterfaceFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Delhivery\Lastmile\Api\Data\PincodeSearchResultInterfaceFactory $searchResultsFactory
    ) {
        $this->resource                 = $resource;
        $this->pincodeCollectionFactory = $pincodeCollectionFactory;
        $this->pincodeInterfaceFactory  = $pincodeInterfaceFactory;
        $this->dataObjectHelper         = $dataObjectHelper;
        $this->searchResultsFactory     = $searchResultsFactory;
    }

    /**
     * Save Manage Pincode.
     *
     * @param \Delhivery\Lastmile\Api\Data\PincodeInterface $pincode
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Delhivery\Lastmile\Api\Data\PincodeInterface $pincode)
    {
        /** @var \Delhivery\Lastmile\Api\Data\PincodeInterface|\Magento\Framework\Model\AbstractModel $pincode */
        try {
            $this->resource->save($pincode);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save the Manage&#x20;Pincode: %1',
                $exception->getMessage()
            ));
        }
        return $pincode;
    }

    /**
     * Retrieve Manage Pincode.
     *
     * @param int $pincodeId
     * @return \Delhivery\Lastmile\Api\Data\PincodeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($pincodeId)
    {
        if (!isset($this->instances[$pincodeId])) {
            /** @var \Delhivery\Lastmile\Api\Data\PincodeInterface|\Magento\Framework\Model\AbstractModel $pincode */
            $pincode = $this->pincodeInterfaceFactory->create();
            $this->resource->load($pincode, $pincodeId);
            if (!$pincode->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested Manage&#x20;Pincode doesn\'t exist'));
            }
            $this->instances[$pincodeId] = $pincode;
        }
        return $this->instances[$pincodeId];
    }

    /**
     * Retrieve Manage Pincodes matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Delhivery\Lastmile\Api\Data\PincodeSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Delhivery\Lastmile\Api\Data\PincodeSearchResultInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Delhivery\Lastmile\Model\ResourceModel\Pincode\Collection $collection */
        $collection = $this->pincodeCollectionFactory->create();

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
            $field = 'pincode_id';
            $collection->addOrder($field, 'ASC');
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        /** @var \Delhivery\Lastmile\Api\Data\PincodeInterface[] $pincodes */
        $pincodes = [];
        /** @var \Delhivery\Lastmile\Model\Pincode $pincode */
        foreach ($collection as $pincode) {
            /** @var \Delhivery\Lastmile\Api\Data\PincodeInterface $pincodeDataObject */
            $pincodeDataObject = $this->pincodeInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $pincodeDataObject,
                $pincode->getData(),
                \Delhivery\Lastmile\Api\Data\PincodeInterface::class
            );
            $pincodes[] = $pincodeDataObject;
        }
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults->setItems($pincodes);
    }

    /**
     * Delete Manage Pincode.
     *
     * @param \Delhivery\Lastmile\Api\Data\PincodeInterface $pincode
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Delhivery\Lastmile\Api\Data\PincodeInterface $pincode)
    {
        /** @var \Delhivery\Lastmile\Api\Data\PincodeInterface|\Magento\Framework\Model\AbstractModel $pincode */
        $id = $pincode->getId();
        try {
            unset($this->instances[$id]);
            $this->resource->delete($pincode);
        } catch (\Magento\Framework\Exception\ValidatorException $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove Manage&#x20;Pincode %1', $id)
            );
        }
        unset($this->instances[$id]);
        return true;
    }

    /**
     * Delete Manage Pincode by ID.
     *
     * @param int $pincodeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($pincodeId)
    {
        $pincode = $this->getById($pincodeId);
        return $this->delete($pincode);
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Delhivery\Lastmile\Model\ResourceModel\Pincode\Collection $collection
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Delhivery\Lastmile\Model\ResourceModel\Pincode\Collection $collection
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
