<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\InstagramMin\Api\Data\IntsPostInterface;
use Olegnax\InstagramMin\Api\Data\IntsPostInterfaceFactory;
use Olegnax\InstagramMin\Model\ResourceModel\IntsPost\Collection;

class IntsPost extends AbstractModel
{

    const BASE_URL = "https://www.instagram.com/";
    const BASE_POST_URL = "https://www.instagram.com/p/";
    protected $_eventPrefix = 'olegnax_instagrammin_intspost';
    protected $dataObjectHelper;

    protected $intspostDataFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param IntsPostInterfaceFactory $intspostDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceModel\IntsPost $resource
     * @param StoreManagerInterface $storeManager
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        IntsPostInterfaceFactory $intspostDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\IntsPost $resource,
        StoreManagerInterface $storeManager,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->intspostDataFactory = $intspostDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve intspost model with intspost data
     * @return IntsPostInterface
     */
    public function getDataModel()
    {
        $intspostData = $this->getData();

        $intspostDataObject = $this->intspostDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $intspostDataObject,
            $intspostData,
            IntsPostInterface::class
        );

        return $intspostDataObject;
    }

    /**
     * @param bool $one
     * @return array|string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getImageUrl($one = true)
    {
        $images = $this->getDisplayUrl($one);

        if (is_array($images)) {
            foreach ($images as &$image) {
                $image = $this->prepareUrl($image);
            }
            return $images;
        }

        return $this->prepareUrl($images);
    }

    public function getDisplayUrl($one = true){
        $images = $this->getData("display_url");
        if ($one) {
            $images = array_shift($images);
        }
        return $images;
    }

    /**
     * @param string $image
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function prepareUrl($image)
    {
        $url = "";
        if ($image) {
            if (is_string($image)) {
                if ( preg_match( '#^http#i', $image ) ) {
                    $url = $image;
                } else {
                    $mediaBaseUrl = $this->_storeManager->getStore()->getBaseUrl(
                        UrlInterface::URL_TYPE_MEDIA
                    );
                    $url          = $mediaBaseUrl . $image;
                }
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getURL()
    {
        return static::BASE_POST_URL . $this->getData("shortcode") . '/';
    }

    /**
     * @return string
     */
    public function getOwnerURL()
    {
        return static::BASE_URL . $this->getData("owner") . '/';
    }

}

