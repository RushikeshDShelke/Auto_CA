<?php
/**
 * Remove Title and Breadcrumbs from blog page
 * 
 * @category    Olegnax
 * @package     Olegnax_Athlete2
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */
namespace Olegnax\Athlete2\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use \Magento\Store\Model\ScopeInterface;
use Olegnax\Athlete2\Helper\Helper;

class ModifyBlogLayout implements ObserverInterface {

	protected $config;
	protected $scopeConfig;

	public function __construct(
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
		$this->scopeConfig = $scopeConfig;
	}

	public function getConfig( $path, $storeCode = null ) {
		return $this->scopeConfig->getValue( $path, ScopeInterface::SCOPE_STORE, $storeCode );
	}

	public function execute( Observer $observer ) {
        if (!$this->getConfig(Helper::XML_ENABLED)) {
            return;
        }
		$layout				 = $observer->getLayout();
		$full_action_name	 = $observer->getFullActionName();
		if ( in_array( $full_action_name, [ 'blog_index_index' ] ) ) {
			$valueBreadcrumbs	 = $this->getConfig( 'athlete2_settings/blog/hide_breadcrumbs' );
			$valueTitle			 = $this->getConfig( 'athlete2_settings/blog/hide_title' ); 

			if ( $valueBreadcrumbs ) {
				$this->removeBlock( 'breadcrumbs', $layout );
			}
			if ( $valueTitle ) {
				$this->removeBlock( 'page.main.title', $layout );
			}
		}
	}

	protected function removeBlock( $name, $layout ) {
		$block = $layout->getBlock( $name );
		if ( $block ) {
			$layout->unsetElement( $name );
			$layout->removeOutputElement( $name );
		}
	}

}
