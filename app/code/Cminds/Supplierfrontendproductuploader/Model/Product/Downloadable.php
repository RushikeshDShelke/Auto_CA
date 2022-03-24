<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Framework\Registry;
use Cminds\Supplierfrontendproductuploader\Model\Product\Downloadable\Link\Url as DownloadableLinkUrl;
use Cminds\Supplierfrontendproductuploader\Model\Product\Downloadable\Link\File as DownloadableLinkFile;
use Magento\Downloadable\Model\Product\TypeHandler\LinkFactory;
use Magento\Framework\App\ProductMetadataInterface;

class Downloadable
{
	const TYPE_CODE = 'downloadable';

    /**
     * Product Factory.
     *
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * Core Registry.
     *
     * @var Registry
     */
    private $registry;

    /**
     * Cminds Link Url Entity.
     *
     * @var DownloadableLinkUrl
     */
    private $linkUrl;

    /**
     * Cminds Link File Entity.
     *
     * @var DownloadableLinkFile
     */
    private $linkFile;

    private $linkFactory;
    private $productMetadata;

    /**
     * Downloadable constructor.
     *
     * @param ProductFactory $productFactory
     * @param Registry $registry
     * @param DownloadableLinkUrl $linkUrl
     * @param DownloadableLinkFile $linkFile
     * @param LinkFactory $linkFactory
     * @param ProductMetadataInterface $productMetadata
     */
	public function __construct(
		ProductFactory $productFactory,
        Registry $registry,
        DownloadableLinkUrl $linkUrl,
        DownloadableLinkFile $linkFile,
        LinkFactory $linkFactory,
        ProductMetadataInterface $productMetadata
	) {
		$this->productFactory = $productFactory;
        $this->registry = $registry;
        $this->linkUrl = $linkUrl;
        $this->linkFile = $linkFile;
        $this->linkFactory = $linkFactory;
        $this->productMetadata = $productMetadata;
	}

    /**
     * Create new links for the newly created product.
     * This method is used only in Cminds\Supplierfrontendproductuploader\Controller\Product\Save Controller.
     * Must be refactored or changed in the future because the form for creating downloadable products seems
     * for me not finished in general.
     *
     * @param int|string $productId
     * @param $files
     * @param null $storeId
     * @param array $data
     *
     * @return $this|void
     * @throws LocalizedException
     */
	public function createLinks($productId, $files, $storeId = null, array $data)
	{
		if (!$productId) {
			return $this;
		}

		$storeId = $storeId ?: Store::DEFAULT_STORE_ID;

		$product = $this->productFactory->create()
			->setStoreId($storeId)
			->load($productId);

		if ($product->getTypeId() !== static::TYPE_CODE) {
			return;
		}

        $this->registry->register('current_context_store_id', $storeId);

        $uploadedFiles = [];
        if ($files) {
            foreach ($files as $key => $file) {
                if ($file['name'] !== '') {
                    $uploadedFiles[$key] = $file;
                }
            }
        }

        if (count($uploadedFiles) === 0) {
            if (empty($data['link_url'])) {
                return;
            }

            $link = $this->linkUrl->buildLink($data);

           $this->saveLink($product, $link);
        } else {
            if (isset($files['downloadable_upload'])) {
                $fileName = 'downloadable_upload';

                $link = $this->linkFile->buildLink($data, $fileName);
                $this->saveLink($product, $link);
            }
        }

        $this->registry->unregister('current_context_store_id');
    
        return $this;
    }

    /**
     * Save, Create, Update Links retrieved form the product form to the database.
     * This method is used only in Cminds\Supplierfrontendproductuploader\Controller\Product\Save Controller.
     *
     * @param $productId
     * @param $files
     * @param null $storeId
     * @param array $links
     *
     * @return $this|void
     * @throws LocalizedException
     */
    public function saveLinks($productId, $files, $storeId = null, array $links)
    {
        if (!$productId || !$links) {
            return $this;
        }

        $storeId = $storeId ?: Store::DEFAULT_STORE_ID;

        $product = $this->productFactory->create()
            ->setStoreId($storeId)
            ->load($productId);

        if ($product->getTypeId() !== static::TYPE_CODE) {
            return;
        }

        $this->registry->register('current_context_store_id', $storeId);

        $uploadedFiles = [];
        if ($files) {
            foreach ($files as $key => $file) {
                if ($file['name'] !== '') {
                    $uploadedFiles[$key] = $file;
                }
            }
        }

        foreach ($links as $link) {
            $fileId = $link['file_id'];
            
            if (isset($link['id'])) {
                $link['link_id'] = $link['id'];
            }

            if (isset($uploadedFiles[$fileId])) {
                $link = $this->linkFile->buildLink($link, $fileId);
                $this->saveLink($product, $link);
            } elseif (isset($link['url']) && $link['url'] !== '') {
                $link['link_url'] = $link['url'];
                $link = $this->linkUrl->buildLink($link);

                $this->saveLink($product, $link);
            }
        }
        
        $this->registry->unregister('current_context_store_id');
    
        return $this;
    }

    /**
     * Save link entity into the database.
     *
     * @param ProductInterface $product
     * @param $link
     *
     * @return Downloadable
     */
    private function saveLink(ProductInterface $product, $link)
    {
        if (!$link) {
            return $this;
        }

        if (is_array($link)) {
            $this->linkFactory->create()
                ->save($product, $link);

            return $this;
        } elseif ($link instanceof LinkInterface) {
            if (!in_array($link->getLinkType(), ['file', 'url'])) {
                return $this;
            }

            if ($link->getLinkType() === 'file') {
                $this->linkFile
                    ->setProduct($product)
                    ->save($link);
            } elseif ($link->getLinkType() === 'url') {
                $this->linkUrl
                    ->setProduct($product)
                    ->save($link);
            }

            return $this;
        }

        return $this;
    }
}
