<?php
/**
 * Olegnax Athlete Slideshow
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_AthleteSlideshow
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */


namespace Olegnax\Athlete2Slideshow\Model\Config\Source;

class Fx implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'scrollHorz', 'label' => __('scrollHorz')],['value' => 'scrollVert', 'label' => __('scrollVert')],['value' => 'fade', 'label' => __('fade')]];
    }

    public function toArray()
    {
        return ['scrollHorz' => __('scrollHorz'),'scrollVert' => __('scrollVert'),'fade' => __('fade')];
    }
}
