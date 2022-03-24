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

namespace Olegnax\Carousel\Model;

use Magento\Catalog\Model\Category\FileInfo;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filter\Template;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\Carousel\Api\Data\SlideInterface;
use Olegnax\Carousel\Api\Data\SlideInterfaceFactory;
use Olegnax\Carousel\Model\ResourceModel\Slide\Collection;


class Slide extends AbstractModel
{

    const UPDATE_TIME = 'update_time';
    protected $slideDataFactory;

    protected $_eventPrefix = 'olegnax_carousel_slide';
    protected $dataObjectHelper;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var DirectoryList
     */
    protected $directoryList;
    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * Slide constructor.
     * @param Context $context
     * @param Registry $registry
     * @param SlideInterfaceFactory $slideDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceModel\Slide $resource
     * @param Collection $resourceCollection
     * @param StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SlideInterfaceFactory $slideDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\Slide $resource,
        Collection $resourceCollection,
        StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->slideDataFactory = $slideDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve slide model with slide data
     * @return SlideInterface
     */
    public function getDataModel()
    {
        $slideData = $this->getData();

        $slideDataObject = $this->slideDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $slideDataObject,
            $slideData,
            SlideInterface::class
        );

        return $slideDataObject;
    }

    public function getIdentities()
    {
        return [$this->_eventPrefix . '_' . $this->getId()];
    }

    public function beforeSave()
    {
        if ($this->hasDataChanges()) {
            $this->setUpdateTime(date('Y-m-d H:i:s'));
        }

        parent::beforeSave();
    }

    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Returns image url
     *
     * @param string $attributeCode
     * @return bool|string
     * @throws LocalizedException
     */
    public function getImageUrl($attributeCode = 'image')
    {
        $url = false;
        $image = $this->getData($attributeCode);
        if ($image) {
            if (is_string($image)) {
                $store = $this->_storeManager->getStore();
                $baseUrl = $store->getBaseUrl();
                $baseUrl = str_replace(parse_url($baseUrl, PHP_URL_PATH), '/', $baseUrl);
                $url = $baseUrl . ltrim($image, '/');
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }

        return $url;
    }

    public function hasImage($fieldId = 'image')
    {
        return ((bool)$this->getData($fieldId) !== false && $this->existsImage($fieldId));
    }

    public function existsImage($fieldId = 'image')
    {
        $fullPath = $this->getAbsoluteImagePath($fieldId);
        return $fullPath
            && file_exists($fullPath)
            && is_file($fullPath);
    }

    public function getAbsoluteImagePath($fieldId = 'image')
    {
        return $this->getPrefixedPath(
            $this->getData($fieldId),
            $this->directoryList->getPath("media")
        );
    }

    private function getPrefixedPath($path, $prefix = '')
    {
        $fullPath = '';
        if (!empty($path)) {
            $path = preg_replace('#^/pub#i', '', $path);
            $path = preg_replace('#^/media#i', '', $path);
            if (preg_match('#/$#', $prefix)) {
                $path = ltrim($path, '/');
            }
            $fullPath = $prefix . $path;
        }
        return $fullPath;
    }

    public function getContent($store_id = null)
    {
        $content = $this->getData('content');
        if (empty($content)) {
            $content = '';
        }
        $content = htmlspecialchars_decode($content);
        $content = trim($content);
        /** @var Template $filter */
        $filter = $this->filterProvider->getBlockFilter();
        if (!empty($store_id)) {
            $filter = $filter->setStoreId($store_id);
        }
        $content = $filter->filter($content);

        return $content;
    }

    public function getContent2($store_id = null)
    {
        $content = $this->getData('content2');
        if (empty($content)) {
            $content = '';
        }
        $content = htmlspecialchars_decode($content);
        $content = trim($content);
        /** @var Template $filter */
        $filter = $this->filterProvider->getBlockFilter();
        if (!empty($store_id)) {
            $filter = $filter->setStoreId($store_id);
        }
        $content = $filter->filter($content);

        return $content;
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Slide::class);
    }
}

