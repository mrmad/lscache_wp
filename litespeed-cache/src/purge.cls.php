<?php
/**
 * The plugin purge class for X-LiteSpeed-Purge
 *
 * @since      	1.1.3
 * @since  		1.5 Moved into /inc
 * @since  		2.2 Refactored. Changed access from public to private for most func and class variables.
 */
namespace LiteSpeed ;

defined( 'WPINC' ) || exit ;

class Purge extends Base
{
	protected static $_instance ;

	protected $_pub_purge = array() ;
	protected $_priv_purge = array() ;
	protected $_purge_related = false ;
	protected $_purge_single = false ;

	const X_HEADER = 'X-LiteSpeed-Purge' ;
	const DB_QUEUE = 'queue' ;

	const TYPE_PURGE_ALL = 'purge_all' ;
	const TYPE_PURGE_ALL_LSCACHE = 'purge_all_lscache' ;
	const TYPE_PURGE_ALL_CSSJS = 'purge_all_cssjs' ;
	const TYPE_PURGE_ALL_CCSS = 'purge_all_ccss' ;
	const TYPE_PURGE_ALL_PLACEHOLDER 	= 'purge_all_placeholder' ;
	const TYPE_PURGE_ALL_LQIP 			= 'purge_all_lqip' ;
	const TYPE_PURGE_ALL_AVATAR = 'purge_all_avatar' ;
	const TYPE_PURGE_ALL_OBJECT = 'purge_all_object' ;
	const TYPE_PURGE_ALL_OPCACHE = 'purge_all_opcache' ;

	const TYPE_PURGE_FRONT = 'purge_front' ;
	const TYPE_PURGE_FRONTPAGE = 'purge_frontpage' ;
	const TYPE_PURGE_PAGES = 'purge_pages' ;
	const TYPE_PURGE_ERROR = 'purge_error' ;

	/**
	 * Initialize
	 *
	 * @since    2.2.3
	 */
	protected function __construct()
	{
	}

	/**
	 * Init hooks
	 *
	 * @since  3.0
	 */
	public function init()
	{
		//register purge actions
		$purge_post_events = array(
			'edit_post',
			'save_post',
			'deleted_post',
			'trashed_post',
			'delete_attachment',
			// 'clean_post_cache', // This will disable wc's not purge product when stock status not change setting
		) ;
		foreach ( $purge_post_events as $event ) {
			// this will purge all related tags
			add_action( $event, __CLASS__ . '::purge_post', 10, 2 ) ;
		}

		add_action( 'wp_update_comment_count', __CLASS__ . '::purge_feeds' ) ;

	}

	/**
	 * Handle all request actions from main cls
	 *
	 * @since  1.8
	 * @access public
	 */
	public static function handler()
	{
		$instance = self::get_instance() ;

		$type = Router::verify_type() ;

		switch ( $type ) {
			case self::TYPE_PURGE_ALL :
				$instance->_purge_all() ;
				break ;

			case self::TYPE_PURGE_ALL_LSCACHE :
				$instance->_purge_all_lscache() ;
				break ;

			case self::TYPE_PURGE_ALL_CSSJS :
				$instance->_purge_all_cssjs() ;
				break ;

			case self::TYPE_PURGE_ALL_CCSS :
				$instance->_purge_all_ccss() ;
				break ;

			case self::TYPE_PURGE_ALL_PLACEHOLDER :
				$instance->_purge_all_placeholder() ;
				break ;

			case self::TYPE_PURGE_ALL_LQIP :
				$instance->_purge_all_lqip() ;
				break ;

			case self::TYPE_PURGE_ALL_AVATAR :
				$instance->_purge_all_avatar() ;
				break ;

			case self::TYPE_PURGE_ALL_OBJECT :
				$instance->_purge_all_object() ;
				break ;

			case self::TYPE_PURGE_ALL_OPCACHE :
				$instance->purge_all_opcache() ;
				break ;

			case self::TYPE_PURGE_FRONT :
				$instance->_purge_front() ;
				break ;

			case self::TYPE_PURGE_FRONTPAGE :
				$instance->_purge_frontpage() ;
				break ;

			case self::TYPE_PURGE_PAGES :
				$instance->_purge_pages() ;
				break ;

			case strpos( $type, self::TYPE_PURGE_ERROR ) === 0 :
				$instance->_purge_error( substr( $type, strlen( self::TYPE_PURGE_ERROR ) ) ) ;
				break ;

			default:
				break ;
		}

		Admin::redirect() ;
	}

