<?php
/**
 * @package   Display Posts Shortcode - Live Search
 * @category  Extension
 * @author    Steven A. Zahm
 * @author    Bill Erickson
 * @license   GPL-2.0+
 * @link      https://connections-pro.com
 * @copyright 2019 Steven A. Zahm
 *
 * @wordpress-plugin
 * Plugin Name:       Display Posts Shortcode - Live Search
 * Plugin URI:        https://connections-pro.com/
 * Description:       An extension for the Display Posts Shortcode plugin which adds support for auto suggest search results.
 * Version:           1.0
 * Author:            Steven A. Zahm and Bill Erickson
 * Author URI:        https://connections-pro.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       display-posts-shortcode-live-search
 * Domain Path:       /languages
 */

if ( ! class_exists( 'Display_Posts_Live_Search' ) ) {

	final class Display_Posts_Live_Search {

		const VERSION = '1.0';

		/**
		 * @var Display_Posts_Live_Search Stores the instance of this class.
		 *
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * @var string The absolute path this this file.
		 *
		 * @since 1.0
		 */
		private $file = '';

		/**
		 * @var string The URL to the plugin's folder.
		 *
		 * @since 1.0
		 */
		private $url = '';

		/**
		 * @var string The absolute path to this plugin's folder.
		 *
		 * @since 1.0
		 */
		private $path = '';

		/**
		 * @var string The basename of the plugin.
		 *
		 * @since 1.0
		 */
		private $basename = '';

		/**
		 * A dummy constructor to prevent the class from being loaded more than once.
		 *
		 * @since 1.0
		 */
		public function __construct() { /* Do nothing here */ }

		/**
		 * The main plugin instance.
		 *
		 * @since 1.0
		 *
		 * @return self
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {

				self::$instance = $self = new self;

				$self->file     = __FILE__;
				$self->url      = plugin_dir_url( $self->file );
				$self->path     = plugin_dir_path( $self->file );
				$self->basename = plugin_basename( $self->file );

				$self->hooks();
				$self->registerJavaScripts();
			}

			return self::$instance;

		}

		/**
		 * @since 1.0
		 */
		private function hooks() {

			add_filter( 'display_posts_shortcode_wrapper_open', array( __CLASS__, 'searchInput' ), 10, 2 );
			add_filter( 'shortcode_atts_display-posts', array( __CLASS__, 'addWrapperClass' ), 10, 4 );
		}

		/**
		 * @since 1.0
		 */
		public function getURL() {

			return $this->url;
		}

		/**
		 * @since 1.0
		 */
		private function registerJavaScripts() {

			$min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
			$url = Display_Posts_Live_Search()->getURL();

			wp_register_script(
				'fastLiveFilter',
				"{$url}assets/js/jquery.fastLiveFilter{$min}.js",
				array( 'jquery' ),
				'1.0.4',
				TRUE
			);

			wp_register_script(
				'dps-live-search',
				"{$url}assets/js/listing-search{$min}.js",
				array( 'fastLiveFilter' ),
				self::VERSION,
				TRUE
			);
		}

		/**
		 * Callback for the `display_posts_shortcode_wrapper_open` filter.
		 *
		 * Search Form
		 *
		 * @author Bill Erickson
		 * @since  1.0
		 *
		 * @see be_display_posts_shortcode()
		 *
		 * @param string $html
		 * @param array  $atts
		 *
		 * @return string
		 */
		public static function searchInput( $html, $atts ) {

			if ( ! empty( $atts['search_form'] ) &&
			     TRUE === filter_var( $atts['search_form'], FILTER_VALIDATE_BOOLEAN ) ) {

				wp_enqueue_script( 'dps-live-search' );

				$html = '<input type="search" class="s" name="dps-live-search" placeholder="Search This List" />' . $html;
			}

			return $html;
		}

		/**
		 * Callback for the `shortcode_atts_display-posts` filter.
		 *
		 * Add `live-search` to the wrapper classes.
		 *
		 * @see shortcode_atts()
		 *
		 * @since 1.0
		 *
		 * @param array  $out       The output array of shortcode attributes.
		 * @param array  $pairs     The supported attributes and their defaults.
		 * @param array  $atts      The user defined shortcode attributes.
		 * @param string $shortcode The shortcode name.
		 *
		 * @return mixed
		 */
		public static function addWrapperClass( $out, $pairs, $atts, $shortcode ) {

			if ( ! empty( $atts['search_form'] ) && TRUE === filter_var(
					$atts['search_form'],
					FILTER_VALIDATE_BOOLEAN
				) ) {

				$out['wrapper_class'] .= ' live-search';
			}

			return $out;
		}

	}

	/**
	 * @since 1.0
	 *
	 * @return Display_Posts_Live_Search
	 */
	function Display_Posts_Live_Search() {

		return Display_Posts_Live_Search::instance();
	}

	Display_Posts_Live_Search();
}
