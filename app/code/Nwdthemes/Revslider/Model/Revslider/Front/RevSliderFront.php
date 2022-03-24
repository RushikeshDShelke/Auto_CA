<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2019 ThemePunch
 */

namespace Nwdthemes\Revslider\Model\Revslider\Front;

use \Nwdthemes\Revslider\Helper\Framework;
use \Nwdthemes\Revslider\Helper\Query;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderFunctions;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderCssParser;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderOutput;
use \Nwdthemes\Revslider\Model\Revslider\RevSliderSlider;

class RevSliderFront extends RevSliderFunctions {

	const TABLE_BACKUP			 = 'nwdthemes_revslider_backup';
	const TABLE_SLIDER			 = 'nwdthemes_revslider_sliders';
	const TABLE_SLIDES			 = 'nwdthemes_revslider_slides';
	const TABLE_STATIC_SLIDES	 = 'nwdthemes_revslider_static_slides';
	const TABLE_CSS				 = 'nwdthemes_revslider_css';
	const TABLE_LAYER_ANIMATIONS = 'nwdthemes_revslider_animations';
	const TABLE_NAVIGATIONS		 = 'nwdthemes_revslider_navigations';
	const TABLE_SETTINGS		 = 'nwdthemes_revslider_settings'; //existed prior 5.0 and still needed for updating from 4.x to any version after 5.x
	const CURRENT_TABLE_VERSION	 = '1.0.8';

	const YOUTUBE_ARGUMENTS		 = 'hd=1&amp;wmode=opaque&amp;showinfo=0&amp;rel=0';
	const VIMEO_ARGUMENTS		 = 'title=0&amp;byline=0&amp;portrait=0&amp;api=1';

