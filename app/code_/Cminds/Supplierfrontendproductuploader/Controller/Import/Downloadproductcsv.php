<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Import;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Management;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class Downloadproductcsv extends AbstractController
{
    /**
     * @var Management
     */
    private $attributeManagement;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * Downloadproductcsv constructor.
     *
     * @param Context               $context
     * @param Data                  $helper
     * @param Management            $management
     * @param ScopeConfigInterface  $scopeConfig
     * @param RawFactory            $rawFactory
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     */
    public function __construct(
        Context $context,
        Data $helper,
        Management $management,
        RawFactory $rawFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->attributeManagement = $management;
        $this->resultRawFactory = $rawFactory;
    }

    /**
     * @return Raw|ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $avoidAttributes = $this->helper->getDisallowedCsvFields();

        $attributeSetId = $this->getRequest()->getParam('attributeSetId');
        if ($attributeSetId === null) {
            $this->messageManager->addErrorMessage(
                __('Attribute set has been not selected.')
            );
            return $this->_redirect('supplier/import/products');
        }

        try {
            $attributes = $this->attributeManagement->getAttributes($attributeSetId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(
                __('Attribute set has been not found.')
            );
            return $this->_redirect('supplier/import/products');
        }

        $attributesCollection = [];
        $attributesCollection[] = 'ID';
        foreach ($attributes as $attribute) {
            if (in_array($attribute['attribute_code'], $avoidAttributes, true)) {
                continue;
            }
            if ($attribute['attribute_code'] === 'sku') {
                $value = $this->scopeConfig->getValue(
                    'products_settings/adding_products/supplier_can_define_sku'
                );
                if (!$value == 2) {
                    continue;
                }
            }
            if ((int)$attribute['is_required'] === 1) {
                $string = trim($attribute['attribute_code']);
                $string .= ((int)$attribute['is_required'] === 1) ? ' (*)' : '';
                $attributesCollection[] = $string;
            } else {
                $string = trim($attribute['attribute_code']);
                $attributesCollection[] = $string;
            }
        }
        $attributesCollection[] = 'category (*)';
        $attributesCollection[] = 'qty (*)';
        $value = $this->scopeConfig->getValue(
            'products_settings/adding_products/maximum_allowed_images'
        );
        for ($i = 0; $i < $value; $i++) {
            $attributesCollection[] = 'image';
        }

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader(
                'Pragma',
                'public',
                true
            )
            ->setHeader(
                'Cache-Control',
                'must-revalidate, post-check=0, pre-check=0',
                true
            )
            ->setHeader('Content-type', 'application/force-download')
            ->setHeader('Content-type', 'application/octet-stream')
            ->setHeader('Content-type', 'download')
            ->setHeader('Content-Disposition', 'attachment;filename=sample_import_file.csv')
            ->setHeader('Content-Transfer-Encoding', 'binary');

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->helper->array2Csv($attributesCollection));

        return $resultRaw;
    }
}
