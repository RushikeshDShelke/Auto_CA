<?php

namespace Nwdthemes\Revslider\Block;

use \Nwdthemes\Revslider\Model\Revslider\RevSliderOutput;

class Revslider extends \Magento\Framework\View\Element\Template {

	protected $_frameworkHelper;
	protected $_pluginHelper;
	protected $_status;
	protected $_content;
    protected $_slider;
    protected $_customerGroupId;
    protected $_output;

	/**
	 * Constructor
	 */

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Nwdthemes\Revslider\Helper\Framework $framework,
		\Nwdthemes\Revslider\Helper\Plugin $pluginHelper,
		array $data = []
	) {
		$this->_frameworkHelper = $framework;
		$this->_pluginHelper = $pluginHelper;

        parent::__construct($context, $data);

        $this->setTemplate('Nwdthemes_Revslider::revslider.phtml');

        $this->_customerGroupId = $customerSession->getCustomer()->getGroupId();
        $this->_status = $this->_scopeConfig->getValue('nwdthemes_revslider/revslider_configuration/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $this->_output = new RevSliderOutput($this->_frameworkHelper);
	}

	protected function _renderSlider() {
		if ( is_null($this->_slider) ) {

            $this->_pluginHelper->loadPlugins($this->_frameworkHelper);

            $settings = $this->getData('settings') ?: '';
            $order = $this->getData('order') ?: '';
            $usage = $this->getData('usage') ?: '';
            $modal = $this->getData('modal') ?: '';
            $layout = $this->getData('layout') ?: '';
            $offset = $this->getData('offset') ?: '';
            $skin = $this->getData('skin') ?: '';
            $zindex = $this->getData('zindex') ?: '';
            $slider_alias = $this->getData('alias') ?: '';

            $this->_output->set_custom_order($order);
            $this->_output->set_custom_settings($settings);
            $this->_output->set_custom_skin($skin);

			ob_start();
			$this->_slider = $this->_output->add_slider_to_stage($slider_alias, $usage, $layout, $offset, $modal);
			$this->_content = ob_get_contents();
			ob_clean();
            ob_end_clean();

            if(!empty($zindex)){
                $this->_content = '<div class="wp-block-themepunch-revslider" style="z-index:'.$zindex.';">' .$this->_content. '</div>';
            }
		}
	}

    /**
     *  Include scritps and styles
     */

    protected function addHeadIncludes() {

        $this->_renderSlider();

        ob_start();
        $content = $this->_frameworkHelper->do_action('wp_footer');
        $content = ob_get_contents();
        ob_clean();
        ob_end_clean();

        // TODO: Check about including this
        //$content .= Mage::helper('nwdrevslider')->includeEnqueuedAssets();

        foreach ($this->_frameworkHelper->getRegisteredStyles() as $_style) {
            $content .= '<link  rel="stylesheet" type="text/css"  media="all" href="' . $_style . '" />';
        }

        $content .= $this->_frameworkHelper->getLocalizeScriptsHtml();

        return $content;
    }

	public function getCacheKeyInfo() {
		$this->_renderSlider();
		$key = parent::getCacheKeyInfo();
		$key[] = $this->getData('alias');
		$key[] = $this->_slider->getParam("disable_on_mobile", "off");
        $key[] = isset($_SERVER['HTTPS']);
        $key[] = $this->_customerGroupId;
        $key[] = $this->_frameworkHelper->getStoreId();
		return $key;
	}

	public function renderSlider() {
		if ($this->_status) {

			$this->_renderSlider();

            if(!empty($this->_slider)) {

                // Customer group permissions
                $useAccessPermissions = $this->_slider->getParam('use_access_permissions', false);
                $allowGroups = $this->_slider->getParam('allow_groups', array());
                if ($useAccessPermissions === 'off'
                    || $useAccessPermissions === false
                    || (isset($allowGroups[0]) && in_array($this->_customerGroupId, $allowGroups))
                    || (isset($allowGroups['group' . $this->_customerGroupId]) && $allowGroups['group' . $this->_customerGroupId])
                ) {

                    // TODO: Move head includes to head block
                    /*$custom_css = RevSliderCssParser::compress_css(RevSliderOperations::getStaticCss());
                    if ($custom_css) {
                        $custom_css = '<style type="text/css">' . $custom_css . '</style>';
                    }*/

                    $this->_content = $this->addHeadIncludes() . /*$custom_css . */$this->_content;

                    $show_alternate = $this->_slider->getParam("show_alternative_type","off");
                    if($show_alternate == 'mobile' || $show_alternate == 'mobile-ie8'){
                        if(strstr($_SERVER['HTTP_USER_AGENT'],'Android') || strstr($_SERVER['HTTP_USER_AGENT'],'webOS') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone') ||strstr($_SERVER['HTTP_USER_AGENT'],'iPod') || strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || strstr($_SERVER['HTTP_USER_AGENT'],'Windows Phone') || $this->_frameworkHelper->wp_is_mobile()){
                            $show_alternate_image = $this->_slider->getParam("show_alternate_image","");
                            $this->_content = '<img class="tp-slider-alternative-image" src="'.$show_alternate_image.'" data-no-retina>';
                        }
                    }

                } else {
                    $this->_content = '';
                }
			}
		}
		return $this->_content;
	}

}
