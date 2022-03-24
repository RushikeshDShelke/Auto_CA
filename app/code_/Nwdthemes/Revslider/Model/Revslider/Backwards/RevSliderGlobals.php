<?php

namespace Nwdthemes\Revslider\Model\Revslider\Backwards;

use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront;

class RevSliderGlobals {
	const SLIDER_REVISION = Framework::RS_REVISION;
	const TABLE_SLIDERS_NAME = RevSliderFront::TABLE_SLIDER;
	const TABLE_SLIDES_NAME = RevSliderFront::TABLE_SLIDES;
	const TABLE_STATIC_SLIDES_NAME = RevSliderFront::TABLE_STATIC_SLIDES;
	const TABLE_SETTINGS_NAME = RevSliderFront::TABLE_SETTINGS;
	const TABLE_CSS_NAME = RevSliderFront::TABLE_CSS;
	const TABLE_LAYER_ANIMS_NAME = RevSliderFront::TABLE_LAYER_ANIMATIONS;
	const TABLE_NAVIGATION_NAME = RevSliderFront::TABLE_NAVIGATIONS;
	public static $table_sliders = RevSliderFront::TABLE_SLIDER;
	public static $table_slides = RevSliderFront::TABLE_SLIDES;
	public static $table_static_slides = RevSliderFront::TABLE_STATIC_SLIDES;
}