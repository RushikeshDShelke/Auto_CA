<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2015 ThemePunch
 */

namespace Nwdthemes\Revslider\Model\Revslider\ExternalSources;

use \Nwdthemes\Revslider\Model\Revslider\RevSliderFunctions;

/**
 * Facebook
 *
 * with help of the API this class delivers album images from Facebook
 *
 * @package    socialstreams
 * @subpackage socialstreams/facebook
 * @author     ThemePunch <info@themepunch.com>
 */

class RevSliderFacebook extends RevSliderFunctions {
	/**
	 * Stream Array
	 *
	 * @since	1.0.0
	 * @access	private
	 * @var		array	$stream		Stream Data Array
	 */
	private $stream;

	/**
	* Transient seconds
	*
	* @since	1.0.0
	* @access	private
	* @var		number	$transient	Transient time in seconds
	*/
	private $transient_sec;

	public function __construct(
		\Nwdthemes\Revslider\Helper\Framework $frameworkHelper,
		$transient_sec = 1200
    ) {
		parent::__construct($frameworkHelper);
		$this->transient_sec = 	$transient_sec;
	}

	/**
	 * Get User ID from its URL
	 *
	 * @since	1.0.0
	 * @param	string	$user_url URL of the Page
	 */
	public function get_user_from_url($user_url){
		$theid = str_replace('https', '', $user_url);
		$theid = str_replace(array('https', 'http', '://', 'www.', 'facebook', '.com', "/"), '', $user_url);
		$theid = explode('?', $theid);

		return trim($theid[0]);
	}

	/**
	 * Get Photosets List from User
	 *
	 * @since	1.0.0
	 * @param	string	$user_id 	Facebook User id (not name)
	 * @param	int	$item_count 	number of photos to pull
	 */
	public function get_photo_sets($user_id, $item_count = 10, $access_token){
		//photoset params
		$url = "https://graph.facebook.com/$user_id/albums?access_token=" . $access_token;
		$photo_sets_list = json_decode($this->_frameworkHelper->wp_remote_fopen($url));
		if(!empty($photo_sets_list->error->message)){
			return array("error",$photo_sets_list->error->message);
		}
		return $this->get_val($photo_sets_list, 'data');
	}

	/**
	 * Get Photoset Photos
	 *
	 * @since	5.1.1
	 * @param	string	$photo_set_id 	Photoset ID
	 * @param	int		$item_count 	number of photos to pull
	 */
	public function get_photo_set_photos($photo_set_id, $item_count = 10, $access_token){
		$url = "https://graph.facebook.com/".$photo_set_id."/photos?fields=photos&access_token=" . $access_token ."&fields=id,from,message,picture,images,link,name,icon,privacy,type,status_type,application,created_time,updated_time,is_hidden,is_expired,comments.limit(1).summary(true),likes.limit(1).summary(true)";

		$transient_name = 'revslider_' . md5($url);

		if($this->transient_sec > 0 && false !== ($data = $this->_frameworkHelper->get_transient($transient_name))){
			return $data;
		}

		$photo_set_photos = json_decode($this->_frameworkHelper->wp_remote_fopen($url));

		$data = $this->get_val($photo_set_photos, 'data');
		if($data !== ''){
			$this->_frameworkHelper->set_transient($transient_name, $data, $this->transient_sec);
		}

		return $data;
	}

	/**
	 * Get Photosets List from User as Options for Selectbox
	 *
	 * @since	1.0.0
	 * @param	string	$user_url 	Facebook User id (not name)
	 * @param	int	$item_count 	number of photos to pull
	 */
	public function get_photo_set_photos_options($user_url, $current_album, $access_token, $item_count = 99){
		$user_id = $this->get_user_from_url($user_url);
		$photo_sets = $this->get_photo_sets($user_id, 999, $access_token);

		if(isset( $photo_sets[0] ) && $photo_sets[0] == "error"){
			return $photo_sets;
		}

		if(empty($current_album)) $current_album = '';

		$return = array();
		if(is_array($photo_sets)){
			foreach($photo_sets as $photo_set){
				$return[] = '<option title="'.$this->get_val($photo_set, 'name').'" '.$this->_frameworkHelper->selected($this->get_val($photo_set, 'id'), $current_album, false).' value="'.$this->get_val($photo_set, 'id').'">'.$this->get_val($photo_set, 'name').'</option>"';
			}
		}
		return $return;
	}


	/**
	 * Get Feed
	 *
	 * @since	1.0.0
	 * @param	string	$user 	User ID
	 * @param	int		$item_count 	number of itmes to pull
	 */
	public function get_photo_feed($user, $access_token, $item_count = 10){
		$url = "https://graph.facebook.com/$user/feed?access_token=" . $access_token ."&fields=full_picture,picture,attachments{media,media_type,url},icon,message,likes.limit(1).summary(true),comments.limit(1).summary(true)";

		$transient_name = 'revslider_' . md5($url);
		if($this->transient_sec > 0 && false !== ($data = $this->_frameworkHelper->get_transient($transient_name))){
			return $data;
		}

		$feed = json_decode($this->_frameworkHelper->wp_remote_fopen($url));

		$data = $this->get_val($feed, 'data');
		if($data !== ''){
			$this->_frameworkHelper->set_transient($transient_name, $data, $this->transient_sec);
		}

		return $data;
	}

	/**
	 * Decode URL from feed
	 *
	 * @since	1.0.0
	 * @param	string	$url 	facebook Output Data
	 */
	private function decode_facebook_url($url){
		$url = str_replace('u00253A', ':', $url);
		$url = str_replace('\u00255C\u00252F', '/', $url);
		$url = str_replace('u00252F', '/', $url);
		$url = str_replace('u00253F', '?', $url);
		$url = str_replace('u00253D', '=', $url);
		$url = str_replace('u002526', '&', $url);

		return $url;
	}
}