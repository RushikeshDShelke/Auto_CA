<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Service;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category;

final class CategoryService
{
    private $categoryCollection;
    private $categoryResource;

    public function __construct(
        CollectionFactory $categoryCollection,
        Category $categoryResource
    ) {
        $this->categoryCollection = $categoryCollection;
        $this->categoryResource = $categoryResource;
    }

    public function setCategoriesAvailability()
    {
        /** @var Collection $categories */
        $categories = $this->categoryCollection->create()
            ->addAttributeToSelect('*');

        foreach ($categories as $category) {
            $category
                ->setData('available_for_supplier', 1);

            $this->categoryResource->saveAttribute($category, 'available_for_supplier');
        }
    }
}
