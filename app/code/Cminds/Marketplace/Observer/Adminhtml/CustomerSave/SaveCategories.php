<?php

namespace Cminds\Marketplace\Observer\Adminhtml\CustomerSave;

use Cminds\Marketplace\Model\ResourceModel\Categories\CollectionFactory;
use Cminds\Marketplace\Model\CategoriesFactory;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveCategories implements ObserverInterface
{
    private $request;
    private $restrictedCategoryCollectionFactory;
    private $restrictedCategoryFactory;

    public function __construct(
        Context $context,
        CollectionFactory $restrictedCategoryCollectionFactory,
        CategoriesFactory $restrictedCategoryFactory
    ) {
        $this->request = $context->getRequest();
        $this->restrictedCategoryCollectionFactory = $restrictedCategoryCollectionFactory;
        $this->restrictedCategoryFactory = $restrictedCategoryFactory;
    }

    public function execute(Observer $observer)
    {
        $postData = (array)$this->request->getPost();

        if (isset($postData['category_ids']) === false
            || is_array($postData['category_ids']) === false
        ) {
            return $this;
        }

        /** @var Customer $customer */
        $customer = $observer->getCustomer();
        $supplierId = $customer->getId();

        $restrictedCategoryIds = array_keys($postData['category_ids'], 0);

        $currentlyRestrictedCollection = $this->restrictedCategoryCollectionFactory
            ->create();
        $currentlyRestrictedCollection
            ->getSelect()
            ->where('supplier_id = ?', $supplierId);

        foreach ($currentlyRestrictedCollection as $index => $restrictedCategory) {
            $categoryId = (int)$restrictedCategory->getCategoryId();
            if (in_array($categoryId, $restrictedCategoryIds, true)) {
                $restrictedCategoryIndex = array_search(
                    $categoryId,
                    $restrictedCategoryIds,
                    true
                );
                unset($restrictedCategoryIds[$restrictedCategoryIndex]);
            } else {
                $restrictedCategory
                    ->delete()
                    ->save();
            }
        }

        if (count($restrictedCategoryIds) === 0) {
            return $this;
        }

        foreach ($restrictedCategoryIds as $restrictedCategoryId) {
            $this->restrictedCategoryFactory->create()
                ->setSupplierId($supplierId)
                ->setCategoryId($restrictedCategoryId)
                ->save();
        }

        return $this;
    }
}
