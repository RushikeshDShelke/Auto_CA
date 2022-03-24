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


namespace Olegnax\Athlete2Slideshow\Helper;

use Olegnax\Core\Helper\Helper as CoreHelper;

class Helper extends CoreHelper
{

    public function getSupportedColors()
    {
        return [
            [
                'value' => 'white',
                'label' => __('white'),
            ],
            [
                'value' => 'black',
                'label' => __('black'),
            ],
            [
                'value' => 'red',
                'label' => __('red'),
            ],
            [
                'value' => 'green',
                'label' => __('green'),
            ],
            [
                'value' => 'blue',
                'label' => __('blue'),
            ],
            [
                'value' => 'yellow',
                'label' => __('yellow'),
            ],
        ];
    }

    public function isRevoulutionActive()
    {
        return $this->isRevoulutionModuleActive() &&
            $this->getConfig('nwdthemes_revslider/revslider_configuration/status');
    }

    public function isRevoulutionModuleActive()
    {
        return $this->_moduleManager->isOutputEnabled('Nwdthemes_Base') &&
            $this->_moduleManager->isOutputEnabled('Nwdthemes_Revslider');
    }

    public function setSlideshowLayout()
    {
        if ($this->isSlideshowEnabled()) {
            return sprintf('html/slideshow/%s.phtml', $this->getConfig('athleteslideshow/general/slider'));
        }
        return 'html/slideshow.phtml';
    }

    public function isSlideshowEnabled()
    {
        $config = $this->getConfig('athleteslideshow');
        $action = $this->_request->getActionName();
        $controller = $this->_request->getControllerName();
        $module = $this->_request->getModuleName();
        $route = $this->_request->getRouteName();
        $show = false;
        if ($config['general']['enabled']) {
            $show = true;
            if ('home' == $config['general']['show']) {
                $show = false;
                if ('cms' == $module && 'index' == $controller && 'index' == $action) {
                    $show = true;
                }
            }
            if ($show && ('customer' == $route && in_array($action, ['create', 'forgotpassword', 'login']))) {
                $show = false;
            }
        }
        return $show;
    }

}
