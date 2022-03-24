<?php

namespace Olegnax\Athlete2\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Athlete2\Helper\Helper;

class Move implements ObserverInterface
{
    protected $scopeConfig;
    /**
     *
     * @var ObjectManager
     */
    public $objectManager;
    /**
     * @var Helper
     */
    private $helper;
    public function __construct(
		Helper $helper,
        ScopeConfigInterface $scopeConfig
    )
    {
		$this->objectManager = ObjectManager::getInstance();
        $this->scopeConfig = $scopeConfig;
		$this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        if (!$this->getConfig(Helper::XML_ENABLED)) {
            return;
        }
        $layout = $observer->getData('layout');
		/* move minicart in header 1*/
		$headerLayout = $this->getConfig('athlete2_settings/header/header_layout');
		if($headerLayout == 'header_1'){
			$layout->getUpdate()->addHandle('olegnax_athlete2_header_move_minicart');
		}
        $fullActionName = $observer->getData('full_action_name');
		
		$account_action_drop = $this->getConfig('athlete2_settings/header/account_action');
		if($account_action_drop == 'login' && $fullActionName !='customer_account_login' && $fullActionName !='multishipping_checkout_login'){
			if(	version_compare($this->helper->getVersion(), '2.4.0', '>' ) ){
				$layout->getUpdate()->addHandle('olegnax_athlete2_login_recaptcha');
            } elseif (version_compare($this->helper->getVersion(), '2.3.1', '=')) {
                $layout->getUpdate()->addHandle('olegnax_athlete2_login_recaptcha_msp_231');
            } else {
                $layout->getUpdate()->addHandle('olegnax_athlete2_login_recaptcha_msp');
            }
		}
		if(	version_compare($this->helper->getVersion(), '2.3.6', '>' ) && version_compare($this->helper->getVersion(), '2.4.0', '!=' ) ){
			$layout->getUpdate()->addHandle('olegnax_athlete2_header_search_args');
		}

		 if ($fullActionName == 'catalog_category_view' || $fullActionName == 'catalogsearch_result_index' ) {
			if($this->getConfig('athlete2_settings/products_listing/move_cat_title')){
				$layout->getUpdate()->addHandle('olegnax_athlete2_category_move_title');
			}
			if($this->getConfig('athlete2_settings/products_listing/move_cat_cms_block')){
				$layout->getUpdate()->addHandle('olegnax_athlete2_category_move_cms_block');
			}
			/*
			if($this->getConfig('athlete2_settings/products_listing/move_breadrumbs')){
				$layout->getUpdate()->addHandle('olegnax_athlete2_category_move_breadcrumbs');
			}
			if($this->getConfig('athlete2_settings/products_listing/move_image')){
				$layout->getUpdate()->addHandle('olegnax_athlete2_category_move_image');
			}
			if($this->getConfig('athlete2_settings/products_listing/move_desc')){
				$layout->getUpdate()->addHandle('olegnax_athlete2_category_move_desc');
			}*/
		 }
		 if ($fullActionName == 'sales_order_print' ) {
			$layout->getUpdate()->addHandle('olegnax_remove_newsletter');
		 }
		 if (!in_array($fullActionName, ['catalog_product_view', 'ox_quickview_catalog_product_view'])) {
			 return $this;			 
		 }
			/* move reviews */
			$reviewsInTab = $this->getConfig('athlete2_settings/product/reviews_position');
			if ($reviewsInTab) {
				if($reviewsInTab == 'oxbottom'){
					$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_view_review_oxbottom');
				} elseif($reviewsInTab == 'bottom'){
					$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_view_review_bottom');
				} elseif($reviewsInTab == 'gallery'){
					$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_view_review_gallery');
				}
			} else{
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_view_move_review');
			}
		
		$tabsInInfo = $this->getConfig('athlete2_settings/product/product_tabs_position');
		/*if($fullActionName == 'ox_quickview_catalog_product_view' && $tabsInInfo == 'info'){
			$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_tabs_remove');
		}*/

			
			if ($tabsInInfo == 'info') {
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_tabs_right');
			} 
			if($tabsInInfo == 'oxbottom'){
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_tabs_oxbottom');
			} 
			if($tabsInInfo == 'bottom'){
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_tabs_bottom');
			}
			if($tabsInInfo == 'gallery'){
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_tabs_gallery');
			}
			/* move related */
			$moveRelated = $this->getConfig('athlete2_settings/product/related_positon');
			$moveUpsell  = $this->getConfig('athlete2_settings/product/upsell_positon');
			if ($moveRelated == 'oxbottom') {
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_related_oxbottom');
			} elseif($moveRelated == 'bottom'){
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_related_bottom');
			} elseif($moveRelated == 'gallery'){
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_related_gallery');
			}
			if ($moveUpsell == 'oxbottom') {
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_upsell_oxbottom');
			} elseif($moveUpsell == 'bottom'){
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_upsell_bottom');
			} elseif($moveUpsell == 'gallery'){
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_upsell_gallery');
			}
			/* sticky product, move elements in sticky wrapper */
			$galleryLayout  = $this->getConfig('athlete2_settings/product/gallery_layout');
			$stickyDesc  = $this->getConfig('athlete2_settings/product/gallery_sticky');
			$infoWrapper  = $this->getConfig('athlete2_settings/product/gallery_wrapper');
			if(($stickyDesc && ($galleryLayout == '1col' || $galleryLayout == '2cols')) || $infoWrapper){
				$layout->getUpdate()->addHandle('olegnax_athlete2_catalog_product_info_wrapper');
			}
			/* replace fotorama with css only gallery for mobile theme */
			if( $this->getConfig('athlete2_settings/product/css_only_gallery')){
				if($this->objectManager->get( 'Olegnax\Core\Helper\Helper' )->isMobileTheme()){
					$layout->getUpdate()->addHandle('olegnax_athlete2_css_only_gallery');
					$layout->getUpdate()->addHandle('olegnax_athlete2_remove_fotorama_video');
				}
			}
			
			if($fullActionName == 'catalog_product_view' && ( $galleryLayout == '1col' || $galleryLayout == '2cols')){
				/* remove fotorama video if fotorama disabled */
				$layout->getUpdate()->addHandle('olegnax_athlete2_remove_fotorama_video');
				/* set product gallery layout */
				$layout->getUpdate()->addHandle('olegnax_athlete2_product_gallery_layout');
			}
        return $this;
    }

    public function getConfig($path, $storeCode = null)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }
}