	/**
	 * Shortcut to purge all lscache
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public static function purge_all( $reason = false )
	{
		self::get_instance()->_purge_all( $reason ) ;
	}

	/**
	 * Purge all caches (lscache/op/oc)
	 *
	 * @since 2.2
	 * @access private
	 */
	private function _purge_all( $reason = false )
	{
		$this->_purge_all_lscache( true ) ;
		$this->_purge_all_cssjs( true ) ;
		// $this->_purge_all_ccss( true ) ;
		// $this->_purge_all_placeholder( true ) ;
		$this->_purge_all_object( true ) ;
		$this->purge_all_opcache( true ) ;

		if ( ! is_string( $reason ) ) {
			$reason = false ;
		}

		if ( $reason ) {
			$reason = "( $reason )" ;
		}

		Log::debug( '[Purge] Purge all ' . $reason, 3 ) ;

		$msg = __( 'Purged all caches successfully.', 'litespeed-cache' ) ;
		! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
	}

	/**
	 * Alerts LiteSpeed Web Server to purge all pages.
	 *
	 * For multisite installs, if this is called by a site admin (not network admin),
	 * it will only purge all posts associated with that site.
	 *
	 * @since 2.2
	 * @access public
	 */
	private function _purge_all_lscache( $silence = false )
	{
		$this->_add( '*' ) ;

		// check if need to reset crawler
		if ( Conf::val( Base::O_CRAWLER ) ) {
			Crawler::get_instance()->reset_pos() ;
		}

		if ( ! $silence ) {
			$msg = __( 'Notified LiteSpeed Web Server to purge all LSCache entries.', 'litespeed-cache' ) ;
			! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
		}
	}

	/**
	 * Delete all critical css
	 *
	 * @since    2.3
	 * @access   private
	 */
	private function _purge_all_ccss( $silence = false )
	{
		CSS::get_instance()->rm_cache_folder() ;

		if ( ! $silence ) {
			$msg = __( 'Cleaned all critical CSS files.', 'litespeed-cache' ) ;
			! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
		}
	}

	/**
	 * Delete all placeholder images
	 *
	 * @since    2.5.1
	 * @access   private
	 */
	private function _purge_all_placeholder( $silence = false )
	{
		Placeholder::get_instance()->rm_cache_folder() ;

		if ( ! $silence ) {
			$msg = __( 'Cleaned all placeholder files.', 'litespeed-cache' ) ;
			! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
		}
	}

	/**
	 * Delete all LQIP images
	 *
	 * @since    3.0
	 * @access   private
	 */
	private function _purge_all_lqip( $silence = false )
	{
		Placeholder::get_instance()->rm_lqip_cache_folder() ;

		if ( ! $silence ) {
			$msg = __( 'Cleaned all LQIP files.', 'litespeed-cache' ) ;
			! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
		}
	}

	/**
	 * Delete all avatar images
	 *
	 * @since    3.0
	 * @access   private
	 */
	private function _purge_all_avatar( $silence = false )
	{
		Avatar::get_instance()->rm_cache_folder() ;

		if ( ! $silence ) {
			$msg = __( 'Cleaned all gravatar files.', 'litespeed-cache' ) ;
			! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
		}
	}

	/**
	 * Alerts LiteSpeed Web Server to purge pages.
	 *
	 * @since    1.2.2
	 * @access   private
	 */
	private function _purge_all_cssjs( $silence = false )
	{
		Optimize::update_option( Optimize::ITEM_TIMESTAMP_PURGE_CSS, time() ) ;

		$this->_add( Tag::TYPE_MIN ) ;

		// For non-ls users
		Optimize::get_instance()->rm_cache_folder() ;

		if ( ! $silence ) {
			$msg = __( 'Notified LiteSpeed Web Server to purge CSS/JS entries.', 'litespeed-cache' ) ;
			! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
		}
	}

