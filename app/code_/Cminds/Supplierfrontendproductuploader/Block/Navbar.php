<?php

namespace Cminds\Supplierfrontendproductuploader\Block;

use Cminds\Supplierfrontendproductuploader\Helper\Data as CmindsHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Request\Http\Proxy;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Event\Manager;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\DataObject;
use Cminds\Supplierfrontendproductuploader\Model\VendorPanel\Cache\Type as Cache;
use Cminds\Supplierfrontendproductuploader\Helper\Inventory as CmindsInventoryHelper;

class Navbar extends Template
{
    const CACHE_KEY_NAVIGATION = 'navigation';

    /**
     * @var array|null
     */
    protected $markedProductIds;
    protected $currentCustomer;
    protected $collectionFactory;
    protected $cmindsHelper;
    protected $customerFactory;
    protected $proxy;

    /**
     * Navigation items.
     *
     * @var DataObject
     */
    private $navItems;

    /**
     * Event manager object.
     *
     * @var Manager
     */
    private $eventManager;

    /**
     * DataObject factory.
     *
     * @var DataObjectFactory
     */
    private $dataObjectFactory;
    
    /**
     * Cminds Inventory Helper.
     *
     * @var CmindsInventoryHelper
     */
    private $cmindsInventoryHelper;

    /**
     * Cache object.
     *
     * @var Cache
     */
    private $cache;

    public function __construct(
        Context $context,
        CurrentCustomer $currentCustomer,
        CollectionFactory $collectionFactory,
        CmindsHelper $cmindsHelper,
        CustomerFactory $customerFactory,
        Proxy $proxy,
        Manager $eventManager,
        DataObjectFactory $dataObjectFactory,
        CmindsInventoryHelper $cmindsInventoryHelper,
        Cache $cache,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->currentCustomer = $currentCustomer;
        $this->collectionFactory = $collectionFactory;
        $this->cmindsHelper = $cmindsHelper;
        $this->customerFactory = $customerFactory;
        $this->proxy = $proxy;
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->cmindsInventoryHelper = $cmindsInventoryHelper;
        $this->cache = $cache;
    }

    /**
     * Prepare navigation items.
     *
     * @return string
     */
    public function _beforeToHtml()
    {
        $this->initNavigation();

        return parent::_beforeToHtml();
    }

    public function getHelper()
    {
        return $this->cmindsHelper;
    }

    public function getMarkedProductCount()
    {
        if ($this->markedProductIds === null) {
            $this->markedProductIds = $this->getMarkedProduct();
        }

        return count($this->markedProductIds);
    }

    public function hasMarkedProducts()
    {
        return $this->getMarkedProductCount() > 0;
    }

    public function getMarkedProduct()
    {
        $count = [];

        $collection = $this->collectionFactory->create();
        $collection
            ->addAttributeToSelect('creator_id')
            ->addAttributeToFilter(
                [
                    [
                        'attribute' => 'creator_id',
                        'eq' => $this->cmindsHelper->getSupplierId(),
                    ],
                ]
            );

        foreach ($collection as $product) {
            $count[] = $product->getId();
        }

        return $count;
    }

    public function getSupplierCustomer($id)
    {
        return $this->customerFactory->create()->load($id);
    }

    public function getActionName()
    {
        return $this->getRequest()->getFullActionName();
    }

    public function getControllerName()
    {
        return $this->proxy->getControllerName();
    }

    public function getStoreConfig($path)
    {
        return $this->_scopeConfig->getValue($path);
    }

    public function isImportEnabled()
    {
        $value = $this->getStoreConfig(
            'products_settings/csv_import/enable_csv_import'
        );

        if ($value) {
            return true;
        }

        return false;
    }

    public function isSourceSuggestionEnabled()
    {
        if( false === $this->cmindsInventoryHelper->msiFunctionalitySupported() )
            return false;

        $value = $this->getStoreConfig(
            'configuration/configure/source_suggestion'
        );

        if ($value) {
            return true;
        }

        return false;
    }

    /**
     * Collect navigation items.
     *
     * @return Navbar
     */
    public function initNavigation()
    {
        if ($this->cache->load(self::CACHE_KEY_NAVIGATION)) {
            return $this;
        }

        $this->eventManager->dispatch(
            'supplierfrontendproductuploader_navbar_init',
            ['navigation_items' => $this->getNavigationItems()]
        );

        return $this;
    }

