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

class Easing implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => 'easeOutQuad', 'label' => __('easeOutQuad')],['value' => 'easeInQuad', 'label' => __('easeInQuad')],['value' => 'easeInOutQuad', 'label' => __('easeInOutQuad')],['value' => 'easeInCubic', 'label' => __('easeInCubic')],['value' => 'easeOutCubic', 'label' => __('easeOutCubic')],['value' => 'easeInOutCubic', 'label' => __('easeInOutCubic')],['value' => 'easeInQuart', 'label' => __('easeInQuart')],['value' => 'easeOutQuart', 'label' => __('easeOutQuart')],['value' => 'easeInOutQuart', 'label' => __('easeInOutQuart')],['value' => 'easeInQuint', 'label' => __('easeInQuint')],['value' => 'easeOutQuint', 'label' => __('easeOutQuint')],['value' => 'easeInOutQuint', 'label' => __('easeInOutQuint')],['value' => 'easeInSine', 'label' => __('easeInSine')],['value' => 'easeOutSine', 'label' => __('easeOutSine')],['value' => 'easeInOutSine', 'label' => __('easeInOutSine')],['value' => 'easeInExpo', 'label' => __('easeInExpo')],['value' => 'easeOutExpo', 'label' => __('easeOutExpo')],['value' => 'easeInOutExpo', 'label' => __('easeInOutExpo')],['value' => 'easeInCirc', 'label' => __('easeInCirc')],['value' => 'easeOutCirc', 'label' => __('easeOutCirc')],['value' => 'easeInOutCirc', 'label' => __('easeInOutCirc')],['value' => 'easeInElastic', 'label' => __('easeInElastic')],['value' => 'easeOutElastic', 'label' => __('easeOutElastic')],['value' => 'easeInOutElastic', 'label' => __('easeInOutElastic')],['value' => 'easeInBack', 'label' => __('easeInBack')],['value' => 'easeOutBack', 'label' => __('easeOutBack')],['value' => 'easeInOutBack', 'label' => __('easeInOutBack')],['value' => 'easeInBounce', 'label' => __('easeInBounce')],['value' => 'easeOutBounce', 'label' => __('easeOutBounce')],['value' => 'easeInOutBounce', 'label' => __('easeInOutBounce')]];
    }

    public function toArray()
    {
        return ['easeOutQuad' => __('easeOutQuad'),'easeInQuad' => __('easeInQuad'),'easeInOutQuad' => __('easeInOutQuad'),'easeInCubic' => __('easeInCubic'),'easeOutCubic' => __('easeOutCubic'),'easeInOutCubic' => __('easeInOutCubic'),'easeInQuart' => __('easeInQuart'),'easeOutQuart' => __('easeOutQuart'),'easeInOutQuart' => __('easeInOutQuart'),'easeInQuint' => __('easeInQuint'),'easeOutQuint' => __('easeOutQuint'),'easeInOutQuint' => __('easeInOutQuint'),'easeInSine' => __('easeInSine'),'easeOutSine' => __('easeOutSine'),'easeInOutSine' => __('easeInOutSine'),'easeInExpo' => __('easeInExpo'),'easeOutExpo' => __('easeOutExpo'),'easeInOutExpo' => __('easeInOutExpo'),'easeInCirc' => __('easeInCirc'),'easeOutCirc' => __('easeOutCirc'),'easeInOutCirc' => __('easeInOutCirc'),'easeInElastic' => __('easeInElastic'),'easeOutElastic' => __('easeOutElastic'),'easeInOutElastic' => __('easeInOutElastic'),'easeInBack' => __('easeInBack'),'easeOutBack' => __('easeOutBack'),'easeInOutBack' => __('easeInOutBack'),'easeInBounce' => __('easeInBounce'),'easeOutBounce' => __('easeOutBounce'),'easeInOutBounce' => __('easeInOutBounce')];
    }
}