	/**
	 * Purge opcode cache
	 *
	 * @since  1.8.2
	 * @access public
	 */
	public function purge_all_opcache( $silence = false )
	{
		if ( ! Router::opcache_enabled() ) {
			Log::debug( '[Purge] Failed to reset opcode cache due to opcache not enabled' ) ;

			if ( ! $silence ) {
				$msg = __( 'Opcode cache is not enabled.', 'litespeed-cache' ) ;
				Admin_Display::error( $msg ) ;
			}

			return false ;
		}

		// Purge opcode cache
		opcache_reset() ;
		Log::debug( '[Purge] Reset opcode cache' ) ;

		if ( ! $silence ) {
			$msg = __( 'Reset the entire opcode cache successfully.', 'litespeed-cache' ) ;
			! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
		}

		return true ;
	}

	/**
	 * Purge object cache
	 *
	 * @since  1.8
	 * @access private
	 */
	private function _purge_all_object( $silence = false )
	{
		if ( ! defined( 'LSCWP_OBJECT_CACHE' ) ) {
			Log::debug( '[Purge] Failed to flush object cache due to object cache not enabled' ) ;

			if ( ! $silence ) {
				$msg = __( 'Object cache is not enabled.', 'litespeed-cache' ) ;
				Admin_Display::error( $msg ) ;
			}

			return false ;
		}
		Object_Cache::get_instance()->flush() ;
		Log::debug( '[Purge] Flushed object cache' ) ;

		if ( ! $silence ) {
			$msg = __( 'Purge all object caches successfully.', 'litespeed-cache' ) ;
			! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
		}

		return true ;
	}

	/**
	 * Adds new public purge tags to the array of purge tags for the request.
	 *
	 * @since 1.1.3
	 * @access public
	 * @param mixed $tags Tags to add to the list.
	 */
	public static function add( $tags )
	{
		self::get_instance()->_add( $tags ) ;
	}

	/**
	 * Add tags to purge
	 *
	 * @since 2.2
	 * @access private
	 */
	private function _add( $tags )
	{
		if ( ! is_array( $tags ) ) {
			$tags = array( $tags ) ;
		}
		if ( ! array_diff( $tags, $this->_pub_purge ) ) {
			return ;
		}

		$this->_pub_purge = array_merge( $this->_pub_purge, $tags ) ;
		Log::debug( '[Purge] added ' . implode( ',', $tags ), 8 ) ;

		// Send purge header immediately
		$curr_built = $this->_build() ;
		if ( defined( 'LITESPEED_DID_send_headers' ) ) {
			// Can't send, already has output, need to save and wait for next run
			self::update_option( self::DB_QUEUE, $curr_built ) ;
			Log::debug( '[Purge] Output existed, queue stored: ' . $curr_built ) ;
		}
		else {
			@header( $curr_built ) ;
			Log::debug( $curr_built ) ;
		}

	}

	/**
	 * Adds new private purge tags to the array of purge tags for the request.
	 *
	 * @since 1.1.3
	 * @access public
	 * @param mixed $tags Tags to add to the list.
	 */
	public static function add_private( $tags )
	{
		self::get_instance()->_add_private( $tags ) ;
	}

	/**
	 * Add tags to private purge
	 *
	 * @since 2.2
	 * @access private
	 */
	private function _add_private( $tags )
	{
		if ( ! is_array( $tags ) ) {
			$tags = array( $tags ) ;
		}
		if ( ! array_diff( $tags, $this->_priv_purge ) ) {
			return ;
		}

		Log::debug( '[Purge] added [private] ' . implode( ',', $tags ), 3 ) ;

		$this->_priv_purge = array_merge( $this->_priv_purge, $tags ) ;

		// Send purge header immediately
		@header( $this->_build() ) ;
	}

