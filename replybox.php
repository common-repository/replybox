<?php
/**
 * Plugin Name: ReplyBox — Better WordPress Comments
 * Description: A simple, honest comment system which works everywhere. No ads, no dodgy affiliate links, no fluff.
 * Version: 1.4.0
 * Author: ReplyBox
 * Author URI: https://getreplybox.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REPLYBOX_URL', \plugins_url( '/', __FILE__ ) );
define( 'REPLYBOX_PATH', \plugin_dir_path( __FILE__ ) );

final class ReplyBox {

	/**
	 * @var ReplyBox|null
	 */
	private static $instance;

	/**
	 * @var array
	 */
	private $options = array();
	/**
	 * @var null|ReplyBox\Welcome
	 */
	private $welcome = null;

	/**
	 * Get ReplyBox instance.
	 *
	 * @return ReplyBox
	 */
	public static function instance() {
		if ( empty( static::$instance ) ) {
			static::$instance = new self();
			static::$instance->init();
		}

		return static::$instance;
	}

	/**
	 * Init ReplyBox class.
	 *
	 * @return void
	 */
	private function init() {
		require_once plugin_dir_path( __FILE__ ) . 'inc/class-welcome.php';

		$this->welcome = new ReplyBox\Welcome();
		$this->options = $this->get_options();

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_post_replybox_settings', array( $this, 'save_form' ) );
		add_action( 'rest_api_init', array( $this, 'register_api_endpoints' ) );

		if ( $this->replace_comments() ) {
			add_filter( 'comments_template', array( $this, 'comments_template' ), 100 );
			add_filter( 'get_comment_link', array( $this, 'comment_link' ), 10, 2 );
			add_filter( 'get_comments_link', array( $this, 'comments_link' ) );
			add_filter( 'pre_render_block', array( $this, 'replace_comment_block' ), 10, 2 );
		}

		add_action( 'admin_bar_menu', array( $this, 'remove_from_admin_bar' ), 999 );
		add_filter( 'wp_count_comments', array( $this, 'count_comments' ), 10, 2 );
		add_filter( 'manage_edit-comments_columns', array( $this, 'comments_columns' ) );
		add_filter( 'bulk_actions-edit-comments', array( $this, 'comments_bulk_actions' ) );
		add_filter( 'comment_row_actions', array( $this, 'comments_row_actions' ) );
		add_filter( 'comment_status_links', array( $this, 'comments_status_links' ) );
		add_action( 'admin_notices', array( $this, 'add_replybox_comments_notice' ) );
		add_action( 'init', array( $this, 'disable_comments_post' ) );


		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Get all options.
	 *
	 * @return array
	 */
	private function get_options() {
		return get_option( 'replybox', array() );
	}

	/**
	 * Save the options.
	 *
	 * @return void
	 */
	private function save_options() {
		update_option( 'replybox', $this->options );
	}

	/**
	 * Get a single option.
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	private function get_option( $key, $default = '' ) {
		if ( isset( $this->options[ $key ] ) ) {
			return $this->options[ $key ];
		}

		return $default;
	}

	/**
	 * Update a single option.
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return $this
	 */
	private function update_option( $key, $value ) {
		$this->options[ $key ] = $value;

		return $this;
	}

	/**
	 * Should we overwrite the comments template?
	 *
	 * @return bool
	 */
	private function replace_comments() {
		$value = $this->get_option( 'site_id' );

		return apply_filters( 'replybox_replace_comments', ! empty( $value ) );
	}

	/**
	 * Generate a new secure token.
	 *
	 * @return string
	 */
	private function generate_token() {
		$token = md5( uniqid( rand(), true ) );

		$this->update_option( 'secure_token', $token )->save_options();
	}

	/**
	 * Register the admin page.
	 */
	public function add_admin_menu() {
		add_submenu_page( 'options-general.php', __( 'ReplyBox', 'replybox' ), __( 'ReplyBox', 'replybox' ),
			'manage_options', 'replybox', array( $this, 'show_admin_page' ) );
	}

	/**
	 * Render the admin page.
	 */
	public function show_admin_page() {
		if ( ! empty( $_GET['settings-updated'] ) ) {
			add_settings_error( 'replybox', 'settings-updated', __( 'Settings saved.', 'replybox' ), 'updated' );
		}

		require_once plugin_dir_path( __FILE__ ) . 'views/admin-page.php';
	}

	/**
	 * Save admin settings.
	 *
	 * @return void
	 */
	public function save_form() {
		check_admin_referer( 'replybox_settings' );

		$site_id = sanitize_text_field( $_POST['site_id'] );
		$site_lang = sanitize_text_field( $_POST['param_lang'] );
		$site_order = sanitize_text_field( $_POST['param_order'] );

		$this->update_option( 'site_id', $site_id )->save_options();
		$this->update_option( 'param_lang', $site_lang )->save_options();
		$this->update_option( 'param_order', $site_order )->save_options();

		if ( ! isset( $_POST['_wp_http_referer'] ) ) {
			$_POST['_wp_http_referer'] = wp_login_url();
		}

		$url = sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) );
		$url = add_query_arg( 'settings-updated', 'true', $url );

		wp_safe_redirect( urldecode( $url ) );
		exit;
	}

	/**
	 * Register our API endpoints.
	 *
	 * @return void
	 */
	public function register_api_endpoints() {
		register_rest_route( 'replybox/v1', '/comments', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_comments_endpoint' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'page'     => array(
					'default'           => 1,
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				),
				'per_page' => array(
					'default'           => 100,
					'validate_callback' => function ( $param ) {
						return is_numeric( $param );
					},
				),
				'token'    => array(
					'required' => true,
					'type'     => 'string',
				),
			),
		) );

		register_rest_route( 'replybox/v1', '/comments', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'post_comments_endpoint' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'token' => array(
					'required' => true,
					'type'     => 'string',
				),
			),
		) );

		register_rest_route( 'replybox/v1', '/comments', array(
			'methods'             => 'PATCH',
			'callback'            => array( $this, 'patch_comments_endpoint' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'token' => array(
					'required' => true,
					'type'     => 'string',
				),
			),
		) );

		register_rest_route( 'replybox/v1', '/comments', array(
			'methods'             => 'DELETE',
			'callback'            => array( $this, 'delete_comments_endpoint' ),
			'permission_callback' => '__return_true',
			'args'                => array(
				'token' => array(
					'required' => true,
					'type'     => 'string',
				),
			),
		) );
	}

	/**
	 * GET comments API endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function get_comments_endpoint( $request ) {
		if ( $this->get_option( 'secure_token' ) !== $request['token'] ) {
			return new WP_Error( 'token_incorrect', __( 'Sorry, incorrect secure token.', 'replybox' ),
				array( 'status' => 403 ) );
		}

		$query    = new WP_Comment_Query;
		$comments = $query->query( array(
			'type'    => 'comment',
			'orderby' => 'id',
			'order'   => 'asc',
			'number'  => $request['per_page'],
			'offset'  => $request['per_page'] * ( absint( $request['page'] ) - 1 ),
		) );

		$query = new WP_Comment_Query;
		$count = $query->query( array(
			'type'  => 'comment',
			'count' => true,
		) );
		$pages = ceil( $count / $request['per_page'] );

		return array(
			'total'    => (int) $count,
			'pages'    => (int) $pages,
			'comments' => $this->prepare_comments( $comments ),
		);
	}

	/**
	 * POST comments API endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function post_comments_endpoint( $request ) {
		if ( $this->get_option( 'secure_token' ) !== $request['token'] ) {
			return new WP_Error( 'token_incorrect', __( 'Sorry, incorrect secure token.', 'replybox' ),
				array( 'status' => 403 ) );
		}

		$user     = get_user_by( 'email', $request['email'] );
		$approved = 'approved' === $request['status'] ? 1 : 0;

		$id = wp_insert_comment( array(
			'comment_post_ID'      => (int) $request['post'],
			'user_id'              => $user ? $user->ID : 0,
			'comment_author'       => $user ? $user->display_name : $request['name'],
			'comment_author_email' => $request['email'],
			'comment_author_url'   => '',
			'comment_content'      => $request['content'],
			'comment_parent'       => (int) $request['parent'],
			'comment_agent'        => 'ReplyBox',
			'comment_approved'     => $request['spam'] ? 'spam' : $approved,
			'comment_date_gmt'     => $request['date_gmt'],
			'comment_date'         => get_date_from_gmt( $request['date_gmt'] ),
		), true );

		if ( $id ) {
			add_comment_meta( $id, 'replybox_id', (int) $request['id'], true );
		}

		return $id;
	}

	/**
	 * PATCH comments API endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function patch_comments_endpoint( $request ) {
		if ( $this->get_option( 'secure_token' ) !== $request['token'] ) {
			return new WP_Error( 'token_incorrect', __( 'Sorry, incorrect secure token.', 'replybox' ),
				array( 'status' => 403 ) );
		}

		$user     = get_user_by( 'email', $request['email'] );
		$approved = 'approved' === $request['status'] ? 1 : 0;

		wp_update_comment( array(
			'comment_ID'           => $request['id'],
			'user_id'              => $user ? $user->ID : 0,
			'comment_author'       => $user ? $user->display_name : $request['name'],
			'comment_author_email' => $request['email'],
			'comment_author_url'   => '',
			'comment_content'      => $request['content'],
			'comment_parent'       => (int) $request['parent'],
			'comment_agent'        => 'ReplyBox',
			'comment_approved'     => $request['spam'] ? 'spam' : $approved,
			'comment_date_gmt'     => $request['date_gmt'],
			'comment_date'         => get_date_from_gmt( $request['date_gmt'] ),
		) );
	}

	/**
	 * DELETE comments API endpoint.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|WP_Error
	 */
	public function delete_comments_endpoint( $request ) {
		if ( $this->get_option( 'secure_token' ) !== $request['token'] ) {
			return new WP_Error( 'token_incorrect', __( 'Sorry, incorrect secure token.', 'replybox' ),
				array( 'status' => 403 ) );
		}

		wp_delete_comment( $request['id'], true );
	}

	/**
	 * Prepare comments for response.
	 *
	 * @param array $comments
	 *
	 * @return array
	 */
	private function prepare_comments( $comments ) {
		foreach ( $comments as $key => $comment ) {
			$comments[ $key ] = array(
				'id'         => $comment->comment_ID,
				'post'       => $comment->comment_post_ID,
				'post_url'   => get_permalink( $comment->comment_post_ID ),
				'post_title' => get_the_title( $comment->comment_post_ID ),
				'parent'     => $comment->comment_parent,
				'user_name'  => $comment->comment_author,
				'user_email' => $comment->comment_author_email,
				'content'    => $comment->comment_content,
				'approved'   => $comment->comment_approved,
				'date_gmt'   => $comment->comment_date_gmt,
			);
		}

		return $comments;
	}

	/**
	 * Get the URL of the embed script.
	 *
	 * @return string
	 */
	private function get_embed_url() {
		return apply_filters( 'replybox_embed_url', 'https://cdn.getreplybox.com/js/embed.js' );
	}

	/**
	 * Replace the default WordPress comments.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function comments_template( $file ) {
		$embed_path = $this->get_embed_path();

		if ( ! $embed_path ) {
			return $file;
		}

		return $embed_path;
	}

	/**
	 * Replace the default WordPress comments block.
	 *
	 * @param string $pre_render
	 * @param string $parsed_block
	 *
	 * @return string
	 */
	public function replace_comment_block( $pre_render, $parsed_block ) {
		if ( 'core/comments' !== $parsed_block['blockName'] ) {
			return $pre_render;
		}

		$embed_path = $this->get_embed_path();

		if ( ! $embed_path || ! file_exists( $embed_path ) ) {
			return $pre_render;
		}

		ob_start();

		include $embed_path;

		return ob_get_clean();
	}

	/**
	 * Enqueue and get the embed script.
	 *
	 * @return false|string
	 */
	public function get_embed_path() {
		global $post;

		if ( ! $this->should_show_comment_embed( $post ) ) {
			return false;
		}

		$data = array(
			'site'       => $this->get_option( 'site_id' ),
			'sort'      => $this->get_option( 'param_order' ),
			'lang'       => $this->get_option( 'param_lang' ),
			'identifier' => $post->ID,
		);

		if ( defined( 'REPLYBOX_SSO_KEY' ) && REPLYBOX_SSO_KEY ) {
			$user    = wp_get_current_user();
			$payload = array(
				'user'      => array(
					'name'      => $user->display_name ?? null,
					'email'     => $user->user_email ?? null,
					'photo_url' => $user ? get_avatar_url( $user ) : null,
				),
				'login_url' => defined( 'REPLYBOX_SSO_LOGIN_URL' ) ? REPLYBOX_SSO_LOGIN_URL : null,
			);
			$payload = base64_encode( json_encode( apply_filters( 'replybox_sso_payload', $payload, $user, $post ) ) );

			$data['sso'] = array(
				'hash'    => hash_hmac( 'sha256', $payload, REPLYBOX_SSO_KEY ),
				'payload' => $payload,
			);
		}

		wp_enqueue_script( 'replybox-js', $this->get_embed_url(), array(), null, true );
		wp_localize_script( 'replybox-js', 'replybox', $data );

		return plugin_dir_path( __FILE__ ) . 'views/comments.php';
	}

	/**
	 * Should we show the comment embed?
	 *
	 * @param WP_Post $post
	 *
	 * @return bool
	 */
	protected function should_show_comment_embed( $post ) {
		$show_embed = is_singular() && post_type_supports( $post->post_type, 'comments' ) && comments_open( $post );

		if ( function_exists( 'is_product' ) && is_product() ) {
			$show_embed = false;
		}

		return apply_filters( 'replybox_show_embed', $show_embed, $post );
	}

	/**
	 * Filter a single comment link.
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	public function comment_link( $link, $comment ) {
		$hash = '#replybox';
		$id   = get_comment_meta( $comment->comment_ID, 'replybox_id', true );

		if ( $id ) {
			$hash = '#comment-' . $id;
		}

		return preg_replace( '/#comment-\d+/', $hash, $link );
	}

	/**
	 * Filter a post's comments link.
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	public function comments_link( $link ) {
		return str_replace( '#comments', '#replybox', $link );
	}

	/**
	 * Plugin activated.
	 *
	 * @return void
	 */
	public function activate() {
		$value = $this->get_option( 'secure_token' );

		if ( empty( $value ) ) {
			$this->generate_token();
		}

		$this->welcome->activate();
	}

	/**
	 * Remove comments from the admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function remove_from_admin_bar( $wp_admin_bar ) {
		$wp_admin_bar->remove_node( 'comments' );
	}

	/**
	 * Remove pending from comment counts.
	 *
	 * @param array $stats
	 * @param int $post_id
	 *
	 * @return bool|mixed|object
	 */
	public function count_comments( $stats, $post_id ) {
		$count = wp_cache_get( "comments-{$post_id}", 'counts' );
		if ( false !== $count ) {
			return $count;
		}

		$stats              = get_comment_count( $post_id );
		$stats['moderated'] = 0;
		unset( $stats['awaiting_moderation'] );

		$stats_object = (object) $stats;
		wp_cache_set( "comments-{$post_id}", $stats_object, 'counts' );

		return $stats_object;
	}

	/**
	 * Remove checkboxes from comments table.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function comments_columns( $columns ) {
		unset( $columns['cb'] );

		return $columns;
	}

	/**
	 * Hide bulk actions.
	 *
	 * @return array
	 */
	public function comments_bulk_actions() {
		return array();
	}

	/**
	 * Hide row actions.
	 *
	 * @return array
	 */
	public function comments_row_actions() {
		return array();
	}

	/**
	 * Don't show pending comments status.
	 *
	 * @param array $status_links
	 *
	 * @return array
	 */
	public function comments_status_links( $status_links ) {
		unset( $status_links['moderated'] );

		return $status_links;
	}

	/**
	 * Add ReplyBox notice on comment page.
	 *
	 * @return void
	 */
	public function add_replybox_comments_notice() {
		global $pagenow;

		if ( empty( $pagenow ) || 'edit-comments.php' !== $pagenow || apply_filters( 'replybox_hide_comments_notice', false ) ) {
			return;
		}

		$site_id = $this->get_option( 'site_id' );
		$url     = ! empty( $site_id ) ? 'https://app.getreplybox.com/sites/' . $site_id : 'https://app.getreplybox.com';

		?>
		<style>
			.notice-replybox {
				border-left-color: #6C2BD9;
			}

			.notice-replybox a {
				color: #6C2BD9;
			}

			.notice-replybox a:hover,
			.notice-replybox a:active,
			.notice-replybox a:focus {
				color: #551fb2;
			}

			.wp-core-ui .notice-replybox .button-primary {
				background: #6C2BD9;
				border-color: #6C2BD9;
			}

			.wp-core-ui .notice-replybox .button-primary:hover,
			.wp-core-ui .notice-replybox .button-primary:active,
			.wp-core-ui .notice-replybox .button-primary:focus {
				background: #551fb2;
				border-color: #551fb2;
			}
		</style>
		<div class="notice notice-info notice-replybox">
			<p><strong><?php _e( 'You are using ReplyBox!', 'replybox' ); ?></strong> <?php _e( "Please go to the ReplyBox app to manage your site's comments.", 'replybox' ); ?></p>
			<p><a href="<?php echo esc_url( $url ); ?>" class="button button-primary" target="_blank"><?php _e( 'Manage comments in ReplyBox', 'replybox' ); ?></a> <span style="margin: 0 4px;">or</span> <a href="https://getreplybox.com/?utm_source=replybox&utm_medium=plugin&utm_campaign=comments-notice" target="_blank">learn more</a> </p>
		</div>
		<?php
	}

	/*
	 * Don't allow comments to be posted to wp-comments-post.php.
	 *
	 * @return void
	 */
	public function disable_comments_post() {
		global $pagenow;

		if ( empty( $pagenow ) || 'wp-comments-post.php' !== $pagenow ) {
			return;
		}

		wp_die( __( "Sorry, you can't post comments using this method.", 'replybox' ), '', array( 'response' => 403 ) );
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * class via the `new` operator from outside of this class.
	 */
	private function __construct() {
		//
	}

	/**
	 * As this class is a singleton it should not be clone-able.
	 */
	private function __clone() {
		//
	}

	/**
	 * As this class is a singleton it should not be able to be unserialized.
	 */
	public function __wakeup() {
		//
	}
}

/**
 * Return the ReplyBox instance.
 *
 * @return ReplyBox
 */
function getreplybox() {
	return ReplyBox::instance();
}

// Let's go!
getreplybox();
