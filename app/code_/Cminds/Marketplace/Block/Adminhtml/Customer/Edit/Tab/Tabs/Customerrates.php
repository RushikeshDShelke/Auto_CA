<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Tabs;

use Cminds\Marketplace\Model\Rating;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;

class Customerrates extends Extended
{
    protected $_rating;
    protected $_registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        Rating $rating,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

        $this->_rating = $rating;
        $this->_registry = $registry;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('customer_rates_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $supplier_id = $this->_registry->registry('current_customer')->getId();

        $collection = $this->_rating
            ->getCollection()
            ->addFieldtoFilter(
                'supplier_id',
                $supplier_id
            );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'customer_id',
            [
                'header' => __('Customer ID'),
                'index' => 'customer_id',
            ]
        );
        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer Name'),
                'index' => 'customer_id',
                'renderer' => '\Cminds\Marketplace\Block\Adminhtml'
                    . '\Customer\Edit\Tab\Tabs\Renderer\Customer',
            ]
        );
        $this->addColumn(
            'rate',
            [
                'header' => __('Rate'),
                'index' => 'rate',
            ]
        );
        $this->addColumn(
            'created_on',
            [
                'header' => __('Voted On'),
                'index' => 'created_on',
                'type' => 'datetime',
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'id',
                'filter' => false,
                'sortable' => false,
                'renderer' => '\Cminds\Marketplace\Block\Adminhtml'
                    . '\Customer\Edit\Tab\Tabs\Renderer\Action'
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl(
            'marketplace/customer/customerrates',
            ['_current' => true]
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }
}