	/**
	 * Activate `purge related tags` for Admin QS.
	 *
	 * @since    1.1.3
	 * @access   public
	 */
	public static function set_purge_related()
	{
		self::get_instance()->_purge_related = true ;
	}

	/**
	 * Activate `purge single url tag` for Admin QS.
	 *
	 * @since    1.1.3
	 * @access   public
	 */
	public static function set_purge_single()
	{
		self::get_instance()->_purge_single = true ;
	}

	/**
	 * Purge frontend url
	 *
	 * @since 1.3
	 * @since 2.2 Renamed from `frontend_purge`; Access changed from public
	 * @access private
	 */
	private function _purge_front()
	{
		if ( empty( $_SERVER[ 'HTTP_REFERER' ] ) ) {
			exit( 'no referer' ) ;
		}

		$this->purgeby_url_cb( $_SERVER[ 'HTTP_REFERER' ] ) ;

		wp_redirect( $_SERVER[ 'HTTP_REFERER' ] ) ;
		exit() ;
	}

	/**
	 * Alerts LiteSpeed Web Server to purge the front page.
	 *
	 * @since    1.0.3
	 * @since  	 2.2 	Access changed from public to private, renamed from `_purge_front`
	 * @access   private
	 */
	private function _purge_frontpage()
	{
		$this->_add( Tag::TYPE_FRONTPAGE ) ;
		if ( LITESPEED_SERVER_TYPE !== 'LITESPEED_SERVER_OLS' ) {
			$this->_add_private( Tag::TYPE_FRONTPAGE ) ;
		}

		$msg = __( 'Notified LiteSpeed Web Server to purge the front page.', 'litespeed-cache' ) ;
		! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
	}

	/**
	 * Alerts LiteSpeed Web Server to purge pages.
	 *
	 * @since    1.0.15
	 * @access   private
	 */
	private function _purge_pages()
	{
		$this->_add( Tag::TYPE_PAGES ) ;

		$msg = __( 'Notified LiteSpeed Web Server to purge pages.', 'litespeed-cache' ) ;
		! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
	}

	/**
	 * Alerts LiteSpeed Web Server to purge error pages.
	 *
	 * @since    1.0.14
	 * @access   private
	 */
	private function _purge_error( $type = false )
	{
		$this->_add( Tag::TYPE_ERROR ) ;

		if ( ! $type || ! in_array( $type, array( '403', '404', '500' ) ) ) {
			return ;
		}

		$this->_add( Tag::TYPE_ERROR . $type ) ;

		$msg = __( 'Notified LiteSpeed Web Server to purge error pages.', 'litespeed-cache' ) ;
		! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( $msg ) ;
	}

	/**
	 * Callback to add purge tags if admin selects to purge selected category pages.
	 *
	 * @since 1.0.7
	 * @access public
	 * @param string $value The category slug.
	 * @param string $key Unused.
	 */
	public function purgeby_cat_cb( $value, $key )
	{
		$val = trim( $value ) ;
		if ( empty( $val ) ) {
			return ;
		}
		if ( preg_match( '/^[a-zA-Z0-9-]+$/', $val ) == 0 ) {
			Log::debug( "[Purge] $val cat invalid" ) ;
			return ;
		}
		$cat = get_category_by_slug( $val ) ;
		if ( $cat == false ) {
			Log::debug( "[Purge] $val cat not existed/published" ) ;
			return ;
		}

		! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( sprintf( __( 'Purge category %s', 'litespeed-cache' ), $val ) ) ;

		$this->_add( Tag::TYPE_ARCHIVE_TERM . $cat->term_id ) ;
	}

	/**
	 * Callback to add purge tags if admin selects to purge selected post IDs.
	 *
	 * @since 1.0.7
	 * @access public
	 * @param string $value The post ID.
	 * @param string $key Unused.
	 */
	public function purgeby_pid_cb( $value, $key )
	{
		$val = trim( $value ) ;
		if ( empty( $val ) ) {
			return ;
		}
		if ( ! is_numeric( $val ) ) {
			Log::debug( "[Purge] $val pid not numeric" ) ;
			return ;
		}
		elseif ( get_post_status( $val ) !== 'publish' ) {
			Log::debug( "[Purge] $val pid not published" ) ;
			return ;
		}
		! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( sprintf( __( 'Purge Post ID %s', 'litespeed-cache' ), $val ) ) ;

		$this->_add( Tag::TYPE_POST . $val ) ;
	}

