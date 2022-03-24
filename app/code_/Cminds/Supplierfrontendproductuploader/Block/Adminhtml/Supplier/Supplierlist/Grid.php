<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Supplier\Supplierlist;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Backend\Block\Widget\Grid\Extended;

class Grid extends Extended
{
    /**
     * Customer collection factory object.
     *
     * @var CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * Object constructor.
     *
     * @param Context           $context                   Context object.
     * @param Data              $backendHelper             Data helper object.
     * @param CollectionFactory $customerCollectionFactory Collection factory object.
     * @param array             $data                      Data array.
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $customerCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );

        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId('supplier_list');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare grid collection.
     *
     * @return Grid
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection()
    {
        $collection = $this->customerCollectionFactory->create()
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->addAttributeToSelect('rejected_notfication_seen')
            ->addAttributeToSelect('supplier_approve')
            ->addAttributeToSelect('supplier_name_new')
            ->addAttributeToSelect('supplier_description_new')
            ->joinAttribute(
                'billing_city',
                'customer_address/city',
                'default_billing',
                null,
                'left'
            )
            ->joinAttribute(
                'billing_telephone',
                'customer_address/telephone',
                'default_billing',
                null,
                'left'
            )
            ->joinAttribute(
                'billing_region',
                'customer_address/region',
                'default_billing',
                null,
                'left'
            )
            ->joinAttribute(
                'billing_country_id',
                'customer_address/country_id',
                'default_billing',
                null,
                'left'
            );

        $collection->addFieldToFilter(
            'group_id',
            [
                'in' => [
                    $this->_scopeConfig->getValue(
                        'configuration/suppliers_group/'
                        . 'supplier_group'
                    ),
                    $this->_scopeConfig->getValue(
                        'configuration/suppliers_group/'
                        . 'suppliert_group_which_can_edit_own_products'
                    ),
                ],
            ],
            'or'
        );
        $this->setCollection($collection);

        parent::_prepareCollection();

        foreach ($collection as $customer) {
            $newSupplierName = $customer->getSupplierNameNew();
            $newSupplierDescription = $customer->getSupplierDescriptionNew();

            $pendingApproval = $newSupplierName || $newSupplierDescription
                ? 1
                : 0;

            $customer->setWaitingForApproval($pendingApproval);
        }

        return $this;
    }

    /**
     * Prepare grid columns.
     *
     * @return Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'width' => '50px',
                'index' => 'entity_id',
            ]
        );
        $this->addColumn(
            'waiting_for_approval',
            [
                'header' => __('Profile Waiting For Approval'),
                'width' => '50px',
                'index' => 'waiting_for_approval',
                'type' => 'options',
                'options' => ['1' => 'Yes', '0' => 'No'],
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'width' => '50px',
                'index' => 'name',
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'width' => '50px',
                'index' => 'email',
            ]
        );
        $this->addColumn(
            'Telephone',
            [
                'header' => __('Telephone'),
                'width' => '50px',
                'index' => 'Telephone',
            ]
        );
        $this->addColumn(
            'billing_country_id',
            [
                'header' => __('Country'),
                'width' => '50px',
                'index' => 'billing_country_id',
            ]
        );
        $this->addColumn(
            'billing_postcode',
            [
                'header' => __('State'),
                'width' => '50px',
                'index' => 'billing_postcode',
            ]
        );
        $this->addColumn(
            'customer_since',
            [
                'header' => __('Since'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'created_at',
                'gmtoffset' => true,
            ]
        );
        $this->addColumn(
            'supplier_approve',
            [
                'header' => __('Is Approved'),
                'align' => 'center',
                'width' => '100',
                'index' => 'supplier_approve',
                'type' => 'options',
                'options' => ['1' => 'Yes', '0' => 'No'],
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
                        'url' => [
                            'base' => 'customer/index/edit',
                            'params' => ['supplier' => true],
                        ],
                        'field' => 'id',
                        'supplier' => true,
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'supplier' => true,
                'is_system' => true,
            ]
        );

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('Excel XML'));

        return parent::_prepareColumns();
    }
}
