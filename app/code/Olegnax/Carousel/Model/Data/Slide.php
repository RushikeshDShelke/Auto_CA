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

namespace Olegnax\Carousel\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Olegnax\Carousel\Api\Data\SlideExtensionInterface;
use Olegnax\Carousel\Api\Data\SlideInterface;


class Slide extends AbstractExtensibleObject implements SlideInterface
{

    /**
     * Get slide_id
     * @return string|null
     */
    public function getSlideId()
    {
        return $this->_get(self::SLIDE_ID);
    }

    /**
     * Set slide_id
     * @param string $slideId
     * @return SlideInterface
     */
    public function setSlideId($slideId)
    {
        return $this->setData(self::SLIDE_ID, $slideId);
    }

    /**
     * Get carousel
     * @return string|null
     */
    public function getCarousel()
    {
        return $this->_get(self::CAROUSEL);
    }

    /**
     * Set carousel
     * @param string $carousel
     * @return SlideInterface
     */
    public function setCarousel($carousel)
    {
        return $this->setData(self::CAROUSEL, $carousel);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return SlideExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param SlideExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        SlideExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Set store_id
     * @param string $storeId
     * @return SlideInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get slide_bg
     * @return string|null
     */
    public function getSlideBg()
    {
        return $this->_get(self::SLIDE_BG);
    }

    /**
     * Set slide_bg
     * @param string $slide_bg
     * @return SlideInterface
     */
    public function setSlideBg($slide_bg)
    {
        return $this->setData(self::SLIDE_BG, $slide_bg);
    }

    /**
     * Get image
     * @return string|null
     */
    public function getImage()
    {
        return $this->_get(self::IMAGE);
    }

    /**
     * Set image
     * @param string $image
     * @return SlideInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }
    /**
     * Get mobile_image
     * @return string|null
     */
    public function getMobileImage()
    {
        return $this->_get(self::MOBILE_IMAGE);
    }

    /**
     * Set mobile_image
     * @param string $mobile_image
     * @return SlideInterface
     */
    public function setMobileImage($mobile_image)
    {
        return $this->setData(self::MOBILE_IMAGE, $mobile_image);
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
     * @return SlideInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Get sort_order
     * @return string|null
     */
    public function getSortOrder()
    {
        return $this->_get(self::SORT_ORDER);
    }

    /**
     * Set sort_order
     * @param string $sortOrder
     * @return SlideInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Get content
     * @return string|null
     */
    public function getContent()
    {
        return $this->_get(self::CONTENT);
    }

    /**
     * Set content
     * @param string $content
     * @return SlideInterface
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }
	
    /**
     * Get content2
     * @return string|null
     */
    public function getContent2()
    {
        return $this->_get(self::CONTENT2);
    }

    /**
     * Set content2
     * @param string $content2
     * @return SlideInterface
     */
    public function setContent2($content2)
    {
        return $this->setData(self::CONTENT2, $content2);
    }
	
    /**
     * Get creation_time
     * @return string|null
     */
    public function getCreationTime()
    {
        return $this->_get(self::CREATION_TIME);
    }

    /**
     * Set creation_time
     * @param string $creationTime
     * @return SlideInterface
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Get update_time
     * @return string|null
     */
    public function getUpdateTime()
    {
        return $this->_get(self::UPDATE_TIME);
    }

    /**
     * Set update_time
     * @param string $updateTime
     * @return SlideInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }
	

	
	
    /**
     * Get layout
     * @return string|null
     */
    public function getLayout()
    {
        return $this->_get(self::LAYOUT);
    }

    /**
     * Set layout
     * @param string $layout
     * @return SlideInterface
     */
    public function setLayout($layout)
    {
        return $this->setData(self::LAYOUT, $layout);
    }
	
    /**
     * Get subtitle_color
     * @return string|null
     */
    public function getSubtitleColor()
    {
        return $this->_get(self::SUBTITLE_COLOR);
    }

    /**
     * Set subtitle_color
     * @param string $subtitle_color
     * @return SlideInterface
     */
    public function setSubtitleColor($subtitle_color)
    {
        return $this->setData(self::SUBTITLE_COLOR, $subtitle_color);
    }
    /**
     * Get title_color
     * @return string|null
     */
    public function getTitleColor()
    {
        return $this->_get(self::TITLE_COLOR);
    }

    /**
     * Set title_color
     * @param string $title_color
     * @return SlideInterface
     */
    public function setTitleColor($title_color)
    {
        return $this->setData(self::TITLE_COLOR, $title_color);
    }
	
    /**
     * Get title_bg
     * @return string|null
     */
    public function getTitleBg()
    {
        return $this->_get(self::TITLE_BG);
    }

    /**
     * Set title_bg
     * @param string $title_bg
     * @return SlideInterface
     */
    public function setTitleBg($title_bg)
    {
        return $this->setData(self::TITLE_BG, $title_bg);
    }
	
    /**
     * Get button_color
     * @return string|null
     */
    public function getButtonColor()
    {
        return $this->_get(self::BUTTON_COLOR);
    }

    /**
     * Set button_color
     * @param string $button_color
     * @return SlideInterface
     */
    public function setButtonColor($button_color)
    {
        return $this->setData(self::BUTTON_COLOR, $button_color);
    }

    /**
     * Get button_bg
     * @return string|null
     */
    public function getButtonBg()
    {
        return $this->_get(self::BUTTON_BG);
    }

    /**
     * Set button_bg
     * @param string $button_bg
     * @return SlideInterface
     */
    public function setButtonBg($button_bg)
    {
        return $this->setData(self::BUTTON_BG, $button_bg);
    }
	
    /**
     * Get button_color_hover
     * @return string|null
     */
    public function getButtonColorHover()
    {
        return $this->_get(self::BUTTON_COLOR_HOVER);
    }

    /**
     * Set button_color_hover
     * @param string $button_color_hover
     * @return SlideInterface
     */
    public function setButtonColorHover($button_color_hover)
    {
        return $this->setData(self::BUTTON_COLOR_HOVER, $button_color_hover);
    }
	
    /**
     * Get button_bg_hover
     * @return string|null
     */
    public function getButtonBgHover()
    {
        return $this->_get(self::BUTTON_BG_HOVER);
    }

    /**
     * Set button_bg_hover
     * @param string $button_bg_hover
     * @return SlideInterface
     */
    public function setButtonBgHover($button_bg_hover)
    {
        return $this->setData(self::BUTTON_BG_HOVER, $button_bg_hover);
    }
	
	
	
	
    /**
     * Get text_color
     * @return string|null
     */
    public function getTextColor()
    {
        return $this->_get(self::TEXT_COLOR);
    }

    /**
     * Set text_color
     * @param string $text_color
     * @return SlideInterface
     */
    public function setTextColor($text_color)
    {
        return $this->setData(self::TEXT_COLOR, $text_color);
    }
	
    /**
     * Get subtitle
     * @return string|null
     */
    public function getSubtitle()
    {
        return $this->_get(self::SUBTITLE);
    }

    /**
     * Set subtitle
     * @param string $subtitle
     * @return SlideInterface
     */
    public function setSubtitle($subtitle)
    {
        return $this->setData(self::SUBTITLE, $subtitle);
    }
	
    /**
     * Get title
     * @return string|null
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * Set title
     * @param string $title
     * @return SlideInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }
	
    /**
     * Get link
     * @return string|null
     */
    public function getLink()
    {
        return $this->_get(self::LINK);
    }

    /**
     * Set link
     * @param string $link
     * @return SlideInterface
     */
    public function setLink($link)
    {
        return $this->setData(self::LINK, $link);
    }
    /**
     * Get button
     * @return string|null
     */
    public function getButton()
    {
        return $this->_get(self::BUTTON);
    }

    /**
     * Set button
     * @param string $button
     * @return SlideInterface
     */
    public function setButton($button)
    {
        return $this->setData(self::BUTTON, $button);
    }
	
    /**
     * Get nav_title
     * @return string|null
     */
    public function getNavTitle()
    {
        return $this->_get(self::NAV_TITLE);
    }

    /**
     * Set nav_title
     * @param string $nav_title
     * @return SlideInterface
     */
    public function setNavTitle($nav_title)
    {
        return $this->setData(self::NAV_TITLE, $nav_title);
    }
	
    /**
     * Get title_size
     * @return string|null
     */
    public function getTitleSize()
    {
        return $this->_get(self::TITLE_SIZE);
    }
	
    /**
     * Set title_size
     * @param string $title_size
     * @return SlideInterface
     */
    public function setTitleSize($title_size)
    {
        return $this->setData(self::TITLE_SIZE, $title_size);
    }
	
    /**
     * Get button_style
     * @return string|null
     */
    public function getButtonStyle()
    {
        return $this->_get(self::BUTTON_STYLE);
    }
    /**
     * Set button_style
     * @param string $button_style
     * @return SlideInterface
     */
    public function setButtonStyle($button_style)
    {
        return $this->setData(self::BUTTON_STYLE, $button_style);
    }
	
    /**
     * Get content_width
     * @return string|null
     */
    public function getContentWidth()
    {
        return $this->_get(self::CONTENT_WIDTH);
    }
    /**
     * Set content_width
     * @param string $content_width
     * @return SlideInterface
     */
    public function setContentWidth($content_width)
    {
        return $this->setData(self::CONTENT_WIDTH, $content_width);
    }
    /**
     * Get content_wrappers
     * @return string|null
     */
    public function getContentWrappers()
    {
        return $this->_get(self::CONTENT_WRAPPERS);
    }
    /**
     * Set content_wrappers
     * @param string $content_wrappers
     * @return SlideInterface
     */
    public function setContentWrappers($content_wrappers)
    {
        return $this->setData(self::CONTENT_WRAPPERS, $content_wrappers);
    }
	
    /**
     * Get custom_class
     * @return string|null
     */
    public function getCustomClass()
    {
        return $this->_get(self::CUSTOM_CLASS);
    }
    /**
     * Set custom_class
     * @param string $custom_class
     * @return SlideInterface
     */
    public function setCustomClass($custom_class)
    {
        return $this->setData(self::CUSTOM_CLASS, $custom_class);
    }
	
    /**
     * Get content_only
     * @return string|null
     */
    public function getContentOnly()
    {
        return $this->_get(self::CONTENT_ONLY);
    }

    /**
     * Set content_only
     * @param string $content_only
     * @return SlideInterface
     */
    public function setContentOnly($content_only)
    {
        return $this->setData(self::CONTENT_ONLY, $content_only);
    }

    /**
     * Get mobile_align
     * @return string|null
     */
    public function getMobileAlign()
    {
        return $this->_get(self::MOBILE_ALIGN);
    }

    /**
     * Set mobile_align
     * @param string $mobile_align
     * @return SlideInterface
     */
    public function setMobileAlign($mobile_align)
    {
        return $this->setData(self::MOBILE_ALIGN, $mobile_align);
    }
	
    /**
     * Get margins
     * @return string|null
     */
    public function getMargins()
    {
        return $this->_get(self::MARGINS);
    }

    /**
     * Set margins
     * @param string $margins
     * @return SlideInterface
     */
    public function setMargins($margins)
    {
        return $this->setData(self::MARGINS, $margins);
    }
	
    /**
     * Get button_css
     * @return string|null
     */
    public function getButtonCss()
    {
        return $this->_get(self::BUTTON_CSS);
    }

    /**
     * Set button_css
     * @param string $button_css
     * @return SlideInterface
     */
    public function setButtonCss($button_css)
    {
        return $this->setData(self::BUTTON_CSS, $button_css);
    }
	
    /**
     * Get slide_link
     * @return string|null
     */
    public function getSlideLink()
    {
        return $this->_get(self::SLIDE_LINK);
    }

    /**
     * Set slide_link
     * @param string $slide_link
     * @return SlideInterface
     */
    public function setSlideLink($slide_link)
    {
        return $this->setData(self::SLIDE_LINK, $slide_link);
    }
}
