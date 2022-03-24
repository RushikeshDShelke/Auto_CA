<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface IntsPostInterface extends ExtensibleDataInterface
{

    const IS_ACTIVE = 'is_active';
    const SHORTCODE = 'shortcode';
    const THUMBNAIL_SRC_480 = 'thumbnail_src_480';
    const TYPENAME = 'typename';
    const TAKEN_AT_TIMESTAMP = 'taken_at_timestamp';
    const OWNER = 'owner';
    const INTSPOST_ID = 'intspost_id';
    const DIMENSIONS_HEIGHT = 'dimensions_height';
    const EDGE_MEDIA_TO_COMMENT = 'edge_media_to_comment';
    const THUMBNAIL_SRC = 'thumbnail_src';
    const EDGE_MEDIA_TO_CAPTION = 'edge_media_to_caption';
    const THUMBNAIL_SRC_640 = 'thumbnail_src_640';
    const EDGE_MEDIA_PREVIEW_LIKE = 'edge_media_preview_like';
    const LOCATION = 'location';
    const INTS_ID = 'ints_id';
    const THUMBNAIL_SRC_320 = 'thumbnail_src_320';
    const DIMENSIONS_WIDTH = 'dimensions_width';
    const DISPLAY_URL = 'display_url';
    const VIDEO_VIEW_COUNT = 'video_view_count';
    const EDGE_LIKED_BY = 'edge_liked_by';

    /**
     * Get intspost_id
     * @return string|null
     */
    public function getIntspostId();

    /**
     * Set intspost_id
     * @param string $intspostId
     * @return IntsPostInterface
     */
    public function setIntspostId($intspostId);

    /**
     * Get owner
     * @return string|null
     */
    public function getOwner();

    /**
     * Set owner
     * @param string $owner
     * @return IntsPostInterface
     */
    public function setOwner($owner);

    /**
     * Get typename
     * @return string|null
     */
    public function getTypename();

    /**
     * Set typename
     * @param string $typename
     * @return IntsPostInterface
     */
    public function setTypename($typename);

    /**
     * Get shortcode
     * @return string|null
     */
    public function getShortcode();

    /**
     * Set shortcode
     * @param string $shortcode
     * @return IntsPostInterface
     */
    public function setShortcode($shortcode);

    /**
     * Get dimensions_width
     * @return string|null
     */
    public function getDimensionsWidth();

    /**
     * Set dimensions_width
     * @param string $dimensionsWidth
     * @return IntsPostInterface
     */
    public function setDimensionsWidth($dimensionsWidth);

    /**
     * Get dimensions_height
     * @return string|null
     */
    public function getDimensionsHeight();

    /**
     * Set dimensions_height
     * @param string $dimensionsHeight
     * @return IntsPostInterface
     */
    public function setDimensionsHeight($dimensionsHeight);

    /**
     * Get display_url
     * @return string|null
     */
    public function getDisplayUrl();

    /**
     * Set display_url
     * @param string $displayUrl
     * @return IntsPostInterface
     */
    public function setDisplayUrl($displayUrl);

    /**
     * Get edge_media_to_caption
     * @return string|null
     */
    public function getEdgeMediaToCaption();

    /**
     * Set edge_media_to_caption
     * @param string $edgeMediaToCaption
     * @return IntsPostInterface
     */
    public function setEdgeMediaToCaption($edgeMediaToCaption);

    /**
     * Get edge_media_to_comment
     * @return string|null
     */
    public function getEdgeMediaToComment();

    /**
     * Set edge_media_to_comment
     * @param string $edgeMediaToComment
     * @return IntsPostInterface
     */
    public function setEdgeMediaToComment($edgeMediaToComment);

    /**
     * Get taken_at_timestamp
     * @return string|null
     */
    public function getTakenAtTimestamp();

    /**
     * Set taken_at_timestamp
     * @param string $takenAtTimestamp
     * @return IntsPostInterface
     */
    public function setTakenAtTimestamp($takenAtTimestamp);

    /**
     * Get edge_liked_by
     * @return string|null
     */
    public function getEdgeLikedBy();

    /**
     * Set edge_liked_by
     * @param string $edgeLikedBy
     * @return IntsPostInterface
     */
    public function setEdgeLikedBy($edgeLikedBy);

    /**
     * Get edge_media_preview_like
     * @return string|null
     */
    public function getEdgeMediaPreviewLike();

    /**
     * Set edge_media_preview_like
     * @param string $edgeMediaPreviewLike
     * @return IntsPostInterface
     */
    public function setEdgeMediaPreviewLike($edgeMediaPreviewLike);

    /**
     * Get location
     * @return string|null
     */
    public function getLocation();

    /**
     * Set location
     * @param string $location
     * @return IntsPostInterface
     */
    public function setLocation($location);

    /**
     * Get video_view_count
     * @return string|null
     */
    public function getVideoViewCount();

    /**
     * Set video_view_count
     * @param string $videoViewCount
     * @return IntsPostInterface
     */
    public function setVideoViewCount($videoViewCount);

    /**
     * Get thumbnail_src
     * @return string|null
     */
    public function getThumbnailSrc();

    /**
     * Set thumbnail_src
     * @param string $thumbnailSrc
     * @return IntsPostInterface
     */
    public function setThumbnailSrc($thumbnailSrc);

    /**
     * Get thumbnail_src_320
     * @return string|null
     */
    public function getThumbnailSrc320();

    /**
     * Set thumbnail_src_320
     * @param string $thumbnailSrc320
     * @return IntsPostInterface
     */
    public function setThumbnailSrc320($thumbnailSrc320);

    /**
     * Get thumbnail_src_480
     * @return string|null
     */
    public function getThumbnailSrc480();

    /**
     * Set thumbnail_src_480
     * @param string $thumbnailSrc480
     * @return IntsPostInterface
     */
    public function setThumbnailSrc480($thumbnailSrc480);

    /**
     * Get thumbnail_src_640
     * @return string|null
     */
    public function getThumbnailSrc640();

    /**
     * Set thumbnail_src_640
     * @param string $thumbnailSrc640
     * @return IntsPostInterface
     */
    public function setThumbnailSrc640($thumbnailSrc640);

    /**
     * Get is_active
     * @return string|null
     */
    public function getIsActive();

    /**
     * Set is_active
     * @param string $isActive
     * @return IntsPostInterface
     */
    public function setIsActive($isActive);

    /**
     * Get ints_id
     * @return string|null
     */
    public function getIntsId();

    /**
     * Set ints_id
     * @param string $intsId
     * @return IntsPostInterface
     */
    public function setIntsId($intsId);
}

