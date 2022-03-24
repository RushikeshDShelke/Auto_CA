<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Lof
 * @package   Lof\PincodeChecker
 * @author    Landofcoder <landofcoder@gmail.com>
 * @copyright 2020 Landofcoder
 * @license   https://landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\PincodeChecker\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Lof\PincodeChecker\Model\ResourceModel\Pincodechecker\CollectionFactory
     */
    protected $pincodeCollection;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    protected $helperConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\Product $product
     * @param \Lof\PincodeChecker\Model\ResourceModel\Pincodechecker\CollectionFactory $pincodeCollection
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param Config $config
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Product $product,
        \Lof\PincodeChecker\Model\ResourceModel\Pincodechecker\CollectionFactory $pincodeCollection,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        Config $config
    )
    {
        $this->pincodeCollection = $pincodeCollection;
        $this->product = $product;
        $this->resultFactory = $resultFactory;
        $this->helperConfig = $config;
        parent::__construct($context);
    }

    /**
     * Get collection of pincode
     */
    public function getCollection()
    {
        return $this->pincodeCollection->create();
    }

    /**
     * Get pincode status
     */
    public function getPincodeStatus($pincode)
    {
        $collection = $this->getCollection();
        $collection->addFieldToFilter('pincode', array('eq' => $pincode));
        
        if($collection->getData()){
            return true;
        }else{
            return false;
        }

    }

    /**
     * Get pincode status by product
     */
    public function getProductPincodeStatus($id, $pincode)
    {
        $product = $this->product->load($id);
        $pincodes = $product->getData('pincode');
        $pincodeArr = explode(',', $pincodes);

        if(in_array($pincode, $pincodeArr))
        {
            return true;
        }else{
            return false;
        }
            
    }

    /**
     * Get pincode status message
     */
    public function getMessage($status, $pincode)
    {
        return $this->helperConfig->getMessage($status, $pincode);
    }

    /**
     * Get redirect url
     */
    public function getRedirect()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    /**
     * Check module enable
     */
    public function getIsEnable()
    {
        return $this->helperConfig->getIsEnable();
    }

    /**
     * Get check on addtocart config value
     */
    public function getIsCheckonAddtoCart()
    {
        return $this->helperConfig->getIsCheckonAddtoCart();
    }

    /**
     * Get success message config value
     */
    public function getSuccessMessage()
    {
        return $this->helperConfig->getSuccessMessage();
    }

    /**
     * Get fail message config value
     */
    public function getFailMessage()
    {
        return $this->helperConfig->getFailMessage();
    }
}