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

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\Carousel\Api\Data\CarouselInterface;
use Olegnax\Carousel\Api\Data\CarouselInterfaceFactory;
use Olegnax\Carousel\Model\ResourceModel\Carousel\Collection;
use Olegnax\Carousel\Model\ResourceModel\Slide\CollectionFactory;


class Carousel extends AbstractModel
{

    const UPDATE_TIME = 'update_time';
    protected $_eventPrefix = 'olegnax_carousel_carousel';
    /**
     * @var CarouselInterfaceFactory
     */
    protected $carouselDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var CollectionFactory
     */
    private $slideFactory;

    /**
     * Carousel constructor.
     * @param Context $context
     * @param Registry $registry
     * @param CarouselInterfaceFactory $carouselDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceModel\Carousel $resource
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $slideFactory
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CarouselInterfaceFactory $carouselDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\Carousel $resource,
        StoreManagerInterface $storeManager,
        CollectionFactory $slideFactory,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->carouselDataFactory = $carouselDataFactory;
        $this->slideFactory = $slideFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve carousel model with carousel data
     * @return CarouselInterface
     */
    public function getDataModel()
    {
        $carouselData = $this->getData();

        $carouselDataObject = $this->carouselDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $carouselDataObject,
            $carouselData,
            CarouselInterface::class
        );

        return $carouselDataObject;
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

    public function getSlide($store_id = null)
    {
        return $this->getAllActiveSlide()
            ->addStoreFilter($store_id ? $this->_storeManager->getStore($store_id) : null);
    }

    public function getAllActiveSlide()
    {
        return $this->getAllSlide()
            ->addFieldToFilter('is_active', '1');
    }

    public function getAllSlide()
    {
        return $this->slideFactory->create()->addFieldToSelect('*')
            ->addFieldToFilter('carousel', $this->getData('identifier'))
            ->setOrder('sort_order', 'asc');
    }

    /**
     * Return current store id
     *
     * @return int
     */
    protected function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Carousel::class);
    }

}

