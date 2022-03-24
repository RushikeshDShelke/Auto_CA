<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Tabs;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

class Assignedcategories extends Extended
{
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

        $this->collectionFactory = $collectionFactory;
    }

    public function _construct()
    {
        $this->setUseAjax(true);
        $this->setId('supplier_assigned_categories');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');

        parent::_construct();
    }

    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('level', ['neq' => 0])
            ->addFieldToFilter('level', ['neq' => 1])
            ->addAttributeToFilter(
                'available_for_supplier',
                [
                    ['eq' => 1]
                ]
            );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('Uncheck To Restrict'),
                'header_css_class' => 'a-center',
                'renderer' => '\Cminds\Marketplace\Block\Adminhtml'
                    . '\Customer\Edit\Tab\Grid\CategoriesCheckbox',
                'field_name' => 'category_ids[]',
                'align' => 'center',
                'index' => 'entity_id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Category Name'),
                'index' => 'name',
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl(
            'marketplace/customer/assignedcategories',
            ['_current' => true]
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    public function getMultipleRows($item)
    {
        return false;
    }
}
