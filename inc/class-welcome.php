<?php
/**
 * A "welcome" page to assit with onboarding users
 * who are not familiar with the app.
 */

namespace ReplyBox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Welcome page class.
 */
class Welcome {
	/**
	 * @var string Page slug.
	 */
	private $slug = 'replybox-welcome';

	/**
	 * Construct.
	 */
	function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', [ $this, 'do_activation_redirect' ] );
		add_action( 'admin_menu', [ $this, 'add_screen' ] );
		add_action( 'admin_head', [ $this, 'remove_menus' ] );
	}

	/**
	 * Activate plugin.
	 */
	public function activate() {
		set_transient( '_replybox_welcome_screen_activation_redirect', true, 30 );
	}

	/**
	 * Do activation redirect.
	 */
	public function do_activation_redirect() {
		// Bail if no activation redirect
		if ( ! get_transient( '_replybox_welcome_screen_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_replybox_welcome_screen_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		// Redirect to welcome page.
		wp_safe_redirect( add_query_arg( array( 'page' => $this->slug ), admin_url( 'index.php' ) ) );
	}

	/**
	 * Add welcome screen page.
	 */
	public function add_screen() {
		add_dashboard_page(
			__( 'Welcome to ReplyBox', 'replybox' ),
			__( 'Welcome to ReplyBox', 'replybox' ),
			'read',
			$this->slug,
			[ $this, 'content' ]
		);
	}

	/**
	 * Welcome screen content.
	 */
	public function content() {
		require_once plugin_dir_path( __FILE__ ) . '../views/admin-welcome.php';
	}

	/**
	 * Remove page from menus.
	 */
	function remove_menus() {
		remove_submenu_page( 'index.php', $this->slug );
	}
}