	/**
	 * Callback to add purge tags if admin selects to purge selected tag pages.
	 *
	 * @since 1.0.7
	 * @access public
	 * @param string $value The tag slug.
	 * @param string $key Unused.
	 */
	public function purgeby_tag_cb( $value, $key )
	{
		$val = trim( $value ) ;
		if ( empty( $val ) ) {
			return ;
		}
		if ( preg_match( '/^[a-zA-Z0-9-]+$/', $val ) == 0 ) {
			Log::debug( "[Purge] $val tag invalid" ) ;
			return ;
		}
		$term = get_term_by( 'slug', $val, 'post_tag' ) ;
		if ( $term == 0 ) {
			Log::debug( "[Purge] $val tag not exist" ) ;
			return ;
		}

		! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( sprintf( __( 'Purge tag %s', 'litespeed-cache' ), $val ) ) ;

		$this->_add( Tag::TYPE_ARCHIVE_TERM . $term->term_id ) ;
	}

	/**
	 * Callback to add purge tags if admin selects to purge selected urls.
	 *
	 * @since 1.0.7
	 * @access public
	 * @param string $value A url to purge.
	 * @param string $key Unused.
	 */
	public function purgeby_url_cb( $value, $key = false )
	{
		$val = trim( $value ) ;
		if ( empty( $val ) ) {
			return ;
		}

		if ( strpos( $val, '<' ) !== false ) {
			Log::debug( "[Purge] $val url contains <" ) ;
			return ;
		}

		$val = Utility::make_relative( $val ) ;

		$hash = Tag::get_uri_tag( $val ) ;

		if ( $hash === false ) {
			Log::debug( "[Purge] $val url invalid" ) ;
			return ;
		}

		! defined( 'LITESPEED_PURGE_SILENT' ) && Admin_Display::succeed( sprintf( __( 'Purge url %s', 'litespeed-cache' ), $val ) ) ;

		$this->_add( $hash ) ;
		return ;
	}

	/**
	 * Purge a list of pages when selected by admin. This method will
	 * look at the post arguments to determine how and what to purge.
	 *
	 * @since 1.0.7
	 * @access public
	 */
	public function purge_list()
	{
		if ( ! isset($_REQUEST[Admin_Display::PURGEBYOPT_SELECT]) || ! isset($_REQUEST[Admin_Display::PURGEBYOPT_LIST]) ) {
			return ;
		}
		$sel = $_REQUEST[Admin_Display::PURGEBYOPT_SELECT] ;
		$list_buf = $_REQUEST[Admin_Display::PURGEBYOPT_LIST] ;
		if ( empty($list_buf) ) {
			return ;
		}
		$list_buf = str_replace(",", "\n", $list_buf) ;// for cli
		$list = explode("\n", $list_buf) ;
		switch($sel) {
			case Admin_Display::PURGEBY_CAT:
				$cb = 'purgeby_cat_cb' ;
				break ;
			case Admin_Display::PURGEBY_PID:
				$cb = 'purgeby_pid_cb' ;
				break ;
			case Admin_Display::PURGEBY_TAG:
				$cb = 'purgeby_tag_cb' ;
				break ;
			case Admin_Display::PURGEBY_URL:
				$cb = 'purgeby_url_cb' ;
				break ;

			default:
				return ;
		}
		array_walk( $list, array( $this, $cb ) ) ;

		// for redirection
		$_GET[ Admin_Display::PURGEBYOPT_SELECT ] = $sel ;
	}

