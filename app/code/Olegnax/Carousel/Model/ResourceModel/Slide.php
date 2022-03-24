<?php declare(strict_types=1);
/**
 * Copyright (c) 2021
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Olegnax\Carousel\Model\ResourceModel;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Olegnax\Carousel\Api\Data\SlideInterface;
use Psr\Log\LoggerInterface;

class Slide extends AbstractDb
{
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var ImageUploader
     */
    private $imageUploader;
    /**
     * @var Filesystem
     */
    private $_filesystem;

    public function __construct(
        Context $context,
        Filesystem $filesystem,
        Json $json,
        LoggerInterface $logger,
        $connectionName = null
    ) {
        $this->_filesystem = $filesystem;
        $this->json = $json;
        $this->_logger = $logger;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('olegnax_carousel_slide', 'slide_id');
    }

    protected function _beforeSave(AbstractModel $object)
    {
        $data = $object->getData('store_id');
        if (!empty($data)) {
            if (is_array($data)) {
                $data = implode(',', $data);
                $object->setData('store_id', $data);
            }
        } else {
            $object->setData('store_id', 0);
        }
        $this->beforeSaveImage($object, SlideInterface::IMAGE);
        $this->beforeSaveImage($object, SlideInterface::MOBILE_IMAGE);

        return parent::_beforeSave($object);
    }

    private function beforeSaveImage($object, $attributeName)
    {
        $value = $object->getData($attributeName);

        if ($this->fileResidesOutsideCategoryDir($value)) {
            // use relative path for image attribute so we know it's outside of category dir when we fetch it
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $value[0]['url'] = parse_url($value[0]['url'], PHP_URL_PATH);
            $value[0]['name'] = $value[0]['url'];
        }

        if ($imageName = $this->getUploadedImageName($value)) {
            if (!$this->fileResidesOutsideCategoryDir($value)) {
                $imageName = $this->checkUniqueImageName($imageName);
            }
            $object->setData($attributeName, $imageName);
        } elseif (!is_string($value)) {
            $object->setData($attributeName, null);
        }

        return $this;
    }

    /**
     * Check for file path resides outside of category media dir. The URL will be a path including pub/media if true
     *
     * @param array|null $value
     * @return bool
     */
    private function fileResidesOutsideCategoryDir($value)
    {
        if (!is_array($value) || !isset($value[0]['url'])) {
            return false;
        }

        $fileUrl = ltrim($value[0]['url'], '/');
        $baseMediaDir = $this->_filesystem->getUri(DirectoryList::MEDIA);

        if (!$baseMediaDir) {
            return false;
        }

        return strpos($fileUrl, $baseMediaDir) !== false;
    }

    /**
     * Gets image name from $value array.
     *
     * Will return empty string in a case when $value is not an array.
     *
     * @param array $value Attribute value
     * @return string
     */
    private function getUploadedImageName($value)
    {
        if (is_array($value) && isset($value[0]['name'])) {
            return $value[0]['name'];
        }

        return '';
    }

    /**
     * Check that image name exists in catalog/category directory and return new image name if it already exists.
     *
     * @param string $imageName
     * @return string
     */
    private function checkUniqueImageName(string $imageName): string
    {
        $imageUploader = $this->getImageUploader();
        $mediaDirectory = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $imageAbsolutePath = $mediaDirectory->getAbsolutePath(
            $imageUploader->getBasePath() . DIRECTORY_SEPARATOR . $imageName
        );

        $imageName = Uploader::getNewFilename($imageAbsolutePath);

        return $imageName;
    }

    /**
     * Get Instance of Category Image Uploader.
     *
     * @return ImageUploader
     *
     * @deprecated 101.0.0
     */
    private function getImageUploader()
    {
        if ($this->imageUploader === null) {
            $this->imageUploader = ObjectManager::getInstance()
                ->get(ImageUploader::class);
        }

        return $this->imageUploader;
    }

    protected function _afterLoad(AbstractModel $object)
    {
        $data = $object->getData('store_id');
        if (!empty($data) && !is_array($data)) {
            $data = explode(',', $data);
            $object->setData('store_id', $data);
        }
        return parent::_afterLoad($object);
    }
}

