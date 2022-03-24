<?php

namespace Cminds\Marketplace\Helper;

use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductFactory;
use Cminds\Marketplace\Model\Config\Source\Fee\Type;

class Profits extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $_fee;
    private $_feeType;
    protected $_productFactory;
    protected $_category;
    protected $_customer;

    public function __construct(
        Context $context,
        Customer $customer,
        ProductFactory $product,
        Category $category,
        Type $type
    ) {
        parent::__construct($context);

        $this->_customer = $customer;
        $this->_feeType = $type;
        $this->_productFactory = $product;
        $this->_category = $category;
    }

    public function getVendorIncome($product, $price) {
        $storeProfit = $this->getStoreProfitByProduct($product);
        $profitType = $this->getFeeType($product);

        if($profitType ==  Type::FIXED) {
            $vendorIncome =  min($price, ($price - $storeProfit));
            $realPercentValue = (100-(100*$vendorIncome)/$price);
        } else {
            $vendorIncome = min($price, ($price * (100 - $storeProfit) / 100));
            $realPercentValue = $storeProfit;
        }

        return array('income' => $vendorIncome, 'percentage' => $realPercentValue);
    }

    public function getStoreProfitByProduct($product) {
        $_fee = null;

        if(is_object($product)) {
            $p = $product;
        } else {
            $p =  $this->_productFactory->create()->load($product);
        }

        if($p->getData(Type::PRODUCT_ATTRIBUTE_FEE)){
            $_fee = $p->getData(Type::PRODUCT_ATTRIBUTE_FEE);
            return $_fee;
        }

        if(!$_fee) {
            $categories = $product->getCategoryIds();

            $categories = $this->_category
                ->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $categories))
                ->addFieldToFilter('is_active', array('eq' => '1'))
                ->addAttributeToSelect(Type::CATEGORY_ATTRIBUTE_FEE)
                ->setOrder(Type::CATEGORY_ATTRIBUTE_FEE, 'DESC');

            $_fee = $categories->getFirstItem()->getData(Type::CATEGORY_ATTRIBUTE_FEE);
        }

        if($_fee == null || trim($_fee) == "" ) {
            $customerObj = $this->_customer->load($p->getCreatorId());
            $_fee = $customerObj->getData(Type::VENDOR_ATTRIBUTE_FEE);

            if($_fee != null && trim($_fee) != "") {
                return $_fee;
            } else {
                $_fee = null;
            }

        }

        if(!$_fee || trim($_fee) == "") {
            $_fee = $this->scopeConfig->getValue(
                'configuration_marketplace/'
                . 'configure/percentage_fee'
            );
        }

        return $_fee;
    }

    public function getFeeType($product) {
        $_feeType = null;

        if(is_object($product)) {
            $p = $product;
        } else {
            $p = $this->_productFactory->create()->load($product);
        }

        if($p->getData(Type::PRODUCT_ATTRIBUTE_FEE_TYPE) &&
            in_array($p->getData(Type::PRODUCT_ATTRIBUTE_FEE_TYPE), Type::toValidate())){
            $_feeType = $p->getData(Type::PRODUCT_ATTRIBUTE_FEE_TYPE);
            return $_feeType;
        }

        if(($_feeType == null || trim($_feeType) == "" )) {
            $categories = $p->getCategoryIds();

            $categories = $this->_category
                ->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $categories))
                ->addFieldToFilter('is_active', array('eq' => '1'))
                ->addAttributeToSelect(Type::CATEGORY_ATTRIBUTE_FEE,Type::CATEGORY_ATTRIBUTE_FEE_TYPE)
                ->setOrder(Type::CATEGORY_ATTRIBUTE_FEE_TYPE, 'DESC');

            $_feeType = $categories->getFirstItem()->getData(Type::CATEGORY_ATTRIBUTE_FEE_TYPE);
        }

        if(($_feeType == null || trim($_feeType) == "" )) {
            $customerObj = $this->_customer->load($p->getCreatorId());
            $_feeType = $customerObj->getData(Type::VENDOR_ATTRIBUTE_FEE_TYPE);

            if(in_array($customerObj->getData(Type::VENDOR_ATTRIBUTE_FEE_TYPE), Type::toValidate())) {
                return $_feeType;
            } else {
                $_feeType = null;
            }
        }

        if(!$_feeType || trim($_feeType) == "") {
            $_feeType =  $_fee = $this->scopeConfig->getValue(
                'configuration_marketplace/'
                . 'configure/fee_type'
            );
        }

        return $_feeType;
    }

    public function calcuateStoreIncome($supplier, $amount)
    {
        $storeFee = $this->getStoreProfit($supplier);

        return $amount * $storeFee / 100;
    }

    public function calculateNetIncome($supplier, $amount)
    {
        $storeFee = $this->getStoreProfit($supplier);

        return $amount - ($amount * ($storeFee / 100));
    }

    public function getStoreProfit($supplier)
    {
        if (!$this->_fee) {
            $customerObj = $this->_customer->load($supplier);
            $this->_fee = $customerObj->getData('percentage_fee');

            $this->scopeConfig
                ->getValue('configuration_marketplace/configure/percentage_fee');
            if ($this->_fee == null || trim($this->_fee) == "") {
                $this->_fee = $this->scopeConfig
                    ->getValue('configuration_marketplace/configure/percentage_fee');
            }
        }

        return $this->_fee;
    }
}