    /**
     * Get base navigation items.
     *
     * @return DataObject
     */
    public function getNavigationItems()
    {
        if ($this->navItems === null) {
            $items = [
                'dashboard' => [
                    'label' => 'Dashboard',
                    'url' => 'supplier',
                    'parent' => null,
                    'action_names' => [
                        'supplier_index_index',
                    ],
                    'sort' => 0,
                ],
                /*'add_product' => [
                    'label' => 'Add a Product',
                    'url' => 'supplier/product/chooseType',
                    'parent' => null,
                    'action_names' => [
                        'supplier_product_chooseType',
                        'supplier_product_create',
                    ],
                    'sort' => 1,
                ], */
                'product_list' => [
                    'label' => 'Product List',
                    'url' => 'supplier/product/productlist',
                    'parent' => null,
                    'action_names' => [
                        'supplier_product_productlist',
                        'supplier_product_edit',
                        'supplier_product_clone',
                    ],
                    'sort' => 2,
                ],
                'settings' => [
                    'label' => 'Settings',
                    'url' => null,
                    'parent' => null,
                    'action_names' => [
                        'supplier_settings_notifications',
                    ],
                    'sort' => 3,
                ],
                'notifications' => [
                    'label' => 'Notifications',
                    'url' => 'supplier/settings/notifications',
                    'parent' => 'settings',
                    'sort' => 0,
                ],

                'reports' => [
                    'label' => 'Reports',
                    'url' => 'marketplace/reports/orderreceived',
                    'parent' => null,
                    'action_names' => [
                        'marketplace_reports_orderreceived',
                    ],
                    'sort' => 4,
                ],
               
            ];

            if ($this->supplierPagesEnabled()) {
                $items['supplier_page'] = [
                    'label' => 'My Profile Page',
                    'url' => 'marketplace/settings/profile',
                    'parent' => 'settings',
                    'sort' => -1,
                ];

                $items['settings']['action_names'] = array_merge(
                    $items['settings']['action_names'],
                    ['supplier_settings_profile']
                );

                $items['my_supplier_page'] = [
                    'label' => 'My Supplier Page',
                    'url' => $this->getSupplierRawPageUrl($this->getLoggedSupplier()->getId()),
                    'parent' => null,
                    'sort' => 4.5,
                ];
            }

            if ($this->isImportEnabled()) {
                $items['import'] = [
                    'label' => 'Import',
                    'url' => null,
                    'parent' => null,
                    'action_names' => [
                        'supplier_import_products',
                    ],
                    'sort' => 1.5,
                ];
                $items['import_products'] = [
                    'label' => 'Products',
                    'url' => 'supplier/import/products',
                    'parent' => 'import',
                    'sort' => 0,
                ];
            }

            $this->navItems = $this->dataObjectFactory->create()
                ->setData($items);
        }

        return $this->navItems;
    }

    /**
     * Get menu items.
     *
     * @param string $parent
     *
     * @return array
     */
    public function getMenuItems($parent = '')
    {
        $cacheKey = self::CACHE_KEY_NAVIGATION;
        if ($parent !== '') {
            $cacheKey .= '_' . $parent;
        }

        $items = $this->cache->load($cacheKey);
        if ($items) {
            return json_decode($items, 1);
        }

        $items = [];
        foreach ($this->getNavigationItems()->getData() as $key => $item) {
            if ((!$parent && $item['parent'] === null)
                || ($parent && $item['parent'] === $parent)
            ) {
                $items[$key] = $item;
            }
        }
        $items = $this->sortItems($items, 'sort');

        $this->cache->save(json_encode($items), $cacheKey);

        return $items;
    }

    /**
     * Sort array by key.
     *
     * @param array  $array
     * @param string $key
     *
     * @return array
     */
    protected function sortItems(array $array, $key)
    {
        $sorter = [];
        $result = [];
        reset($array);

        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }

        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $result[$ii] = $array[$ii];
        }

        return $result;
    }


    /**
     * Validate if current navigation item is active.
     *
     * @param array $item
     *
     * @return bool
     */
    public function isActive(array $item)
    {
        $result = false;
        if (isset($item['action_names'])
            && in_array($this->getActionName(), $item['action_names'], true)
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * Validate if navigation item has key attribute.
     *
     * @param array  $item
     * @param string $key
     *
     * @return bool
     */
    public function hasKey($item, $key)
    {
        $result = false;

        if (isset($item[$key]) && $item[$key] === true) {
            $result = true;
        }

        return $result;
    }

    /**
     * Get supplier page url from helper.
     *
     * @param int $supplierId
     *
     * @return string
     */
    public function getSupplierRawPageUrl($supplierId)
    {
        return $this->cmindsHelper->getSupplierRawPageUrl($supplierId);
    }

    /**
     * Get logged customer from session.
     *
     * @return Customer
     */
    public function getLoggedSupplier()
    {
        return $this->cmindsHelper->getLoggedSupplier();
    }

    /**
     * Get configuration of supplier pages.
     *
     * @return bool
     */
    public function supplierPagesEnabled()
    {
        return $this->cmindsHelper->supplierPagesEnabled();
    }
}
