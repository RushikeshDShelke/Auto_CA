<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Catalog\Products;


use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as SetCollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\WebsiteFactory;

class Grid extends Extended
{
    /**
     * Product factory object.
     *
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * Module manager object.
     *
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * Product type object.
     *
     * @var ProductType
     */
    private $productType;

    /**
     * Attribute set collection factory object.
     *
     * @var SetCollectionFactory
     */
    private $setCollectionFactory;

    /**
     * Product visibility object.
     *
     * @var ProductVisibility
     */
    private $productVisibility;

    /**
     * Product status object.
     *
     * @var ProductStatus
     */
    private $productStatus;

    /**
     * Website factory object.
     *
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * Object constructor.
     *
     * @param Context              $context Context object.
     * @param BackendHelper        $backendHelper Backend helper object.
     * @param ProductFactory       $productFactory Product factory object.
     * @param ModuleManager        $moduleManager Module manager object.
     * @param ProductType          $productType Product type object.
     * @param SetCollectionFactory $setCollectionFactory Set collection factory object.
     * @param ProductVisibility    $productVisibility Product visibility object.
     * @param ProductStatus        $productStatus Product status object.
     * @param WebsiteFactory       $websiteFactory Website factory object.
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        ProductFactory $productFactory,
        ModuleManager $moduleManager,
        ProductType $productType,
        SetCollectionFactory $setCollectionFactory,
        ProductVisibility $productVisibility,
        ProductStatus $productStatus,
        WebsiteFactory $websiteFactory
    ) {
        parent::__construct(
            $context,
            $backendHelper
        );

        $this->productFactory = $productFactory;
        $this->moduleManager = $moduleManager;
        $this->productType = $productType;
        $this->setCollectionFactory = $setCollectionFactory;
        $this->productVisibility = $productVisibility;
        $this->productStatus = $productStatus;
        $this->websiteFactory = $websiteFactory;
    }

    /**
     * Prepare object.
     *
     * @return void
     */
    protected function _construct() // @codingStandardsIgnoreLine
    {
        parent::_construct();

        $this->setId('supplier_products_list');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
    }

