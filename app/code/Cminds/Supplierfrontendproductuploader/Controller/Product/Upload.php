<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Product;

use Cminds\Supplierfrontendproductuploader\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Upload extends AbstractController
{
    protected $product;
    protected $registry;
    protected $adapter;
    protected $jsonResultFactory;

    public function __construct(
        Context $context,
        Data $helper,
        Product $product,
        Registry $registry,
        AdapterFactory $adapterInterface,
        JsonFactory $jsonResultFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $helper,
            $storeManager,
            $scopeConfig
        );

        $this->product = $product;
        $this->registry = $registry;
        $this->adapter = $adapterInterface;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();
        $files = $this->getRequest()->getFiles();
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        if (isset($files['file_upload']['name'])
            && ($files['file_upload']['tmp_name'] != null)
        ) {
            $uploader = new \Magento\Framework\File\Uploader('file_upload');
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);

            $path = $this->getHelper()->getImageCacheDir(null);

            try {
                $uploader->save($path, $files['file_upload']['name']);
                $adapter = $this->adapter->create();
                $image = new \Magento\Framework\Image(
                    $adapter,
                    $path . $uploader->getUploadedFileName()
                );
                $image->resize(171);
                $image->save($path . '/resized/' . $uploader->getUploadedFileName());
                $this->storeManager
                    ->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $imageUrl = $this->storeManager
                        ->getStore()
                        ->getBaseUrl(
                            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        )
                    . 'upload/resized' . $uploader->getUploadedFileName();

                $ret = [
                    'success' => true,
                    'url' => $imageUrl,
                    'name' => $uploader->getUploadedFileName(),
                ];
            } catch (\Exception $e) {
                $ret = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }

            return $result->setData($ret);
        }
    }
}
