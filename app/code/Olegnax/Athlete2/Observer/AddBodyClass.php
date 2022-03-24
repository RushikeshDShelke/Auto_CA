<?php

namespace Olegnax\Athlete2\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Page\Config;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Athlete2\Helper\Helper;

class AddBodyClass implements ObserverInterface
{
    /**
     * @var \Olegnax\Athlete2\Helper\Helper
     */
    protected $athleteHelper;
    protected $config;
    protected $scopeConfig;

    public function __construct(
        Config $config,
		Helper $helper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
		$this->athleteHelper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        if (!$this->getConfig(Helper::XML_ENABLED)) {
            return;
        }
        $class = [];
        if (1 == $this->getConfig('athlete2_design/appearance_toolbar/toolbar_nobg')) {
            $class[] = 'toolbar-nobg';
        }		
        if (1 == $this->getConfig('athlete2_design/appearance_sidebar/sidebar_nobg')) {
            $class[] = 'sidebar-nobg';
        }
        if (1 == $this->getConfig('athlete2_settings/product/gallery_zoom_cursor')) {
            $class[] = 'custom-gallery-cursor';
        }		
        if (1 == $this->athleteHelper->isLazyLoadEnabled()) {
            $class[] = 'ox-lazy';
        }
        if (1 == $this->getConfig('athlete2_settings/general/messages_fixed')) {
            $class[] = 'ox-messages-fixed';
        }
		if ('2' == $this->getConfig('athlete2_settings/product/actions_position')){
			$count = 0;
			if( 1 == $this->getConfig('athlete2_settings/product/compare_disable')){
				$count++;
			}
			if(1 == $this->getConfig('athlete2_settings/product/wishlist_disable')){
				$count++;				
			}
			if($count == 1){
				$class[] = 'ox-quickview-sa';
			}
		}
		$class[] = $this->getConfig('athlete2_settings/header/menu_hover_style');
        $class[] = 'menu--align-' . $this->getConfig('athlete2_settings/header/menu_align');
        if(!empty($this->getConfig('athlete2_settings/header/menu_align_sticky'))) {
            $class[] = 'menu--align-sticky-' . $this->getConfig('athlete2_settings/header/menu_align_sticky');
        }
        $class[] = 'minicart--style-' . $this->getConfig('athlete2_settings/header/minicart_style');
        $class[] = 'mobile-header--layout-' . $this->getConfig('athlete2_settings/mobile_header/mobile_header_layout');
        $class[] = 'footer--layout-' . $this->getConfig('athlete2_settings/footer/footer_layout');
        $header_layout = $this->getConfig('athlete2_settings/header/header_layout');
        $class[] = 'header--layout-' . $header_layout[-1];

        if (1 == $this->getConfig('athlete2_settings/contacts_page/contacts_page_fullwidth')) {
            $class[] = 'contacts-fullwidth';
        }
        if (1 == $this->getConfig('athlete2_settings/products_listing/grid_fullwidth')) {
            $class[] = 'categories--fullwidth';
        }
        if (1 == $this->getConfig('athlete2_settings/product/product_fullwidth')) {
            $class[] = 'product-page--fullwidth';
        }
        if ( $this->getConfig('athlete2_settings/product/product_tabs_style')) {
            $class[] = 'tabs-style--' . $this->getConfig('athlete2_settings/product/product_tabs_style');
        }
        if (2 == $this->getConfig('athlete2_settings/header/menu_position')) {
            $class[] = 'menu-position--below';
        }
        if (1 == $this->getConfig('athlete2_settings/header/minicart_btn_minimal')) {
            $class[] = 'minicart-btn--minimal';
        }
        if (1 == $this->getConfig('athlete2_settings/header/minicart_counter_mobile')) {
            $class[] = 'minicart--show-counter-mobile';
        }
        if (1 == $this->getConfig('athlete2_settings/header/minicart_btn_hide_icon') && !$this->getConfig('athlete2_settings/header/minicart_btn_minimal')) {
            $class[] = 'minicart-btn__icon--hide';
        }
        if ('main' != $this->getConfig('athlete2_settings/mobile_header/wishlist_mobile_position') && '' != $this->getConfig('athlete2_settings/mobile_header/wishlist_mobile_position')) {
            $class[] = 'mobile-header__wishlist--hide';
        }
        if ('main' != $this->getConfig('athlete2_settings/mobile_header/compare_mobile_position') && '' != $this->getConfig('athlete2_settings/mobile_header/compare_mobile_position')) {
            $class[] = 'mobile-header__compare--hide';
        }
        if ($this->getConfig('athlete2_settings/header/sticky_header') && $this->getConfig('athlete2_settings/header/sticky_header_smart')) {
            $class[] = 'sticky-smart';
        } else {
            $class[] = 'sticky-simple';
        }
        if ($this->getConfig('athlete2_settings/header/sticky_header') && $this->getConfig('athlete2_settings/header/sticky_header_minimized')) {
            $class[] = 'sticky-minimized';
        }
        if ($this->getConfig('athlete2_design/appearance_general/inputs_underlined')) {
            $class[] = 'inputs-style--underlined';
        }
        if ($this->getConfig('athlete2_settings/products_listing/quickview_mobile')) {
            $class[] = 'quickview-mobile--hide';
        }

        foreach ($class as $_class) {
            $this->addBodyClass($_class);
        }

    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    public function addBodyClass($className)
    {
        $className = strtolower($className);
        $bodyClasses = $this->config->getElementAttribute(Config::ELEMENT_TYPE_BODY, Config::BODY_ATTRIBUTE_CLASS);
        $bodyClasses = $bodyClasses ? explode(' ', $bodyClasses) : [];
        $bodyClasses[] = $className;
        $bodyClasses = array_unique($bodyClasses);
        $this->config->setElementAttribute(
            Config::ELEMENT_TYPE_BODY,
            Config::BODY_ATTRIBUTE_CLASS,
            implode(' ', $bodyClasses)
        );
        return $this;
    }
}