    /**
     * Retrieve current request store object.
     *
     * @return StoreInterface
     */
    private function getStore()
    {
        $storeId = (int)$this->_request->getParam('store', 0);

        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Apply sorting and filtering to collection.
     *
     * @return Extended
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareCollection() // @codingStandardsIgnoreLine
    {
        $store = $this->getStore();
        $collection = $this->productFactory->create()
            ->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->setStore($store);

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }

        if ($store->getId()) {
            $collection
                ->addStoreFilter($store)
                ->joinAttribute(
                    'name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    Store::DEFAULT_STORE_ID
                )
                ->joinAttribute(
                    'custom_name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                )
                ->joinAttribute(
                    'status',
                    'catalog_product/status',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                )
                ->joinAttribute(
                    'visibility',
                    'catalog_product/visibility',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                )
                ->joinAttribute(
                    'price',
                    'catalog_product/price',
                    'entity_id',
                    null,
                    'left',
                    $store->getId()
                );
        } else {
            $collection
                ->addAttributeToSelect('price')
                ->joinAttribute(
                    'status',
                    'catalog_product/status',
                    'entity_id',
                    null,
                    'inner'
                )
                ->joinAttribute(
                    'visibility',
                    'catalog_product/visibility',
                    'entity_id',
                    null,
                    'inner'
                );
        }

        $collection
            ->addAttributeToFilter(
                'creator_id',
                ['neq' => null]
            )
            ->joinTable(
                $collection->getTable('customer_entity'),
                'entity_id = creator_id',
                [
                    'supplier_email' => 'email',
                    'supplier_firstname' => 'firstname',
                    'supplier_lastname' => 'lastname',
                ]
            );

        $this->setCollection($collection);

        $this->getCollection()->addWebsiteNamesToResult();

        return parent::_prepareCollection();
    }

    /**
     * Add column filtering conditions to collection.
     *
     * @param Column $column Column object.
     *
     * @return Extended
     */
    protected function _addColumnFilterToCollection($column) // @codingStandardsIgnoreLine
    {
        if ($column->getId() === 'supplier_name') {
            $filterString = $column->getFilter()->getCondition();

            $this->getCollection()->getSelect()
                ->joinInner(
                    ['ce' => 'customer_entity'],
                    'at_creator_id.value = ce.entity_id',
                    [
                        'email AS supplier_email',
                        'lastname AS supplier_lastname',
                        'firstname AS supplier_firstname',
                    ]
                )
                ->where('(ce.firstname like ?', $filterString)
                ->orWhere('ce.lastname like ?)', $filterString);

            return $this;
        }

        if ($this->getCollection()) {
            if ($column->getId() === 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                );
            }
        }

        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * Prepare grid columns.
     *
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns() // @codingStandardsIgnoreLine
    {
        $this
            ->addColumn(
                'entity_id',
                [
                    'header' => __('ID'),
                    'type' => 'number',
                    'index' => 'entity_id',
                    'header_css_class' => 'col-id',
                    'column_css_class' => 'col-id',
                ]
            )
            ->addColumn(
                'supplier_name',
                [
                    'header' => __('Supplier Name'),
                    'renderer' => '\Cminds\Supplierfrontendproductuploader\Block\Adminhtml'
                        . '\Catalog\Products\Grid\Renderer\Supplier',
                ]
            )
            ->addColumn(
                'supplier_email',
                [
                    'header' => __('Supplier Email'),
                    'index' => 'supplier_email',
                ]
            )
            ->addColumn(
                'name',
                [
                    'header' => __('Name'),
                    'index' => 'name',
                ]
            )
            ->addColumn(
                'type',
                [
                    'header' => __('Type'),
                    'index' => 'type_id',
                    'type' => 'options',
                    'options' => $this->productType->getOptionArray(),
                ]
            );

        $sets = $this->setCollectionFactory->create()
            ->setEntityTypeFilter(
                $this->productFactory->create()->getResource()->getTypeId()
            )
            ->load()
            ->toOptionHash();

        $this->addColumn(
            'set_name',
            [
                'header' => __('Attribute Set'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name',
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
            ]
        );

        $store = $this->getStore();
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->addColumn(
                'qty',
                [
                    'header' => __('Quantity'),
                    'type' => 'number',
                    'index' => 'qty',
                ]
            );
        }

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->productVisibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->productStatus->getOptionArray(),
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'websites',
                [
                    'header' => __('Websites'),
                    'sortable' => false,
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => $this->websiteFactory->create()
                        ->getCollection()
                        ->toOptionHash(),
                    'header_css_class' => 'col-websites',
                    'column_css_class' => 'col-websites',
                ]
            );
        }

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'renderer' => 'Cminds\Supplierfrontendproductuploader'
                    . '\Block\Adminhtml\Catalog\Products\Grid\Renderer\CreatedAt',
            ]
        );

        $this->addColumn(
            'supplier_product_status_action',
            [
                'header' => __('Approve'),
                'filter' => false,
                'sortable' => false,
                'index' => 'supplier_product_status_action',
                'type' => 'action',
                'renderer' => 'Cminds\Supplierfrontendproductuploader'
                    . '\Block\Adminhtml\Catalog\Products\Grid\Renderer\Approve',
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => 'catalog/product/edit',
                            'params' => [
                                'store' => $this->getRequest()->getParam('store'),
                                'supplier' => true,
                            ],
                        ],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid url.
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'supplier/supplier/products',
            ['_current' => true]
        );
    }

    /**
     * Retrieve row url.
     *
     * @param DataObject $row Row object.
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'catalog/product/edit',
            [
                'store' => $this->_request->getParam('store'),
                'supplier' => true,
                'id' => $row->getId(),
            ]
        );
    }
    public function getRecentProducts(){
        $store = $this->getStore();
        $collection = $this->productFactory->create()
            ->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->setStore($store);

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
        }

        if ($store->getId()) {
            $collection
                ->addStoreFilter($store)
                ->joinAttribute(
                    'name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    Store::DEFAULT_STORE_ID
                )
                ->joinAttribute(
                    'custom_name',
                    'catalog_product/name',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                )
                ->joinAttribute(
                    'status',
                    'catalog_product/status',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                )
                ->joinAttribute(
                    'visibility',
                    'catalog_product/visibility',
                    'entity_id',
                    null,
                    'inner',
                    $store->getId()
                )
                ->joinAttribute(
                    'price',
                    'catalog_product/price',
                    'entity_id',
                    null,
                    'left',
                    $store->getId()
                );
        } else {
            $collection
                ->addAttributeToSelect('price')
                ->joinAttribute(
                    'status',
                    'catalog_product/status',
                    'entity_id',
                    null,
                    'inner'
                )
                ->joinAttribute(
                    'visibility',
                    'catalog_product/visibility',
                    'entity_id',
                    null,
                    'inner'
                );
        }

        $collection
            ->addAttributeToFilter(
                'creator_id',
                ['neq' => null]
            )
            ->joinTable(
                $collection->getTable('customer_entity'),
                'entity_id = creator_id',
                [
                    'supplier_email' => 'email',
                    'supplier_firstname' => 'firstname',
                    'supplier_lastname' => 'lastname',
                ]
            );
            $collection->setOrder('entity_id', 'DESC');
            $collection->setPageSize(5);
            return $collection;
    }
}