	/**
	 * Purge a post on update.
	 *
	 * This function will get the relevant purge tags to add to the response
	 * as well.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param integer $id The post id to purge.
	 */
	public static function purge_post( $id )
	{
		$post_id = intval($id) ;
		// ignore the status we don't care
		if ( ! in_array(get_post_status($post_id), array( 'publish', 'trash', 'private', 'draft' )) ) {
			return ;
		}

		$instance = self::get_instance() ;

		$purge_tags = $instance->_get_purge_tags_by_post($post_id) ;
		if ( empty($purge_tags) ) {
			return ;
		}
		if ( in_array( '*', $purge_tags ) ) {
			$instance->_purge_all_lscache() ;
		}
		else {
			$instance->_add( $purge_tags ) ;
			if ( Conf::val( Base::O_CACHE_REST ) ) {
				$instance->_add( Tag::TYPE_REST ) ;
			}
		}
		Control::set_stale() ;
	}

	/**
	 * Hooked to the load-widgets.php action.
	 * Attempts to purge a single widget from cache.
	 * If no widget id is passed in, the method will attempt to find the widget id.
	 *
	 * @since 1.1.3
	 * @access public
	 * @param type $widget_id The id of the widget to purge.
	 */
	public static function purge_widget($widget_id = null)
	{
		if ( is_null($widget_id) ) {
			$widget_id = $_POST['widget-id'] ;
			if ( is_null($widget_id) ) {
				return ;
			}
		}
		self::add(Tag::TYPE_WIDGET . $widget_id) ;
		self::add_private(Tag::TYPE_WIDGET . $widget_id) ;
	}

	/**
	 * Hooked to the wp_update_comment_count action.
	 * Purges the comment widget when the count is updated.
	 *
	 * @access public
	 * @since 1.1.3
	 * @global type $wp_widget_factory
	 */
	public static function purge_comment_widget()
	{
		global $wp_widget_factory ;
		$recent_comments = $wp_widget_factory->widgets['WP_Widget_Recent_Comments'] ;
		if ( !is_null($recent_comments) ) {
			self::add(Tag::TYPE_WIDGET . $recent_comments->id) ;
			self::add_private(Tag::TYPE_WIDGET . $recent_comments->id) ;
		}
	}

	/**
	 * Purges feeds on comment count update.
	 *
	 * @since 1.0.9
	 * @access public
	 */
	public static function purge_feeds()
	{
		if ( Conf::val(Base::O_CACHE_TTL_FEED) > 0 ) {
			self::add(Tag::TYPE_FEED) ;
		}
	}

	/**
	 * Purges all private cache entries when the user logs out.
	 *
	 * @access public
	 * @since 1.1.3
	 */
	public static function purge_on_logout()
	{
		self::add_private('*') ;
	}

	/**
	 * Generate all purge tags before output
	 *
	 * @access private
	 * @since 1.1.3
	 */
	private function _finalize()
	{
		// Make sure header output only run once
		if ( ! defined( 'LITESPEED_DID_' . __FUNCTION__ ) ) {
			define( 'LITESPEED_DID_' . __FUNCTION__, true ) ;
		}
		else {
			return ;
		}

		do_action('litespeed_api_purge') ;

		// Append unique uri purge tags if Admin QS is `PURGESINGLE`
		if ( $this->_purge_single ) {
			$this->_pub_purge[] = Tag::build_uri_tag() ; // TODO: add private tag too
		}
		// Append related purge tags if Admin QS is `PURGE`
		if ( $this->_purge_related ) {
			// Before this, tags need to be finalized
			$tags_related = Tag::output_tags() ;
			// NOTE: need to remove the empty item `B1_` to avoid purging all
			$tags_related = array_filter($tags_related) ;
			if ( $tags_related ) {
				$this->_pub_purge = array_merge($this->_pub_purge, $tags_related) ;
			}
		}

		if ( ! empty($this->_pub_purge) ) {
			$this->_pub_purge = array_unique($this->_pub_purge) ;
		}

		if ( ! empty($this->_priv_purge) ) {
			$this->_priv_purge = array_unique($this->_priv_purge) ;
		}
	}

	/**
	 * Gathers all the purge headers.
	 *
	 * This will collect all site wide purge tags as well as third party plugin defined purge tags.
	 *
	 * @since 1.1.0
	 * @access public
	 * @return string the built purge header
	 */
	public static function output()
	{
		$instance = self::get_instance() ;

		$instance->_finalize() ;

		return $instance->_build() ;
	}

