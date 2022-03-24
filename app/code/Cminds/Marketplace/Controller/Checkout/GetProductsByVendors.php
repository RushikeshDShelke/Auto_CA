<?php

namespace Cminds\Marketplace\Controller\Checkout;

use Cminds\Marketplace\Helper\Supplier;
use Cminds\Marketplace\Model\Methods as MethodsModel;
use Cminds\Marketplace\Model\Shipping\Carrier\Marketplace\Shipping;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\CustomerFactory as CustomerFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Cminds\Marketplace\Helper\Data;
use Cminds\Supplierfrontendproductuploader\Helper\Price;

class GetProductsByVendors extends Action
{
    protected $productFactory;
    protected $customerFactory;
    protected $store;
    protected $methods;
    protected $carrierModel;
    protected $supplierHelper;
    protected $session;
    protected $jsonResultFactory;
    protected $marketplaceHelper;
    protected $priceHelper;

    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        CustomerFactory $customerFactory,
        StoreManagerInterface $store,
        MethodsModel $methodsModel,
        Shipping $carrierModel,
        Supplier $supplierHelper,
        Session $session,
        JsonFactory $jsonFactory,
        Data $marketplaceHelper,
        Price $price
    ) {
        parent::__construct($context);

        $this->productFactory = $productFactory;
        $this->customerFactory = $customerFactory;
        $this->store = $store;
        $this->methods = $methodsModel;
        $this->carrierModel = $carrierModel;
        $this->supplierHelper = $supplierHelper;
        $this->session = $session;
        $this->jsonResultFactory = $jsonFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->priceHelper = $price;
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $json = $this->getRequest()->getParams();
        $items = json_decode($json['json'], true);
        $counrtyval = $json['cid'];

        $this->session->unsCountryVal();
        $CounrtyVal = $this->session->setCountryVal($counrtyval);
        $productsBySuppliers = [];

        foreach ($items as $item) {
            $product = $this->productFactory->create()
                ->load($item['product']['entity_id']);

            $productData = $product->getData();
            if (isset($productData['thumbnail'])) {
                $productData['productImage'] = $this->store->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                    . 'catalog/product' . $productData['thumbnail'];
            } else {
                $productData['productImage'] = $this->store->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_STATIC)
                    . 'frontend/Magento/luma/en_US/Magento_Catalog/'
                    . 'images/product/placeholder/image.jpg';
            }

            if ($product->getCreatorId() === null) {
                $productsBySuppliers['non_supplier'][] = $productData;
            } else {
                $productsBySuppliers[$product->getCreatorId()][] = $productData;
            }
        }

        $output = [];

        foreach ($productsBySuppliers as $supplierId => $products) {
            $methods = $this->supplierHelper->getShippingMethods(
                $supplierId,
                $products
            );

            $methodsArr = [];
            $selected = $this->session->getMarketplaceShippingMethods();

            foreach ($methods as $method) {
                $methodData = $method->getData();
                if (isset($selected[$supplierId])
                    && $selected[$supplierId]['method_id'] === $methodData['id']
                ) {
                    $methodData['checked'] = true;
                } else {
                    $methodData['checked'] = false;
                }

                if (isset($methodData['price'])) {
                    $convertedPrice = $this->priceHelper->convertToCurrentCurrencyPrice((double)$methodData['price']);
                    $currencySymbol = $this->priceHelper->getCurrentCurrencySymbol();
                    $methodData['converted_price'] = $currencySymbol . $convertedPrice;
                }

                $methodsArr[] = $methodData;
            }

            if ($supplierId === 'non_supplier') {
                $selected['non_supplier'] = [
                    'method_id' => null,
                    'price' => $this->carrierModel
                        ->getSupplierShippingPriceNonSupplier(),
                ];

                $price_total = $this->supplierHelper
                    ->calculateTotalShippingPrice($selected);
                $this->session
                    ->setMarketplaceShippingMethods($selected);
                $this->session
                    ->setMarketplaceShippingPrice($price_total);
            }
            
            $supplierName = $this->supplierHelper->getSupplierNameForShippingMethods($supplierId);
            if ($supplierId != 'non_supplier' && !empty($methodsArr)) {
                    $output[] = [
                        'supplier_id' => $supplierId,
                        'supplier_name' => $supplierName,
                        'products' => $products,
                        'methods' => $methodsArr,
                    ];
            } else {
                $output = [];
            }
        }

        return $result->setData(
            $output
        );
    }
}