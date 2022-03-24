<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2020 ThemePunch
 * @since	  6.2.0
 */

namespace Nwdthemes\Revslider\Model\Revslider\Admin\Includes;

use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderFunctions;

class RevSliderLicense extends RevSliderFunctions {
	/**
	 * Activate the Plugin through the ThemePunch Servers
	 * @before 6.0.0: RevSliderOperations::checkPurchaseVerification();
	 * @before 6.2.0: RevSliderAdmin::activate_plugin();
	 **/
	public function activate_plugin($code){
		$rslb = new RevSliderLoadBalancer($this->_frameworkHelper);
		$data = array('code' => urlencode($code), 'version'	=> urlencode(Framework::RS_REVISION), 'product' => urlencode(Framework::$RS_PLUGIN_SLUG));

		$response	  = $rslb->call_url('activate.php', $data, 'updates');
		$version_info = $this->_frameworkHelper->wp_remote_retrieve_body($response);

		if($this->_frameworkHelper->is_wp_error($version_info)) return false;

		if($version_info == 'valid'){
			$this->_frameworkHelper->update_option('revslider-valid', 'true');
			$this->_frameworkHelper->update_option('revslider-code', $code);
			return true;
		}elseif($version_info == 'exist'){
			return 'exist';
		}elseif($version_info == 'banned'){
			return 'banned';
		}

		return false;
	}


	/**
	 * Deactivate the Plugin through the ThemePunch Servers
	 * @before 6.0.0: RevSliderOperations::doPurchaseDeactivation();
	 * @before 6.2.0: RevSliderAdmin::deactivate_plugin();
	 **/
	public function deactivate_plugin(){
		$rslb = new RevSliderLoadBalancer($this->_frameworkHelper);
		$code = $this->_frameworkHelper->get_option('revslider-code', '');
		$data = array('code' => urlencode($code), 'product' => urlencode(Framework::$RS_PLUGIN_SLUG));

		$res = $rslb->call_url('deactivate.php', $data, 'updates');
		$vi	 = $this->_frameworkHelper->wp_remote_retrieve_body($res);

		if($this->_frameworkHelper->is_wp_error($vi)) return false;

		if($vi == 'valid'){
			$this->_frameworkHelper->update_option('revslider-valid', 'false');
			$this->_frameworkHelper->update_option('revslider-code', '');

			return true;
		}

		return false;
	}
}
