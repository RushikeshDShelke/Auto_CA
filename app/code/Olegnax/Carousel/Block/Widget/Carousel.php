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

namespace Olegnax\Carousel\Block\Widget;

use Magento\Customer\Model\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Olegnax\Carousel\Model\CarouselFactory;
use Olegnax\Carousel\Model\ResourceModel\Carousel\CollectionFactory;


class Carousel extends Template implements BlockInterface
{

    protected $_template = "widget/carousel.phtml";

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var \Olegnax\Carousel\Model\Carousel
     */
    protected $carouselCollection;

    /**
     * Carousel constructor.
     * @param Template\Context $context
     * @param CarouselFactory $carouselFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $carouselFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        Json $json,
        array $data = []
    ) {
        $this->carouselCollection = $carouselFactory->create();
        $this->httpContext = $httpContext;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * @param array $newval
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCacheKeyInfo($newval = [])
    {
        return array_merge([
            'OLEGNAX_CAROUSEL_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            $this->json->serialize($this->getData()),
        ], parent::getCacheKeyInfo(), $newval);
    }

    /**
     * @return string
     */
    public function getCarouselId()
    {
        return 'ox_carousel_' . substr(md5(microtime()), -5);
    }

    public function getSlide($store_id = null)
    {
        $carousel = $this->getCarousel();
        if ($carousel) {
            return $this->getCarousel()->getSlide($store_id);
        }

        return [];
    }

    /**
     * @return \Olegnax\Carousel\Model\Carousel
     */
    public function getCarousel()
    {
        $carousel = $this->getData('current_carousel');
        if (empty($carousel)) {
            $carousels = $this->carouselCollection
                ->addFieldToSelect('*')
                ->addFieldToFilter('identifier', $this->getData('carousel'));
            if ($carousels->getSize()) {
                foreach ($carousels as $carousel) {
                    $this->setData('current_carousel', $carousel);
                    break;
                }
            }
        }

        return $carousel;
    }

    protected function _construct()
    {
        $this->addData([
            'cache_lifetime' => 86400,
        ]);
        if (!$this->hasData('template') && !$this->getTemplate()) {
            $this->setTemplate('Olegnax_Carousel::widget/carousel.phtml');
        }
        parent::_construct();
    }
	
	public function getAutoScroll() {
		$auto_scroll = $this->getData( 'autoplay' );
		if ( empty( $auto_scroll ) ) {
			$auto_scroll = 0;
		}

		return $auto_scroll;
	}
	
	public function getResponsive( $to_string = true ) {
        $responsive = [
            '0' => [
                'items' => max(1, (int)$this->getColumnsMobile()),
            ],
            '768' => [
                'items' => max(1, (int)$this->getColumnsTablet()),
            ],
            '1025' => [
                'items' => max(1, (int)$this->getColumnsDesktopSmall()),
            ],
            '1160' => [
                'items' => max(1, (int)$this->getColumnsDesktop()),
            ],
        ];
		if ( $to_string ) {
			return json_encode( $responsive );
		}

		return $responsive;
	}

	public function prepareStyle( array $style, string $separatorValue = ': ', string $separatorAttribute = ';' ) {
		$style = array_filter( $style );
		if ( empty( $style ) ) {
			return '';
		}
		foreach ( $style as $key => &$value ) {
			$value = $key . $separatorValue . $value;
		}
		$style = implode( $separatorAttribute, $style );

		return $style;
	}
	public function customStyles( $slider_id, $slide ) {
		$styles = array();
		if(!empty($slide)) {
			if (!empty($slide->getData('subtitle_color'))) { $styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id').' .subtitle{ color:' .$slide->getData('subtitle_color') . '}'; }
			if (!empty($slide->getData('title_color'))) { $styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id').' .title{ color:' .$slide->getData('title_color') . '}'; }
			if (!empty($slide->getData('title_bg'))) { $styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' .title{ background-color:' . $slide->getData('title_bg') . '}'; }
			if (!empty($slide->getData('button_color'))) { 
				$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' a.ox-slide__button{ color:' . $slide->getData('button_color') . '}'; 
			}
			if (!empty($slide->getData('button_color_hover'))) { 
				$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id').' a.ox-slide__button:hover{ color:' .$slide->getData('button_color_hover') . '}'; 
			}
			if (!empty($slide->getData('button_bg_hover'))) { 
				if($slide->getData('button_style') == 'underline'){
					$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' a.ox-slide__button.underline:hover{ border-color:' . $slide->getData('button_bg_hover') . '}'; 
				} elseif($slide->getData('button_style') == 'outline'){
					$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' a.ox-slide__button.outline:hover{ border-color:' . $slide->getData('button_bg_hover') . '}';
					$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' a.ox-slide__button:after{ background-color:' . $slide->getData('button_bg_hover') . '}'; 
				} else {
					$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' a.ox-slide__button:after{ background-color:' . $slide->getData('button_bg_hover') . '}'; 
				}
			}
			if (!empty($slide->getData('button_bg'))) { 
				if($slide->getData('button_style') == 'underline'){
					$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' a.ox-slide__button.underline{ border-color:' . $slide->getData('button_bg') . '}'; 
				} elseif($slide->getData('button_style') == 'outline'){
					$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' a.ox-slide__button.outline{ border-color:' . $slide->getData('button_bg') . '}'; 
					$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' a.ox-slide__button{ background-color:' . $slide->getData('button_bg') . '}'; 
				} else {
					$styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' a.ox-slide__button{ background-color:' . $slide->getData('button_bg') . '}'; 
				}
			}
			if (!empty($slide->getData('content_width'))) {
			    $styles[] =  '#' . $slider_id. ' .slide' . $slide->getData('slide_id').'.slide-content-2-col .ox-carousel__col:first-child{' .
                    'max-width:' .$slide->getData('content_width') . 'px;' .
                '}' .
			    '#' . $slider_id. ' .slide' . $slide->getData('slide_id').' .ox-carousel__inner{ max-width:' .$slide->getData('content_width') . 'px }';
			}
			if (!empty($slide->getData('text_color'))) { $styles[] = '#' . $slider_id. ' .slide' . $slide->getData('slide_id') .' .ox-carousel__content{ color:' . $slide->getData('text_color') . '}'; }
		}
		if ( !empty($styles) ){
			return implode(' ', $styles);
		}
	}
	public function getBaseUrl() {
		$this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
	}
}