	/**
	 * Build the current purge headers.
	 *
	 * @since 1.1.5
	 * @access private
	 * @return string the built purge header
	 */
	private function _build()
	{
		if ( empty( $this->_pub_purge ) && empty( $this->_priv_purge ) ) {
			return ;
		}

		$purge_header = '' ;
		$private_prefix = self::X_HEADER . ': private,' ;

		if ( ! empty( $this->_pub_purge ) ) {
			$public_tags = $this->_append_prefix( $this->_pub_purge ) ;
			if ( empty( $public_tags ) ) {
				// If this ends up empty, private will also end up empty
				return ;
			}
			$purge_header = self::X_HEADER . ': public,' ;
			if ( Control::is_stale() ) {
				$purge_header .= 'stale,' ;
			}
			$purge_header .= implode( ',', $public_tags ) ;
			$private_prefix = ';private,' ;
		}

		// Handle priv purge tags
		if ( ! empty( $this->_priv_purge ) ) {
			$private_tags = $this->_append_prefix( $this->_priv_purge, true ) ;
			$purge_header .= $private_prefix . implode( ',', $private_tags ) ;
		}

		return $purge_header ;
	}

	/**
	 * Append prefix to an array of purge headers
	 *
	 * @since 1.1.0
	 * @access private
	 * @param array $purge_tags The purge tags to apply the prefix to.
	 * @param  boolean $is_private If is private tags or not.
	 * @return array The array of built purge tags.
	 */
	private function _append_prefix( $purge_tags, $is_private = false )
	{
		$curr_bid = is_multisite() ? get_current_blog_id() : '' ;

		if ( ! in_array('*', $purge_tags) ) {
			$tags = array() ;
			foreach ($purge_tags as $val) {
				$tags[] = LSWCP_TAG_PREFIX . $curr_bid . '_' . $val ;
			}
			return $tags ;
		}

		if ( defined('LSWCP_EMPTYCACHE') || $is_private ) {
			return array('*') ;
		}

		// Would only use multisite and network admin except is_network_admin
		// is false for ajax calls, which is used by wordpress updates v4.6+
		if ( is_multisite() && (is_network_admin() || (
				Router::is_ajax() && (check_ajax_referer('updates', false, false) || check_ajax_referer('litespeed-purgeall-network', false, false))
				)) ) {
			$blogs = Activation::get_network_ids() ;
			if ( empty($blogs) ) {
				Log::debug('[Purge] build_purge_headers: blog list is empty') ;
				return '' ;
			}
			$tags = array() ;
			foreach ($blogs as $blog_id) {
				$tags[] = LSWCP_TAG_PREFIX . $blog_id . '_' ;
			}
			return $tags ;
		}
		else {
			return array(LSWCP_TAG_PREFIX . $curr_bid . '_') ;
		}
	}

