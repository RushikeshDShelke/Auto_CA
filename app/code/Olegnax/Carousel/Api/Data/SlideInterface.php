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

namespace Olegnax\Carousel\Api\Data;


use Magento\Framework\Api\ExtensibleDataInterface;

interface SlideInterface extends ExtensibleDataInterface
{

    const CREATION_TIME = 'creation_time';
    const UPDATE_TIME = 'update_time';
    const STORE_ID = 'store_id';
    const SLIDE_ID = 'slide_id';
    const CAROUSEL = 'carousel';
    const IS_ACTIVE = 'is_active';
    const SORT_ORDER = 'sort_order';
	const LAYOUT = 'layout';
    const SLIDE_BG = 'slide_bg';
	const IMAGE = 'image';
	const MOBILE_IMAGE = 'mobile_image';
	const SUBTITLE_COLOR = 'subtitle_color';
	const TITLE_COLOR = 'title_color';
	const TITLE_BG = 'title_bg';
	const TITLE_SIZE = 'title_size';
	const BUTTON_STYLE = 'button_style';
	const BUTTON_COLOR = 'button_color';
	const BUTTON_BG = 'button_bg';
	const BUTTON_COLOR_HOVER = 'button_color_hover';
	const BUTTON_BG_HOVER = 'button_bg_hover';
	const TEXT_COLOR = 'text_color';
	const SUBTITLE = 'subtitle';
	const TITLE = 'title';
	const LINK = 'link';
	const BUTTON = 'button';
	const NAV_TITLE = 'nav_title';
    const CONTENT = 'content';
	const CONTENT2 = 'content2';
    const CONTENT_WIDTH = 'content_width';
	const CONTENT_WRAPPERS = 'content_wrappers';
	const CUSTOM_CLASS = 'custom_class';
	const CONTENT_ONLY = 'content_only';
	const MOBILE_ALIGN = 'mobile_align';
	const MARGINS = 'margins';
	const BUTTON_CSS = 'button_css';
	const SLIDE_LINK = 'slide_link';
	
    /**
     * Get slide_id
     * @return string|null
     */
    public function getSlideId();

    /**
     * Set slide_id
     * @param string $slideId
     * @return SlideInterface
     */
    public function setSlideId($slideId);

    /**
     * Get carousel
     * @return string|null
     */
    public function getCarousel();

    /**
     * Set carousel
     * @param string $carousel
     * @return SlideInterface
     */
    public function setCarousel($carousel);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $storeId
     * @return SlideInterface
     */
    public function setStoreId($storeId);

    /**
     * Get slide_bg
     * @return string|null
     */
    public function getSlideBg();

    /**
     * Set slide_bg
     * @param string $slide_bg
     * @return SlideInterface
     */
    public function setSlideBg($slide_bg);

    /**
     * Get image
     * @return string|null
     */
    public function getImage();

    /**
     * Set image
     * @param string $image
     * @return SlideInterface
     */
    public function setImage($image);

    /**
     * Get mobile_image
     * @return string|null
     */
    public function getMobileImage();

    /**
     * Set mobile_image
     * @param string $mobile_image
     * @return SlideInterface
     */
    public function setMobileImage($mobile_image);
	
    /**
     * Get is_active
     * @return string|null
     */
    public function getIsActive();

    /**
     * Set is_active
     * @param string $isActive
     * @return SlideInterface
     */
    public function setIsActive($isActive);

    /**
     * Get sort_order
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Set sort_order
     * @param string $sortOrder
     * @return SlideInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get content
     * @return string|null
     */
    public function getContent();

    /**
     * Set content
     * @param string $content
     * @return SlideInterface
     */
    public function setContent($content);
	
    /**
     * Get content2
     * @return string|null
     */
    public function getContent2();

    /**
     * Set content2
     * @param string $content2
     * @return SlideInterface
     */
    public function setContent2($content2);
    /**
     * Get creation_time
     * @return string|null
     */
    public function getCreationTime();

    /**
     * Set creation_time
     * @param string $creationTime
     * @return SlideInterface
     */
    public function setCreationTime($creationTime);

    /**
     * Get update_time
     * @return string|null
     */
    public function getUpdateTime();

    /**
     * Set update_time
     * @param string $updateTime
     * @return SlideInterface
     */
    public function setUpdateTime($updateTime);

	
    /**
     * Get layout
     * @return string|null
     */
    public function getLayout();

    /**
     * Set layout
     * @param string $layout
     * @return SlideInterface
     */
    public function setLayout($layout);
	
    /**
     * Get subtitle_color
     * @return string|null
     */
    public function getSubtitleColor();

    /**
     * Set subtitle_color
     * @param string $subtitle_color
     * @return SlideInterface
     */
    public function setSubtitleColor($subtitle_color);

