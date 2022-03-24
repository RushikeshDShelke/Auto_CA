<?php

namespace Cminds\Marketplace\Block\Adminhtml\Supplier\Customfields;

use Cminds\Marketplace\Model\Fields;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;

class Grid extends Extended
{
    protected $_productFactory;
    protected $_queueFactory;
    protected $_fields;

    public function __construct(
        Context $context,
        Data $backendHelper,
        Fields $fields,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

        $this->_fields = $fields;
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId('custom_fields');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_fields->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'width' => '50px',
                'index' => 'id',
                'type' => 'number',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
            ]
        );
        $this->addColumn(
            'label',
            [
                'header' => __('Label'),
                'index' => 'label',
            ]
        );
        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
            ]
        );
        $this->addColumn(
            'is_required',
            [
                'header' => __('Required'),
                'index' => 'is_required',
                'type' => 'options',
                'options' => [
                    0 => 'No',
                    1 => 'Yes',
                ],
            ]
        );
        $this->addColumn(
            'must_be_approved',
            [
                'header' => __('Must be Approved'),
                'index' => 'must_be_approved',
                'type' => 'options',
                'options' => [
                    0 => 'No',
                    1 => 'Yes',
                ],
            ]
        );
        $this->addColumn(
            'is_system',
            [
                'header' => __('Is System'),
                'index' => 'is_system',
                'type' => 'options',
                'options' => [
                    0 => 'No',
                    1 => 'Yes',
                ],
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
            ]
        );
        $this->addColumn(
            'visible_on_create_form',
            [
                'header' => __('Visible on create form'),
                'index' => 'visible_on_create_form',
                'type' => 'options',
                'options' => [
                    0 => __('Not visible'),
                    1 => __('Visible'),
                ],
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => ['base' => '*/suppliers/editCustomField'],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ]
        );

        return parent::_prepareColumns();
    }
}
