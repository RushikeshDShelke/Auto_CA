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

namespace Olegnax\Carousel\Model\Slide;

use Magento\Catalog\Model\Category\FileInfo;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Olegnax\Carousel\Api\Data\SlideInterface;
use Olegnax\Carousel\Model\ResourceModel\Slide\CollectionFactory;
use Olegnax\Carousel\Model\Slide;


class DataProvider extends AbstractDataProvider
{

    protected $dataPersistor;

    protected $collection;

    protected $loadedData;
    protected $fileInfo;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $data = $model->getData();
            $data = $this->convertValues($data, $model);
            $this->loadedData[$model->getId()] = $data;
        }
        $data = $this->dataPersistor->get('olegnax_carousel_slide');

        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()] = $model->getData();
            $this->dataPersistor->clear('olegnax_carousel_slide');
        }

        return $this->loadedData;
    }

    /**
     * @param array $data
     * @param Slide $model
     * @return mixed
     */
    protected function convertValues($data, $model)
    {
        $data = $this->convertImage($data, $model, SlideInterface::IMAGE);
        $data = $this->convertImage($data, $model, SlideInterface::MOBILE_IMAGE);
        return $data;
    }

    /**
     * @param array $data
     * @param Slide $model
     * @param string $attributeCode
     * @return mixed
     * @throws LocalizedException
     */
    private function convertImage($data, $model, $attributeCode){
        if (isset($data[$attributeCode]) && !empty($data[$attributeCode])) {
            unset($data[$attributeCode]);
            $fileName = $model->getData($attributeCode);
            $fileInfo = $this->getFileInfo();
            if ($fileInfo->isExist($fileName)) {
                $stat = $fileInfo->getStat($fileName);
                $mime = $fileInfo->getMimeType($fileName);

                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $data[$attributeCode][0]['name'] = basename($fileName);

                if ($fileInfo->isBeginsWithMediaDirectoryPath($fileName)) {
                    $data[$attributeCode][0]['url'] = $fileName;
                } else {
                    $data[$attributeCode][0]['url'] = $model->getImageUrl($attributeCode);
                }

                $data[$attributeCode][0]['size'] = isset($stat) ? $stat['size'] : 0;
                $data[$attributeCode][0]['type'] = $mime;
            }
        }
        return $data;
    }

    /**
     * Get FileInfo instance
     *
     * @return FileInfo
     *
     * @deprecated 102.0.0
     */
    private function getFileInfo(): FileInfo
    {
        if ($this->fileInfo === null) {
            $this->fileInfo = ObjectManager::getInstance()->get(FileInfo::class);
        }
        return $this->fileInfo;
    }
}

