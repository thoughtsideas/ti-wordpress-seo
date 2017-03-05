<?php
/**
 * Plugin Name: TI WordPress SEO
 * Plugin URI: https://github.com/thoughtsideas/ti-wordpress-seo/
 * Description: Tweak WordPress SEO plugin for simplicity.
 * Author: Michael Bragg <michael@michaelbragg.net>
 * Version: 1.0.0
 *
 * @package TI\WordPressSEO
 */

/**
 * Class
 */
class TI_WordPress_SEO {

	/**
	 * Current version number
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	const VERSION = '1.0.0';

	/**
	 * Hold class instance.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var TI_WordPress_SEO
	 */
	protected static $instance;

	/**
	 * Store this users data
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var array
	 */
	protected static $user;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		self::$user = $this->get_user();

	}

	/**
	 * Get class instance.
	 *
	 * @since 0.1.0
	 * @return TI_WordPress_SEO
	 */
	public static function has_instance() {

		if ( null === self::$instance ) {
			self::$instance = new TI_WordPress_SEO();
		}

		return self::$instance;

	}

	/**
	 * Get current users details
	 *
	 * @since 0.1.0
	 */
	protected static function get_user() {

		/** Get current user's ID. */
		$user_id = wp_get_current_user()->ID;
		$current_user = new WP_User( $user_id );

		/** Set the users details for use later. */
		return $current_user;

	}

	/**
	 * Check users capabilities
	 *
	 * @since 0.1.0
	 * @param string $capability User capability to check against.
	 * @return boolean             If the user has this capability.
	 */
	public function has_user_capability( $capability ) {

		if ( self::$user->has_cap( $capability ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Changes the SEO metbox priority for post types
	 *
	 * @since 0.1.0
	 * @uses wp_dashboard_setup
	 */
	public function change_metabox_priority() {
		return 'low';
	}

	/**
	 * Removes the SEO item from the admin bar
	 *
	 * @since 0.1.0
	 * @uses remove_menu
	 */
	public static function remove_admin_bar_seo() {

		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'wpseo-menu' );

	}

	/**
	 * Remove SEO dashboard widgets
	 *
	 *  @since 0.1.0
	 *  @uses  remove_meta_box
	 */
	public static function remove_dashboard_widgets() {

		remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'side' );

	}

	/**
	 * Removes the extra columns on the post/page listing screens.
	 *
	 * @since 0.1.0
	 * @uses manage_edit-post_columns
	 * @param	array $columns From WordPress core.
	 * @return array Updated columns array.
	 */
	public static function remove_columns( $columns ) {

		unset( $columns['wpseo-score'] );
		unset( $columns['wpseo-title'] );
		unset( $columns['wpseo-metadesc'] );
		unset( $columns['wpseo-focuskw'] );
		unset( $columns['wpseo-score-readability'] );

		return $columns;

	}

	/**
	 * Removes a meta box or any other element from a particular post edit screen
	 * of a given post type.
	 *
	 * @since 0.1.0
	 * @uses add_meta_boxes
	 */
	public function remove_metabox() {

		$post_types = get_post_types();

		foreach ( $post_types as $post_type ) {
			remove_meta_box( 'wpseo_meta', $post_type, 'normal' );
		}

	}

	/**
	 * Remove SEO Score from post view.
	 *
	 * @since 0.1.0
	 * @uses admin_head
	 */
	public function remove_seo_score() {

		wp_add_inline_style( 'wp-admin', '#keyword-score { display: none; }' );

	}

}

/**
 * Load the hooks for the plugins.
 */
function ti_wordpress_seo_init() {

	/** Check WordPress SEO plugin is installed. */
	if ( ! class_exists( 'WPSEO_Admin' ) ) {

		/** Stop the plugin running. Don't waste time running unnecessary code. */
		return false;

	}

	$ti_wpseo	= new TI_WordPress_SEO();

	$ti_wpseo::has_instance();

	add_filter(
		'wpseo_metabox_prio',
		array( $ti_wpseo, 'change_metabox_priority' )
	);

	add_action(
		'wp_before_admin_bar_render',
		array( $ti_wpseo, 'remove_admin_bar_seo' )
	);

	/** Check if user is less that Editor. */
	if ( ! $ti_wpseo->has_user_capability( 'edit_pages' ) ) {

		add_action(
			'wp_dashboard_setup',
			array( $ti_wpseo, 'remove_dashboard_widgets' )
		);

		add_filter(
			'manage_edit-post_columns',
			array( $ti_wpseo, 'remove_columns' )
		);

		add_action(
			'add_meta_boxes',
			array( $ti_wpseo, 'remove_metabox' ),
			11
		);

		add_action(
			'admin_head',
			array( $ti_wpseo, 'remove_seo_score' )
		);

	}

}

add_action( 'plugins_loaded', 'ti_wordpress_seo_init' );
