<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Olegnax\InstagramMin\Api\Data\IntsPostExtensionInterface;
use Olegnax\InstagramMin\Api\Data\IntsPostInterface;

class IntsPost extends AbstractExtensibleObject implements IntsPostInterface
{

    /**
     * Get intspost_id
     * @return string|null
     */
    public function getIntspostId()
    {
        return $this->_get(self::INTSPOST_ID);
    }

    /**
     * Set intspost_id
     * @param string $intspostId
     * @return IntsPostInterface
     */
    public function setIntspostId($intspostId)
    {
        return $this->setData(self::INTSPOST_ID, $intspostId);
    }

    /**
     * Get owner
     * @return string|null
     */
    public function getOwner()
    {
        return $this->_get(self::OWNER);
    }

    /**
     * Set owner
     * @param string $owner
     * @return IntsPostInterface
     */
    public function setOwner($owner)
    {
        return $this->setData(self::OWNER, $owner);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return IntsPostExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param IntsPostExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        IntsPostExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get typename
     * @return string|null
     */
    public function getTypename()
    {
        return $this->_get(self::TYPENAME);
    }

    /**
     * Set typename
     * @param string $typename
     * @return IntsPostInterface
     */
    public function setTypename($typename)
    {
        return $this->setData(self::TYPENAME, $typename);
    }

    /**
     * Get shortcode
     * @return string|null
     */
    public function getShortcode()
    {
        return $this->_get(self::SHORTCODE);
    }

    /**
     * Set shortcode
     * @param string $shortcode
     * @return IntsPostInterface
     */
    public function setShortcode($shortcode)
    {
        return $this->setData(self::SHORTCODE, $shortcode);
    }

    /**
     * Get dimensions_width
     * @return string|null
     */
    public function getDimensionsWidth()
    {
        return $this->_get(self::DIMENSIONS_WIDTH);
    }

    /**
     * Set dimensions_width
     * @param string $dimensionsWidth
     * @return IntsPostInterface
     */
    public function setDimensionsWidth($dimensionsWidth)
    {
        return $this->setData(self::DIMENSIONS_WIDTH, $dimensionsWidth);
    }

    /**
     * Get dimensions_height
     * @return string|null
     */
    public function getDimensionsHeight()
    {
        return $this->_get(self::DIMENSIONS_HEIGHT);
    }

    /**
     * Set dimensions_height
     * @param string $dimensionsHeight
     * @return IntsPostInterface
     */
    public function setDimensionsHeight($dimensionsHeight)
    {
        return $this->setData(self::DIMENSIONS_HEIGHT, $dimensionsHeight);
    }

    /**
     * Get display_url
     * @return string|null
     */
    public function getDisplayUrl()
    {
        return $this->_get(self::DISPLAY_URL);
    }

    /**
     * Set display_url
     * @param string $displayUrl
     * @return IntsPostInterface
     */
    public function setDisplayUrl($displayUrl)
    {
        return $this->setData(self::DISPLAY_URL, $displayUrl);
    }

    /**
     * Get edge_media_to_caption
     * @return string|null
     */
    public function getEdgeMediaToCaption()
    {
        return $this->_get(self::EDGE_MEDIA_TO_CAPTION);
    }

    /**
     * Set edge_media_to_caption
     * @param string $edgeMediaToCaption
     * @return IntsPostInterface
     */
    public function setEdgeMediaToCaption($edgeMediaToCaption)
    {
        return $this->setData(self::EDGE_MEDIA_TO_CAPTION, $edgeMediaToCaption);
    }

    /**
     * Get edge_media_to_comment
     * @return string|null
     */
    public function getEdgeMediaToComment()
    {
        return $this->_get(self::EDGE_MEDIA_TO_COMMENT);
    }

    /**
     * Set edge_media_to_comment
     * @param string $edgeMediaToComment
     * @return IntsPostInterface
     */
    public function setEdgeMediaToComment($edgeMediaToComment)
    {
        return $this->setData(self::EDGE_MEDIA_TO_COMMENT, $edgeMediaToComment);
    }

    /**
     * Get taken_at_timestamp
     * @return string|null
     */
    public function getTakenAtTimestamp()
    {
        return $this->_get(self::TAKEN_AT_TIMESTAMP);
    }

    /**
     * Set taken_at_timestamp
     * @param string $takenAtTimestamp
     * @return IntsPostInterface
     */
    public function setTakenAtTimestamp($takenAtTimestamp)
    {
        return $this->setData(self::TAKEN_AT_TIMESTAMP, $takenAtTimestamp);
    }

    /**
     * Get edge_liked_by
     * @return string|null
     */
    public function getEdgeLikedBy()
    {
        return $this->_get(self::EDGE_LIKED_BY);
    }

    /**
     * Set edge_liked_by
     * @param string $edgeLikedBy
     * @return IntsPostInterface
     */
    public function setEdgeLikedBy($edgeLikedBy)
    {
        return $this->setData(self::EDGE_LIKED_BY, $edgeLikedBy);
    }

    /**
     * Get edge_media_preview_like
     * @return string|null
     */
    public function getEdgeMediaPreviewLike()
    {
        return $this->_get(self::EDGE_MEDIA_PREVIEW_LIKE);
    }

    /**
     * Set edge_media_preview_like
     * @param string $edgeMediaPreviewLike
     * @return IntsPostInterface
     */
    public function setEdgeMediaPreviewLike($edgeMediaPreviewLike)
    {
        return $this->setData(self::EDGE_MEDIA_PREVIEW_LIKE, $edgeMediaPreviewLike);
    }

    /**
     * Get location
     * @return string|null
     */
    public function getLocation()
    {
        return $this->_get(self::LOCATION);
    }

    /**
     * Set location
     * @param string $location
     * @return IntsPostInterface
     */
    public function setLocation($location)
    {
        return $this->setData(self::LOCATION, $location);
    }

    /**
     * Get video_view_count
     * @return string|null
     */
    public function getVideoViewCount()
    {
        return $this->_get(self::VIDEO_VIEW_COUNT);
    }

    /**
     * Set video_view_count
     * @param string $videoViewCount
     * @return IntsPostInterface
     */
    public function setVideoViewCount($videoViewCount)
    {
        return $this->setData(self::VIDEO_VIEW_COUNT, $videoViewCount);
    }

    /**
     * Get thumbnail_src
     * @return string|null
     */
    public function getThumbnailSrc()
    {
        return $this->_get(self::THUMBNAIL_SRC);
    }

    /**
     * Set thumbnail_src
     * @param string $thumbnailSrc
     * @return IntsPostInterface
     */
    public function setThumbnailSrc($thumbnailSrc)
    {
        return $this->setData(self::THUMBNAIL_SRC, $thumbnailSrc);
    }

    /**
     * Get thumbnail_src_320
     * @return string|null
     */
    public function getThumbnailSrc320()
    {
        return $this->_get(self::THUMBNAIL_SRC_320);
    }

    /**
     * Set thumbnail_src_320
     * @param string $thumbnailSrc320
     * @return IntsPostInterface
     */
    public function setThumbnailSrc320($thumbnailSrc320)
    {
        return $this->setData(self::THUMBNAIL_SRC_320, $thumbnailSrc320);
    }

    /**
     * Get thumbnail_src_480
     * @return string|null
     */
    public function getThumbnailSrc480()
    {
        return $this->_get(self::THUMBNAIL_SRC_480);
    }

    /**
     * Set thumbnail_src_480
     * @param string $thumbnailSrc480
     * @return IntsPostInterface
     */
    public function setThumbnailSrc480($thumbnailSrc480)
    {
        return $this->setData(self::THUMBNAIL_SRC_480, $thumbnailSrc480);
    }

    /**
     * Get thumbnail_src_640
     * @return string|null
     */
    public function getThumbnailSrc640()
    {
        return $this->_get(self::THUMBNAIL_SRC_640);
    }

    /**
     * Set thumbnail_src_640
     * @param string $thumbnailSrc640
     * @return IntsPostInterface
     */
    public function setThumbnailSrc640($thumbnailSrc640)
    {
        return $this->setData(self::THUMBNAIL_SRC_640, $thumbnailSrc640);
    }

    /**
     * Get is_active
     * @return string|null
     */
    public function getIsActive()
    {
        return $this->_get(self::IS_ACTIVE);
    }

    /**
     * Set is_active
     * @param string $isActive
     * @return IntsPostInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Get ints_id
     * @return string|null
     */
    public function getIntsId()
    {
        return $this->_get(self::INTS_ID);
    }

    /**
     * Set ints_id
     * @param string $intsId
     * @return IntsPostInterface
     */
    public function setIntsId($intsId)
    {
        return $this->setData(self::INTS_ID, $intsId);
    }
}

