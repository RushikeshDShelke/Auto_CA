<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Import;

use Cminds\Supplierfrontendproductuploader\Helper\Data as CmindsHelper;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class Products extends Template
{
    protected $_product;
    protected $registry;
    protected $catalogProduct;
    protected $_cmindsHelper;
    protected $_attributeSetCollection;
    protected $_objectManager;

    public function __construct(
        Registry $registry,
        Context $context,
        CatalogProduct $product,
        CmindsHelper $cmindsHelper,
        AttributeSetCollection $attributeSetCollection,
        ObjectManagerInterface $objectManagerInterface
    ) {
        $this->catalogProduct = $product;
        $this->registry = $registry;
        $this->_cmindsHelper = $cmindsHelper;
        $this->_attributeSetCollection = $attributeSetCollection;
        $this->_objectManager = $objectManagerInterface;

        parent::__construct($context);
    }

    public function getReport()
    {
        return $this->registry->registry('import_data');
    }

    public function isExists()
    {
        return (is_array($this->getReport()) && count($this->getReport()) > 0);
    }

    public function getSuccessFull()
    {
        $success = [];

        foreach ($this->getReport() as $report) {
            if (!$report['success']) {
                continue;
            }
            $success[] = $report;
        }

        return $success;
    }

    public function getFailed()
    {
        $failed = [];

        foreach ($this->getReport() as $report) {
            if ($report['success']) {
                continue;
            }
            $failed[] = $report;
        }

        return $failed;
    }

    public function getMaxImagesCount()
    {
        return $this->_cmindsHelper->getMaxImages();
    }

    public function isUploadDone()
    {
        return $this->registry->registry('upload_done');
    }

    public function getSelectedAttributeSetId()
    {
        return $this->registry->registry('attributeSetId');
    }

    public function getEntityTypeId()
    {
        return $this->catalogProduct->getResource()->getTypeId();
    }

    public function getAttributeSetCollection()
    {
        return $this->_attributeSetCollection
            ->setEntityTypeFilter($this->getEntityTypeId())
            ->addFieldToFilter('available_for_supplier', 1);
    }

    public function getStoreConfig($path)
    {
        $scopeConfig = $this->_objectManager
            ->create('Magento\Framework\App\Config\ScopeConfigInterface');

        return $scopeConfig->getValue($path);
    }
}