    /**
     * Get title_color
     * @return string|null
     */
    public function getTitleColor();

    /**
     * Set title_color
     * @param string $title_color
     * @return SlideInterface
     */
    public function setTitleColor($title_color);
	
    /**
     * Get title_bg
     * @return string|null
     */
    public function getTitleBg();

    /**
     * Set title_bg
     * @param string $title_bg
     * @return SlideInterface
     */
    public function setTitleBg($title_bg);
	
    /**
     * Get button_color
     * @return string|null
     */
    public function getButtonColor();

    /**
     * Set button_color
     * @param string $button_color
     * @return SlideInterface
     */
    public function setButtonColor($button_color);
	
    /**
     * Get button_bg
     * @return string|null
     */
    public function getButtonBg();

    /**
     * Set button_bg
     * @param string $button_bg
     * @return SlideInterface
     */
    public function setButtonBg($button_bg);

    /**
     * Get button_color_hover
     * @return string|null
     */
    public function getButtonColorHover();

    /**
     * Set button_color_hover
     * @param string $button_color_hover
     * @return SlideInterface
     */
    public function setButtonColorHover($button_color_hover);
	
    /**
     * Get button_bg_hover
     * @return string|null
     */
    public function getButtonBgHover();

    /**
     * Set button_bg_hover
     * @param string $button_bg_hover
     * @return SlideInterface
     */
    public function setButtonBgHover($button_bg_hover);
	
    /**
     * Get text_color
     * @return string|null
     */
    public function getTextColor();

    /**
     * Set text_color
     * @param string $text_color
     * @return SlideInterface
     */
    public function setTextColor($text_color);

    /**
     * Get subtitle
     * @return string|null
     */
    public function getSubtitle();

    /**
     * Set subtitle
     * @param string $subtitle
     * @return SlideInterface
     */
    public function setSubtitle($subtitle);
	
    /**
     * Get title
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     * @param string $title
     * @return SlideInterface
     */
    public function setTitle($title);

    /**
     * Get link
     * @return string|null
     */
    public function getLink();

    /**
     * Set link
     * @param string $link
     * @return SlideInterface
     */
    public function setLink($link);
	
    /**
     * Get button
     * @return string|null
     */
    public function getButton();

    /**
     * Set button
     * @param string $button
     * @return SlideInterface
     */
    public function setButton($button);
	
    /**
     * Get nav_title
     * @return string|null
     */
    public function getNavTitle();

    /**
     * Set nav_title
     * @param string $nav_title
     * @return SlideInterface
     */
    public function setNavTitle($nav_title);
	
    /**
     * Get title_size
     * @return string|null
     */
    public function getTitleSize();

    /**
     * Set title_size
     * @param string $title_size
     * @return SlideInterface
     */
    public function setTitleSize($title_size);
	
    /**
     * Get button_style
     * @return string|null
     */
    public function getButtonStyle();

    /**
     * Set button_style
     * @param string $button_style
     * @return SlideInterface
     */
    public function setButtonStyle($button_style);
	
    /**
     * Get content_width
     * @return string|null
     */
    public function getContentWidth();

    /**
     * Set content_width
     * @param string $content_width
     * @return SlideInterface
     */
    public function setContentWidth($content_width);
	
    /**
     * Get content_wrappers
     * @return string|null
     */
    public function getContentWrappers();

    /**
     * Set content_wrappers
     * @param string $content_wrappers
     * @return SlideInterface
     */
    public function setContentWrappers($content_wrappers);
	
    /**
     * Get custom_class
     * @return string|null
     */
    public function getCustomClass();
	
    /**
     * Set custom_class
     * @param string $custom_class
     * @return SlideInterface
     */
    public function setCustomClass($custom_class);
	
    /**
     * Get content_only
     * @return string|null
     */
    public function getContentOnly();
	
    /**
     * Set content_only
     * @param string $content_only
     * @return SlideInterface
     */
    public function setContentOnly($content_only);
	
    /**
     * Get mobile_align
     * @return string|null
     */
    public function getMobileAlign();
	
    /**
     * Set mobile_align
     * @param string $mobile_align
     * @return SlideInterface
     */
    public function setMobileAlign($mobile_align);
	
    /**
     * Get margins
     * @return string|null
     */
    public function getMargins();
	
    /**
     * Set margins
     * @param string $margins
     * @return SlideInterface
     */
    public function setMargins($margins);

    /**
     * Get button_css
     * @return string|null
     */
    public function getButtonCss();

    /**
     * Set button_css
     * @param string $button_css
     * @return SlideInterface
     */
    public function setButtonCss($button_css);

    /**
     * Get slide_link
     * @return string|null
     */
    public function getSlideLink();

    /**
     * Set slide_link
     * @param string $slide_link
     * @return SlideInterface
     */
    public function setSlideLink($slide_link);
}