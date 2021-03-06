<?php
/**
 * The Third Party integration with the bbPress plugin.
 *
 * @since		2.9.8.8
 * @package		LiteSpeed_Cache
 * @subpackage	LiteSpeed_Cache/thirdparty
 * @author		LiteSpeed Technologies <info@litespeedtech.com>
 */
namespace LiteSpeed\Thirdparty;
defined( 'WPINC' ) || exit;

use \LiteSpeed\API;

class Elementor
{
	/**
	 * Detect if Elementor is installed and it's on ESI
	 *
	 * @since 2.9.8.8
	 * @access public
	 */
	public static function detect()
	{
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) return;
		if ( ! isset( $_GET[ 'lsesi' ] ) || $_GET[ 'lsesi' ] !== 'admin-bar' ) return;

		add_action( 'admin_bar_menu', __CLASS__ . '::add_menu_in_admin_bar', 100 );
	}

	public static function add_menu_in_admin_bar()
	{
		/*
		* As Elementor hook to the_contet filter to add the Edit with Elementor button,
		* force apply the_content filter to run the hook,
		* ESI itself can retrive the post data
		*/
		apply_filters( 'the_content', '' );
	}
}
