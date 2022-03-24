<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2015 ThemePunch
 */

namespace Nwdthemes\Revslider\Model\Revslider\ExternalSources;

use \Nwdthemes\Revslider\Model\Revslider\RevSliderFunctions;

/**
 * Vimeo
 *
 * with help of the API this class delivers all kind of Images/Videos from Vimeo
 *
 * @package    socialstreams
 * @subpackage socialstreams/vimeo
 * @author     ThemePunch <info@themepunch.com>
 */

class RevSliderVimeo extends RevSliderFunctions {
	/**
	 * Stream Array
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $stream    Stream Data Array
	 */
	private $stream;

	/**
	* Transient seconds
	*
	* @since    1.0.0
	* @access   private
	* @var      number    $transient Transient time in seconds
	*/
	private $transient_sec;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $api_key	Youtube API key.
	 */
	public function __construct(
		\Nwdthemes\Revslider\Helper\Framework $frameworkHelper,
		$transient_sec = 1200
    ) {
		parent::__construct($frameworkHelper);
		$this->transient_sec = 	$transient_sec;
	}

	/**
	 * Get Vimeo User Videos
	 *
	 * @since    1.0.0
	 */
	public function get_vimeo_videos($type, $value){
		//call the API and decode the response
		$url = 'https://vimeo.com/api/v2/';
		$url .= ($type == 'user') ? $value.'/videos.json' : $type.'/'.$value.'/videos.json';

		$transient_name = 'revslider_' . md5($url);

		if($this->transient_sec > 0 && false !== ($data = $this->_frameworkHelper->get_transient($transient_name)))
			return ($data);

		$rsp = json_decode($this->_frameworkHelper->wp_remote_fopen($url));
		$this->_frameworkHelper->set_transient($transient_name, $rsp, $this->transient_sec);

		return $rsp;
	}
}