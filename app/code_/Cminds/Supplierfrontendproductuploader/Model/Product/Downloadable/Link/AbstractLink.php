<?php
namespace Cminds\Supplierfrontendproductuploader\Model\Product\Downloadable\Link;

use Magento\Downloadable\Model\LinkFactory as DownloadableLinkFactory;
use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Magento\Downloadable\Model\Link\Builder as LinkBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\ProductMetadataInterface;

abstract class AbstractLink 
{
	/**
	 * Product Factory.
	 *
	 * @var ProductFactory
	 */
	protected $productFactory;

	/**
	 * Cached Product Entity.
	 *
	 * @var Product
	 */
	protected $product;

	/**
	 * Link Builder.
	 *
	 * @var LinkBuilder
	 */
	//protected $linkBuilder;

	/**
	 * Magento Link Entity Factory.
	 *
	 * @var DownloadableLinkFactory
	 */
	protected $downloadableLinkFactory;

	/**
	 * Core Registry.
	 *
	 * @var Registry
	 */
	protected $registry;

	/**
	 * Link Repository.
	 *
	 * @var LinkRepository
	 */
	protected $linkRepository;

	protected $productMetadata;

    /**
     * AbstractLink constructor.
     *
     * @param ProductFactory $productFactory
     * @param DownloadableLinkFactory $downloadableLinkFactory
     * @param Registry $registry
     * @param LinkRepository $linkRepository
     * @param ProductMetadataInterface $productMetadata
     */
	public function __construct(
		ProductFactory $productFactory,
		downloadableLinkFactory $downloadableLinkFactory,
		Registry $registry,
		LinkRepository $linkRepository,
        ProductMetadataInterface $productMetadata
	) {
		$this->productFactory = $productFactory;
		$this->downloadableLinkFactory = $downloadableLinkFactory;
		$this->registry = $registry;
		$this->linkRepository = $linkRepository;
		$this->productMetadata = $productMetadata;
	}

	/**
	 * Set default values to the link, which is initiated.
	 *
	 * @return array
	 */
	protected function setDefaultValues()
	{
		return [
			'title' => 'Link', 
			'sort_order' => '1',
            'sample' => [
                'file' => '[]',
                'url' => ''
            ],
            'price' => 0,
            'number_of_downloads' => 0,
            'is_shareable' => 1,
            'link_url' => NULL
		];
	}

	/**
	 * Set specified data to the default values.
	 * Used while initiating link entity.
	 *
	 * @param array $linkEntity
	 * @param array $data
	 *
	 * @return array|void
	 */
	protected function setCustomData(array $linkEntity, array $data)
	{
		if (!$linkEntity || !$data) {
			return;
		}

		foreach ($data as $key => $value) {
			$linkEntity[$key] = $data[$key];
		}

		return $linkEntity;
	}

	/**
	 * Set link type.
	 *
	 * @param array $linkEntity
	 *
	 * @return array|void
	 */
	protected function setType(array $linkEntity)
	{
		if (!$linkEntity) {
			return;
		}

		$linkEntity['type'] = static::LINK_DATA_TYPE;

		return $linkEntity;
	}

	/**
	 * Set Product.
	 *
	 * @param ProductInterface $product
	 *
	 * @return AbstractLink
	 */
	public function setProduct(ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }

	/**
	 * Get Product.
	 *
	 * @return Product
	 */
    public function getProduct()
    {
        return $this->product;
    }

	/**
	 * Create or build link entity.
	 *
	 * @param array $data
	 *
	 * @return LinkInterface
	 * @throws LocalizedException
	 */
    public function createLink(array $data)
	{
		$linkData = $this->setDefaultValues();
		$linkData = $this->setCustomData($linkData, $data);
		$linkData = $this->setType($linkData);

        $version = $this->productMetadata->getVersion();

        /** true if current magento version is lower than 2.1.0 */
        if (version_compare($version, '2.1.0') === -1) {
            $preparedData['link'][] = $linkData;

            return $preparedData;
        } else {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            /** Class Magento\Downloadable\Model\Link\Builder exists only starting from version 2.1.0 */
            $linkBuilder = $objectManager->create('Magento\Downloadable\Model\Link\Builder');

            $link = $linkBuilder->setData(
                $linkData
            )->build(
                $this->downloadableLinkFactory->create()
            );

            return $link;
        }
	}

	/**
	 * Save link entity into the database.
	 *
	 * @param LinkInterface $link
	 *
	 * @return AbstractLink
	 * @throws LocalizedException
	 */
	public function save(LinkInterface $link)
	{
		$product = $this->getProduct();
		if (!$product) {
			throw new LocalizedException(__('No product for saving downloadable link is specified'));
		}

		$sku = $product->getSku();

		$storeId = $this->registry->registry('current_context_store_id') ?: Store::DEFAULT_STORE_ID;

        $this->linkRepository->save($sku, $link, $storeId);

        return $this;
	}
}