	/**
	 * Gets all the purge tags correlated with the post about to be purged.
	 *
	 * If the purge all pages configuration is set, all pages will be purged.
	 *
	 * This includes site wide post types (e.g. front page) as well as
	 * any third party plugin specific post tags.
	 *
	 * @since 1.0.0
	 * @access private
	 * @param integer $post_id The id of the post about to be purged.
	 * @return array The list of purge tags correlated with the post.
	 */
	private function _get_purge_tags_by_post( $post_id )
	{
		// If this is a valid post we want to purge the post, the home page and any associated tags & cats
		// If not, purge everything on the site.

		$purge_tags = array() ;
		$config = Conf::get_instance() ;

		if ( Conf::val( Base::O_PURGE_POST_ALL ) ) {
			// ignore the rest if purge all
			return array( '*' ) ;
		}

		// now do API hook action for post purge
		do_action('litespeed_api_purge_post', $post_id) ;

		// post
		$purge_tags[] = Tag::TYPE_POST . $post_id ;
		$purge_tags[] = Tag::get_uri_tag(wp_make_link_relative(get_permalink($post_id))) ;

		// for archive of categories|tags|custom tax
		global $post ;
		$original_post = $post ;
		$post = get_post($post_id) ;
		$post_type = $post->post_type ;

		global $wp_widget_factory ;
		$recent_posts = $wp_widget_factory->widgets['WP_Widget_Recent_Posts'] ;
		if ( ! is_null($recent_posts) ) {
			$purge_tags[] = Tag::TYPE_WIDGET . $recent_posts->id ;
		}

		// get adjacent posts id as related post tag
		if( $post_type == 'post' ){
			$prev_post = get_previous_post() ;
			$next_post = get_next_post() ;
			if( ! empty($prev_post->ID) ) {
				$purge_tags[] = Tag::TYPE_POST . $prev_post->ID ;
				Log::debug('--------purge_tags prev is: '.$prev_post->ID) ;
			}
			if( ! empty($next_post->ID) ) {
				$purge_tags[] = Tag::TYPE_POST . $next_post->ID ;
				Log::debug('--------purge_tags next is: '.$next_post->ID) ;
			}
		}

		if ( Conf::val( Base::O_PURGE_POST_TERM ) ) {
			$taxonomies = get_object_taxonomies($post_type) ;
			//Log::debug('purge by post, check tax = ' . var_export($taxonomies, true)) ;
			foreach ( $taxonomies as $tax ) {
				$terms = get_the_terms($post_id, $tax) ;
				if ( ! empty($terms) ) {
					foreach ( $terms as $term ) {
						$purge_tags[] = Tag::TYPE_ARCHIVE_TERM . $term->term_id ;
					}
				}
			}
		}

		if ( Conf::val( Base::O_CACHE_TTL_FEED ) ) {
			$purge_tags[] = Tag::TYPE_FEED ;
		}

		// author, for author posts and feed list
		if ( Conf::val( Base::O_PURGE_POST_AUTHOR) ) {
			$purge_tags[] = Tag::TYPE_AUTHOR . get_post_field('post_author', $post_id) ;
		}

		// archive and feed of post type
		// todo: check if type contains space
		if ( Conf::val( Base::O_PURGE_POST_POSTTYPE) ) {
			if ( get_post_type_archive_link($post_type) ) {
				$purge_tags[] = Tag::TYPE_ARCHIVE_POSTTYPE . $post_type ;
			}
		}

		if ( Conf::val( Base::O_PURGE_POST_FRONTPAGE) ) {
			$purge_tags[] = Tag::TYPE_FRONTPAGE ;
		}

		if ( Conf::val( Base::O_PURGE_POST_HOMEPAGE) ) {
			$purge_tags[] = Tag::TYPE_HOME ;
		}

		if ( Conf::val( Base::O_PURGE_POST_PAGES) ) {
			$purge_tags[] = Tag::TYPE_PAGES ;
		}

		if ( Conf::val( Base::O_PURGE_POST_PAGES_WITH_RECENT_POSTS) ) {
			$purge_tags[] = Tag::TYPE_PAGES_WITH_RECENT_POSTS ;
		}

		// if configured to have archived by date
		$date = $post->post_date ;
		$date = strtotime($date) ;

		if ( Conf::val( Base::O_PURGE_POST_DATE) ) {
			$purge_tags[] = Tag::TYPE_ARCHIVE_DATE . date('Ymd', $date) ;
		}

		if ( Conf::val( Base::O_PURGE_POST_MONTH) ) {
			$purge_tags[] = Tag::TYPE_ARCHIVE_DATE . date('Ym', $date) ;
		}

		if ( Conf::val( Base::O_PURGE_POST_YEAR) ) {
			$purge_tags[] = Tag::TYPE_ARCHIVE_DATE . date('Y', $date) ;
		}

		// Set back to original post as $post_id might affecting the global $post value
		$post = $original_post ;

		return array_unique($purge_tags) ;
	}

	/**
	 * The dummy filter for purge all
	 *
	 * @since 1.1.5
	 * @access public
	 * @param string $val The filter value
	 * @return string     The filter value
	 */
	public static function filter_with_purge_all( $val )
	{
		self::purge_all() ;
		return $val ;
	}
}
