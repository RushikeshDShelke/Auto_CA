<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Product;

use Cminds\Supplierfrontendproductuploader\Model\Config as ModuleConfig;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Block\Data as DirectoryBlockData;
use Magento\Directory\Helper\Data as DirectoryHelperData;
use Magento\Framework\Json\EncoderInterface as JsonEncoderInterface;
use Magento\Framework\App\Cache\Type\Config as CacheTypeConfig;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;

/**
 * Cminds Supplierfrontendproductuploader product sources block.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 */
class Source extends DirectoryBlockData
{
    /**
     * Customer session object.
     *
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Customer factory object.
     *
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Module config object.
     *
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * Object constructor.
     *
     * @param Context                   $context                       Context object.
     * @param CustomerSession           $customerSession               Customer session object.
     * @param CustomerFactory           $customerFactory               Customer factory object.
     * @param ModuleConfig              $moduleConfig                  Module config object.
     * @param DirectoryHelperData       $directoryHelper               Magento\Directory\Helper\Data
     * @param JsonEncoderInterface      $jsonEncoder                   Magento\Framework\Json\EncoderInterface
     * @param CacheTypeConfig           $configCacheType               Magento\Framework\App\Cache\Type\Config
     * @param RegionCollectionFactory   $regionCollectionFactory       Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     * @param CountryCollectionFactory  $countryCollectionFactory      Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     * @param array                     $data                          Data array.
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        ModuleConfig $moduleConfig,
        DirectoryHelperData $directoryHelper,
        JsonEncoderInterface $jsonEncoder,
        CacheTypeConfig $configCacheType,
        RegionCollectionFactory $regionCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        array $data
    ) {

        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->moduleConfig = $moduleConfig;

        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Retrieve currently logged in customer object.
     *
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * Get config value.
     *
     * @param string $path
     * 
     * @return string|null
     */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