	public function __construct(
		\Nwdthemes\Revslider\Helper\Framework $frameworkHelper
	) {
		parent::__construct($frameworkHelper);

		$this->_frameworkHelper->add_action('wp_enqueue_scripts', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_actions'));
	}


	/**
	 * START: DEPRECATED FUNCTIONS THAT ARE IN HERE FOR OLD ADDONS TO WORK PROPERLY
	 **/

	/**
	 * old version of add_admin_bar();
	 **/
	public static function putAdminBarMenus(){
		return RevSliderFront::add_admin_bar();
	}

	/**
	 * END: DEPRECATED FUNCTIONS THAT ARE IN HERE FOR OLD ADDONS TO WORK PROPERLY
	 **/

	/**
	 * Add all actions that the frontend needs here
	 **/
	public static function add_actions(){

		$func	 = new RevSliderFunctions(self::$frameworkHelper);
		$css	 = new RevSliderCssParser(self::$frameworkHelper);
		$rs_ver	 = self::$frameworkHelper->apply_filters('revslider_remove_version', Framework::RS_REVISION);
		$global	 = $func->get_global_settings();
		$inc_global = $func->_truefalse($func->get_val($global, 'allinclude', true));
		$inc_footer = $func->_truefalse($func->get_val($global, array('script', 'footer'), false));

		$custom_css = $func->get_static_css();
		$custom_css = $css->compress_css($custom_css);

		$custom_css = (trim($custom_css) == '') ? '#rs-demo-id {}' : $custom_css;

		self::$frameworkHelper->wp_add_inline_style('rs-plugin-settings', $custom_css);

		self::$frameworkHelper->add_action('wp_head', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_meta_generator'));
		self::$frameworkHelper->add_action('wp_head', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'js_set_start_size'), 99);
		self::$frameworkHelper->add_action('admin_head', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'js_set_start_size'), 99);
		self::$frameworkHelper->add_action('wp_footer', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'load_icon_fonts'));
		self::$frameworkHelper->add_action('wp_footer', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'load_google_fonts'));

		//Async JS Loading
		if($func->_truefalse($func->get_val($global, array('script', 'defer'), false)) === true){
			self::$frameworkHelper->add_filter('clean_url', array('\Nwdthemes\Revslider\Model\Revslider\Front\RevSliderFront', 'add_defer_forscript'), 11, 1);
		}
	}


	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @since: 5.0
	 */
	public static function add_meta_generator(){
		echo self::$frameworkHelper->apply_filters('revslider_meta_generator', '<meta name="generator" content="Powered by Slider Revolution ' . Framework::RS_REVISION . ' - responsive, Mobile-Friendly Slider Plugin for WordPress with comfortable drag and drop interface." />' . "\n");
	}

	/**
	 * Load Used Icon Fonts
	 * @since: 5.0
	 */
	public static function load_icon_fonts(){
		global $fa_var, $fa_icon_var, $pe_7s_var;
		$func	= new RevSliderFunctions(self::$frameworkHelper);
		$global	= $func->get_global_settings();
		$ignore_fa = $func->_truefalse($func->get_val($global, 'fontawesomedisable', false));

		echo ($ignore_fa === false && ($fa_icon_var == true || $fa_var == true)) ? '<link rel="stylesheet" property="stylesheet" id="rs-icon-set-fa-icon-css" href="' . Framework::$RS_PLUGIN_URL . 'public/assets/fonts/font-awesome/css/font-awesome.min.css" type="text/css" media="all" />'."\n" : '';
		echo ($pe_7s_var) ? '<link rel="stylesheet" property="stylesheet" id="rs-icon-set-pe-7s-css" href="' . Framework::$RS_PLUGIN_URL . 'public/assets/fonts/pe-icon-7-stroke/css/pe-icon-7-stroke.min.css" type="text/css" media="all" />'."\n" : '';
	}


	/**
	 * Load Used Google Fonts
	 * add google fonts of all sliders found on the page
	 * @since: 6.0
	 */
	public static function load_google_fonts(){
		$func	= new RevSliderFunctions(self::$frameworkHelper);
		$fonts	= $func->print_clean_font_import();
		if(!empty($fonts)){
			echo $fonts."\n";
		}
	}


	/**
	 * add admin menu points in ToolBar Top
	 * @since: 5.0.5
	 * @before: putAdminBarMenus()
	 */
	public static function add_admin_bar(){
		if(!self::$frameworkHelper->is_super_admin() || !self::$frameworkHelper->is_admin_bar_showing()){
			return;
		}

		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				if(jQuery('#wp-admin-bar-revslider-default').length > 0 && jQuery('rs-module-wrap').length > 0){
					var aliases = new Array();
					jQuery('rs-module-wrap').each(function(){
						aliases.push(jQuery(this).data('alias'));
					});

					if(aliases.length > 0){
						jQuery('#wp-admin-bar-revslider-default li').each(function(){
							var li = jQuery(this),
								t = jQuery.trim(li.find('.ab-item .rs-label').data('alias')); //text()

							if(jQuery.inArray(t,aliases)!=-1){
							}else{
								li.remove();
							}
						});
					}
				}else{
					jQuery('#wp-admin-bar-revslider').remove();
				}
			});
		</script>
		<?php
}

	/**
	 * add admin nodes
	 * @since: 5.0.5
	 */
	public static function add_admin_menu_nodes(){
		if(!self::$frameworkHelper->is_super_admin() || !self::$frameworkHelper->is_admin_bar_showing()){
			return;
		}

		self::_add_node('<span class="rs-label">Slider Revolution</span>', false, self::$frameworkHelper->admin_url('admin.php?page=revslider'), array('class' => 'revslider-menu'), 'revslider'); //<span class="wp-menu-image dashicons-before dashicons-update"></span>

		//add all nodes of all Slider
		$sl = new RevSliderSlider(self::$frameworkHelper);
		$sliders = $sl->get_slider_for_admin_menu();

		if(!empty($sliders)){
			foreach ($sliders as $id => $slider){
				self::_add_node('<span class="rs-label" data-alias="' . self::$frameworkHelper->esc_attr($slider['alias']) . '">' . self::$frameworkHelper->esc_html($slider['title']) . '</span>', 'revslider', self::$frameworkHelper->getBackendUrl('nwdthemes_revslider/revslider/builder') . '?id=' . $id, array('class' => 'revslider-sub-menu'), self::$frameworkHelper->esc_attr($slider['alias'])); //<span class="wp-menu-image dashicons-before dashicons-update"></span>
			}
		}
	}

	/**
	 * add admin node
	 * @since: 5.0.5
	 */
	public static function _add_node($title, $parent = false, $href = '', $custom_meta = array(), $id = ''){
		if(!self::$frameworkHelper->is_super_admin() || !self::$frameworkHelper->is_admin_bar_showing()){
			return;
		}

		$id = ($id == '') ? strtolower(str_replace(' ', '-', $title)) : $id;

		//links from the current host will open in the current window
		$meta = (strpos($href, site_url()) !== false) ? array() : array('target' => '_blank'); //external links open in new tab/window
		$meta = array_merge($meta, $custom_meta);

		global $wp_admin_bar;
		$wp_admin_bar->add_node(array('parent'=> $parent, 'id' => $id, 'title' => $title, 'href' => $href, 'meta' => $meta));
	}

	/**
	 * adds async loading
	 * @since: 5.0
	 */
	public static function add_defer_forscript($url){
		if(strpos($url, 'rs6.min.js') === false && strpos($url, 'rbtools.min.js') === false){
			return $url;
		}elseif(self::$frameworkHelper->is_admin()){
			return $url;
		}else{
			return $url . "' defer='defer";
		}
	}

	/**
	 * Add functionality to gutenberg, elementor, visual composer and so on
	 **/
	public static function add_post_editor(){
		/**
		 * Page Editor Extensions
		 **/
		if(function_exists('is_user_logged_in') && self::$frameworkHelper->is_user_logged_in()){
			//only include gutenberg for production
			if(self::$frameworkHelper->is_admin() && defined('ABSPATH')){
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				if(function_exists('is_plugin_active') && !self::$frameworkHelper->is_plugin_active('revslider-gutenberg/plugin.php')){
					require_once(Framework::$RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/gutenberg/gutenberg-block.php');
					new RevSliderGutenberg('gutenberg/');
				}
			}
		}

		//Elementor Functionality
		require_once(Framework::$RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/elementor/elementor.class.php');
		self::$frameworkHelper->add_action('init', array('RevSliderElementor', 'init'));
	}

	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @since: 5.4.3
	 * @before: add_setREVStartSize()
		//NOT COMPRESSED VERSION
		function setREVStartSize(e){
			//window.requestAnimationFrame(function() {
				window.RSIW = window.RSIW===undefined ? window.innerWidth : window.RSIW;
				window.RSIH = window.RSIH===undefined ? window.innerHeight : window.RSIH;
				try {
					var pw = document.getElementById(e.c).parentNode.offsetWidth,
						newh;
					pw = pw===0 || isNaN(pw) ? window.RSIW : pw;
					e.tabw = e.tabw===undefined ? 0 : parseInt(e.tabw);
					e.thumbw = e.thumbw===undefined ? 0 : parseInt(e.thumbw);
					e.tabh = e.tabh===undefined ? 0 : parseInt(e.tabh);
					e.thumbh = e.thumbh===undefined ? 0 : parseInt(e.thumbh);
					e.tabhide = e.tabhide===undefined ? 0 : parseInt(e.tabhide);
					e.thumbhide = e.thumbhide===undefined ? 0 : parseInt(e.thumbhide);
					e.mh = e.mh===undefined || e.mh=="" || e.mh==="auto" ? 0 : parseInt(e.mh,0);
					if(e.layout==="fullscreen" || e.l==="fullscreen")
						newh = Math.max(e.mh,window.RSIH);
					else{
						e.gw = Array.isArray(e.gw) ? e.gw : [e.gw];
						for (var i in e.rl) if (e.gw[i]===undefined || e.gw[i]===0) e.gw[i] = e.gw[i-1];
						e.gh = e.el===undefined || e.el==="" || (Array.isArray(e.el) && e.el.length==0)? e.gh : e.el;
						e.gh = Array.isArray(e.gh) ? e.gh : [e.gh];
						for (var i in e.rl) if (e.gh[i]===undefined || e.gh[i]===0) e.gh[i] = e.gh[i-1];

						var nl = new Array(e.rl.length),
							ix = 0,
							sl;
						e.tabw = e.tabhide>=pw ? 0 : e.tabw;
						e.thumbw = e.thumbhide>=pw ? 0 : e.thumbw;
						e.tabh = e.tabhide>=pw ? 0 : e.tabh;
						e.thumbh = e.thumbhide>=pw ? 0 : e.thumbh;
						for (var i in e.rl) nl[i] = e.rl[i]<window.RSIW ? 0 : e.rl[i];
						sl = nl[0];
						for (var i in nl) if (sl>nl[i] && nl[i]>0) { sl = nl[i]; ix=i;}
						var m = pw>(e.gw[ix]+e.tabw+e.thumbw) ? 1 : (pw-(e.tabw+e.thumbw)) / (e.gw[ix]);
						newh =  (e.gh[ix] * m) + (e.tabh + e.thumbh);
					}
					if(window.rs_init_css===undefined) window.rs_init_css = document.head.appendChild(document.createElement("style"));
					document.getElementById(e.c).height = newh+"px";
					window.rs_init_css.innerHTML += "#"+e.c+"_wrapper { height: "+newh+"px }";
				} catch(e){
					console.log("Failure at Presize of Slider:" + e)
				}
			//}
		  };
	 */
	public static function js_set_start_size(){
		if(Framework::$revslider_rev_start_size_loaded === true) return false;

		$script = '<script type="text/javascript">';
		$script .= 'function setREVStartSize(e){
			//window.requestAnimationFrame(function() {
				window.RSIW = window.RSIW===undefined ? window.innerWidth : window.RSIW;
				window.RSIH = window.RSIH===undefined ? window.innerHeight : window.RSIH;
				try {
					var pw = document.getElementById(e.c).parentNode.offsetWidth,
						newh;
					pw = pw===0 || isNaN(pw) ? window.RSIW : pw;
					e.tabw = e.tabw===undefined ? 0 : parseInt(e.tabw);
					e.thumbw = e.thumbw===undefined ? 0 : parseInt(e.thumbw);
					e.tabh = e.tabh===undefined ? 0 : parseInt(e.tabh);
					e.thumbh = e.thumbh===undefined ? 0 : parseInt(e.thumbh);
					e.tabhide = e.tabhide===undefined ? 0 : parseInt(e.tabhide);
					e.thumbhide = e.thumbhide===undefined ? 0 : parseInt(e.thumbhide);
					e.mh = e.mh===undefined || e.mh=="" || e.mh==="auto" ? 0 : parseInt(e.mh,0);
					if(e.layout==="fullscreen" || e.l==="fullscreen")
						newh = Math.max(e.mh,window.RSIH);
					else{
						e.gw = Array.isArray(e.gw) ? e.gw : [e.gw];
						for (var i in e.rl) if (e.gw[i]===undefined || e.gw[i]===0) e.gw[i] = e.gw[i-1];
						e.gh = e.el===undefined || e.el==="" || (Array.isArray(e.el) && e.el.length==0)? e.gh : e.el;
						e.gh = Array.isArray(e.gh) ? e.gh : [e.gh];
						for (var i in e.rl) if (e.gh[i]===undefined || e.gh[i]===0) e.gh[i] = e.gh[i-1];

						var nl = new Array(e.rl.length),
							ix = 0,
							sl;
						e.tabw = e.tabhide>=pw ? 0 : e.tabw;
						e.thumbw = e.thumbhide>=pw ? 0 : e.thumbw;
						e.tabh = e.tabhide>=pw ? 0 : e.tabh;
						e.thumbh = e.thumbhide>=pw ? 0 : e.thumbh;
						for (var i in e.rl) nl[i] = e.rl[i]<window.RSIW ? 0 : e.rl[i];
						sl = nl[0];
						for (var i in nl) if (sl>nl[i] && nl[i]>0) { sl = nl[i]; ix=i;}
						var m = pw>(e.gw[ix]+e.tabw+e.thumbw) ? 1 : (pw-(e.tabw+e.thumbw)) / (e.gw[ix]);
						newh =  (e.gh[ix] * m) + (e.tabh + e.thumbh);
					}
					if(window.rs_init_css===undefined) window.rs_init_css = document.head.appendChild(document.createElement("style"));
					document.getElementById(e.c).height = newh+"px";
					window.rs_init_css.innerHTML += "#"+e.c+"_wrapper { height: "+newh+"px }";
				} catch(e){
					console.log("Failure at Presize of Slider:" + e)
				}
			//});
		  };';
		$script .= '</script>' . "\n";
		echo self::$frameworkHelper->apply_filters('revslider_add_setREVStartSize', $script);

		Framework::$revslider_rev_start_size_loaded = true;
	}

	/**
	 * sets the post saving value to true, so that the output echo will not be done
	 **/
	public static function set_post_saving(){
		Framework::$revslider_save_post = true;
	}

	/**
	 * check the current post for the existence of a short code
	 * @before: hasShortcode()
	 */
	public static function has_shortcode($shortcode = ''){
		$found = false;

		if(empty($shortcode)) return false;
		if(!self::$frameworkHelper->is_singular()) return false;

		$post = self::$frameworkHelper->get_post(self::$frameworkHelper->get_the_ID());
		if(stripos($post->post_content, '[' . $shortcode) !== false) $found = true;

		return $found;
	}

	/**
	 * Create Tables
	 * @only_base needs to be false
	 *  it can only be true by fixing database issues
	 *  this protects that the _bkp tables are not filled after
	 *  we are already on version 6.0
	 **/
	public static function create_tables($only_base = false){
		$table_version = self::$frameworkHelper->get_option('revslider_table_version', '1.0.0');

		if(version_compare($table_version, self::CURRENT_TABLE_VERSION, '<')){
			$wpdb = self::$frameworkHelper->getQueryHelper();

			//create CSS entries
			$result = $wpdb->get_row("SELECT COUNT( DISTINCT id ) AS NumberOfEntrys FROM " . $wpdb->prefix . self::TABLE_CSS);
			if(!empty($result) && $result->NumberOfEntrys == 0){
				$css_class = new RevSliderCssParser(self::$frameworkHelper);
				$css_class->import_css_captions();
			}

			self::$frameworkHelper->update_option('revslider_table_version', self::CURRENT_TABLE_VERSION);
			//$table_version = self::CURRENT_TABLE_VERSION;
		}


		/**
		 * check if table version is below 1.0.8.
		 * if yes, duplicate the tables into _bkp
		 * this way, we can revert back to v5 if any slider
		 * has issues in the v6 migration process
		 **/
		if(version_compare($table_version, '1.0.8', '<') && ($only_base === false || $only_base === '')){
			self::$frameworkHelper->backupDB();
		}
	}


	/**
	 * get the images from posts/pages for yoast seo
	 **/
	public static function get_images_for_seo($url, $type, $user){
		if(in_array($type, array('user', 'term'), true)) return $url;
		if(!is_object($user) || !isset($user->ID)) return $url;

		$post = self::$frameworkHelper->get_post($user->ID);
		if(is_a($post, 'WP_Post') && self::$frameworkHelper->has_shortcode($post->post_content, 'rev_slider')){
			preg_match_all('/\[rev_slider.*alias=.(.*)"\]/', $post->post_content, $shortcodes);

			if(isset($shortcodes[1]) && $shortcodes[1] !== ''){
				foreach($shortcodes[1] as $s){
					if(!RevSliderSlider::alias_exists($s)) continue;

					$sldr = new RevSliderSlider(self::$frameworkHelper);
					$sldr->init_by_alias($s);
					$sldr->get_slides();
					$imgs = $sldr->get_images();
					if(!empty($imgs)){
						if(!isset($url['images'])) $url['images'] = array();
						foreach($imgs as $v){
							$url['images'][] = $v;
						}
					}
				}
			}
		}

		return $url;
	}

}
