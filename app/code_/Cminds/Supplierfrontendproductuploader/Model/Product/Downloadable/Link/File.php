<?php
namespace Cminds\Supplierfrontendproductuploader\Model\Product\Downloadable\Link;

use Magento\Downloadable\Api\Data\LinkInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Downloadable\Helper\File as FileHelper;
use Magento\Downloadable\Model\Link as DownloadableLink;
use Magento\Downloadable\Model\LinkFactory as DownloadableLinkFactory;
use Magento\Downloadable\Api\LinkRepositoryInterface as LinkRepository;
use Cminds\Supplierfrontendproductuploader\Model\Product\Downloadable\LinkEntityInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\App\ProductMetadataInterface;

class File extends AbstractLink implements LinkEntityInterface
{
	const LINK_DATA_TYPE = 'file';

    /**
     * Uploader factory.
     *
     * @var UploaderFactory
     */
	protected $uploaderFactory;

    /**
     * File Helper.
     *
     * @var FileHelper
     */
	protected $fileHelper;

    /**
     * Downloadable Link Model.
     *
     * @var DownloadableLink
     */
	protected $downloadableLink;

    /**
     * Downloadable Link Factory.
     *
     * @var DownloadableLinkFactory
     */
	protected $downloadableLinkFactory;

    /**
     * Product Metadata Interface.
     * It is used only to retrieve current Magento verison.
     *
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * File constructor.
     *
     * @param UploaderFactory $uploaderFactory
     * @param FileHelper $fileHelper
     * @param DownloadableLink $downloadableLink
     * @param LinkRepository $linkRepository
     * @param DownloadableLinkFactory $downloadableLinkFactory
     * @param ProductFactory $productFactory
     * @param Registry $registry
     * @param ProductMetadataInterface $productMetadata
     */
	public function __construct(
		UploaderFactory $uploaderFactory,
		FileHelper $fileHelper,
		DownloadableLink $downloadableLink,
		LinkRepository $linkRepository,
		DownloadableLinkFactory $downloadableLinkFactory,
		ProductFactory $productFactory,
		Registry $registry,
        ProductMetadataInterface $productMetadata
	) {
		parent::__construct(
			$productFactory,
			$downloadableLinkFactory,
			$registry,
			$linkRepository,
            $productMetadata
		);

		$this->uploaderFactory = $uploaderFactory;
		$this->fileHelper = $fileHelper;
		$this->downloadableLink = $downloadableLink;
		$this->downloadableLinkFactory = $downloadableLinkFactory;
        $this->productMetadata = $productMetadata;
	}

    /**
     * Build Link Entity or link array data depending on current magento version.
     *
     * @param array $data
     * @param $name
     *
     * @return array|LinkInterface
     * @throws LocalizedException
     */
	public function buildLink(array $data, $name)
	{
		if (!$data) {
			throw new LocalizedException(__('No data for link is provided'));
		}

		if (!$name || !is_string($name)) {
			throw new LocalizedException(__('No file id is specified for downloadable link'));
		}

		$linkData = $data;

		$linkData['file'] = $this->getFileData($name);

		$link = parent::createLink($linkData);

		return $link;
	}

    /**
     * Get uploaded file data.
     *
     * @param string $name file name from the form
     * @return array|string
     *
     * @throws LocalizedException
     * @throws \Exception
     */
	public function getFileData($name)
	{
		if (!$name || !is_string($name)) {
			throw new LocalizedException(__('File name is not  set or is set not properly'));
		}

		$baseTmp = $this->downloadableLink->getBaseTmpPath();

        $uploader = $this->uploaderFactory->create(['fileId' => $name]);

        $result = $this->fileHelper->uploadFromTmp($baseTmp, $uploader);
        if (!$result) {
            throw new \Exception('File can not be moved from temporary folder to the destination folder.');
        }

        $file = $this->formFile($result);

        return $file;
	}

    /**
     * Depending on the current Magento version prepare uploaded file data.
     *
     * @param array $result
     *
     * @return array|string
     * @throws LocalizedException
     */
	private function formFile(array $result = [])
    {
        if (!$result) {
            throw new LocalizedException(__('No file data is provided'));
        }

        if (!isset($result['name']) ||
            !isset($result['file']) ||
            !isset($result['size'])
        ) {
            throw new LocalizedException(__('Not all required file attributes are not set'));
        }

        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.1.0') === -1) {
            return json_encode(
                [
                    [
                        'name' => $result['name'],
                        'file' => $result['file'],
                        'size' => $result['size'],
                        'status' => 'new'
                    ]
                ]
            );
        } else {
            return
                [
                    [
                        'name' => $result['name'],
                        'file' => $result['file'],
                        'size' => $result['size'],
                        'status' => 'new'
                    ]
                ];
        }
    }
}
