<?php
/**
 * Functions of BuddyPress's Legacy theme.
 *
 * @since 1.7.0
 *
 * @package BuddyPress
 * @subpackage BP_Theme_Compat
 * @version 14.3.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/** Theme Setup ***************************************************************/

if ( !class_exists( 'BP_Legacy' ) ) :

/**
 * Loads BuddyPress Legacy Theme functionality.
 *
 * This is not a real theme by WordPress standards, and is instead used as the
 * fallback for any WordPress theme that does not have BuddyPress templates in it.
 *
 * To make your custom theme BuddyPress compatible and customize the templates, you
 * can copy these files into your theme without needing to merge anything
 * together; BuddyPress should safely handle the rest.
 *
 * See @link BP_Theme_Compat() for more.
 *
 * @since 1.7.0
 *
 * @package BuddyPress
 * @subpackage BP_Theme_Compat
 */
class BP_Legacy extends BP_Theme_Compat {

	/** Functions *************************************************************/

	/**
	 * The main BuddyPress (Legacy) Loader.
	 *
	 * @since 1.7.0
	 *
	 */
	public function __construct() {
		parent::start();
	}

	/**
	 * Component global variables.
	 *
	 * You'll want to customize the values in here, so they match whatever your
	 * needs are.
	 *
	 * @since 1.7.0
	 */
	protected function setup_globals() {
		$bp            = buddypress();
		$this->id      = 'legacy';
		$this->name    = 'BP Legacy';
		$this->version = bp_get_version();
		$this->dir     = trailingslashit( $bp->themes_dir . '/bp-legacy' );
		$this->url     = trailingslashit( $bp->themes_url . '/bp-legacy' );
	}

	/**
	 * Setup the theme hooks.
	 *
	 * @since 1.7.0
	 *
	 */
	protected function setup_actions() {

		// Template Output.
		add_filter( 'bp_get_activity_action_pre_meta', array( $this, 'secondary_avatars' ), 10, 2 );

		// Filter BuddyPress template hierarchy and look for page templates.
		add_filter( 'bp_get_buddypress_template', array( $this, 'theme_compat_page_templates' ), 10, 1 );

		/** Scripts ***********************************************************/

		add_action( 'bp_enqueue_community_scripts', array( $this, 'enqueue_styles'   ) ); // Enqueue theme CSS
		add_action( 'bp_enqueue_community_scripts', array( $this, 'enqueue_scripts'  ) ); // Enqueue theme JS
		add_action( 'bp_enqueue_community_scripts', array( $this, 'localize_scripts' ) ); // Enqueue theme script localization

		/** This filter is documented in bp-core/bp-core-dependency.php */
		if ( is_buddypress() || ! apply_filters( 'bp_enqueue_assets_in_bp_pages_only', true ) ) {
			// Body no-js class.
			add_filter( 'body_class', array( $this, 'add_nojs_body_class' ), 20, 1 );
		}

		/** Buttons ***********************************************************/

		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			// Register buttons for the relevant component templates
			// Friends button.
			if ( bp_is_active( 'friends' ) )
				add_action( 'bp_member_header_actions',    'bp_add_friend_button',           5 );

			// Activity button.
			if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() )
				add_action( 'bp_member_header_actions',    'bp_send_public_message_button',  20 );

			// Messages button.
			if ( bp_is_active( 'messages' ) )
				add_action( 'bp_member_header_actions',    'bp_send_private_message_button', 20 );

			// Group buttons.
			if ( bp_is_active( 'groups' ) ) {
				add_action( 'bp_group_header_actions',          'bp_group_join_button',               5           );
				add_action( 'bp_directory_groups_actions',      'bp_group_join_button'                            );
				add_action( 'bp_groups_directory_group_filter', 'bp_legacy_theme_group_create_nav', 999           );
				add_action( 'bp_after_group_admin_content',     'bp_legacy_groups_admin_screen_hidden_input'      );
				add_action( 'bp_before_group_admin_form',       'bp_legacy_theme_group_manage_members_add_search' );
			}

			// Blog button.
			if ( bp_is_active( 'blogs' ) ) {
				add_action( 'bp_directory_blogs_actions',    'bp_blogs_visit_blog_button'           );
				add_action( 'bp_blogs_directory_blog_types', 'bp_legacy_theme_blog_create_nav', 999 );
			}
		}

		/** Notices ***********************************************************/

		// Only hook the 'sitewide_notices' overlay if the Sitewide
		// Notices widget is not in use (to avoid duplicate content).
		if ( bp_is_active( 'messages' ) && ! bp_is_widget_block_active( 'bp/sitewide-notices', 'bp_messages_sitewide_notices_widget' ) ) {
			add_action( 'wp_footer', array( $this, 'sitewide_notices' ), 9999 );
		}

		/** Ajax **************************************************************/

		$actions = array(

			// Directory filters.
			'blogs_filter'    => 'bp_legacy_theme_object_template_loader',
			'forums_filter'   => 'bp_legacy_theme_object_template_loader',
			'groups_filter'   => 'bp_legacy_theme_object_template_loader',
			'members_filter'  => 'bp_legacy_theme_object_template_loader',
			'messages_filter' => 'bp_legacy_theme_messages_template_loader',
			'invite_filter'   => 'bp_legacy_theme_invite_template_loader',
			'requests_filter' => 'bp_legacy_theme_requests_template_loader',

			// Friends.
			'accept_friendship' => 'bp_legacy_theme_ajax_accept_friendship',
			'addremove_friend'  => 'bp_legacy_theme_ajax_addremove_friend',
			'reject_friendship' => 'bp_legacy_theme_ajax_reject_friendship',

			// Activity.
			'activity_get_older_updates'  => 'bp_legacy_theme_activity_template_loader',
			'activity_mark_fav'           => 'bp_legacy_theme_mark_activity_favorite',
			'activity_mark_unfav'         => 'bp_legacy_theme_unmark_activity_favorite',
			'activity_widget_filter'      => 'bp_legacy_theme_activity_template_loader',
			'delete_activity'             => 'bp_legacy_theme_delete_activity',
			'delete_activity_comment'     => 'bp_legacy_theme_delete_activity_comment',
			'get_single_activity_content' => 'bp_legacy_theme_get_single_activity_content',
			'new_activity_comment'        => 'bp_legacy_theme_new_activity_comment',
			'post_update'                 => 'bp_legacy_theme_post_update',
			'bp_spam_activity'            => 'bp_legacy_theme_spam_activity',
			'bp_spam_activity_comment'    => 'bp_legacy_theme_spam_activity',

			// Groups.
			'groups_invite_user' => 'bp_legacy_theme_ajax_invite_user',
			'joinleave_group'    => 'bp_legacy_theme_ajax_joinleave_group',

			// Messages.
			'messages_autocomplete_results' => 'bp_legacy_theme_ajax_messages_autocomplete_results',
			'messages_close_notice'         => 'bp_legacy_theme_ajax_close_notice',
			'messages_delete'               => 'bp_legacy_theme_ajax_messages_delete',
			'messages_markread'             => 'bp_legacy_theme_ajax_message_markread',
			'messages_markunread'           => 'bp_legacy_theme_ajax_message_markunread',
			'messages_send_reply'           => 'bp_legacy_theme_ajax_messages_send_reply',
		);

		// Conditional actions.
		if ( bp_is_active( 'messages', 'star' ) ) {
			$actions['messages_star'] = 'bp_legacy_theme_ajax_messages_star_handler';
		}

		/**
		 * Register all of these AJAX handlers.
		 *
		 * The "wp_ajax_" action is used for logged in users, and "wp_ajax_nopriv_"
		 * executes for users that aren't logged in. This is for backpat with BP <1.6.
		 */
		foreach ( $actions as $name => $function ) {
			bp_ajax_register_action( $name );
			add_action( 'wp_ajax_'        . $name, $function );
			add_action( 'wp_ajax_nopriv_' . $name, $function );
		}

		// Register the heartbeat Ajax action.
		bp_ajax_register_action( 'heartbeat' );

		add_filter( 'bp_ajax_querystring', 'bp_legacy_theme_ajax_querystring', 10, 2 );

		/** Override **********************************************************/

		/**
		 * Fires after all of the BuddyPress theme compat actions have been added.
		 *
		 * @since 1.7.0
		 *
		 * @param BP_Legacy $template_pack Current Template Pack instance.
		 */
		do_action_ref_array( 'bp_theme_compat_actions', array( &$this ) );
	}

	/**
	 * Load the theme CSS
	 *
	 * @since 1.7.0
	 * @since 2.3.0 Support custom CSS file named after the current theme or parent theme.
	 *
	 */
	public function enqueue_styles() {
		$min = bp_core_get_minified_asset_suffix();

		// Locate the BP stylesheet.
		$ltr = $this->locate_asset_in_stack( "buddypress{$min}.css",     'css' );

		// LTR.
		if ( ! is_rtl() && isset( $ltr['location'], $ltr['handle'] ) ) {
			wp_enqueue_style( $ltr['handle'], $ltr['location'], array( 'bp-tooltips' ), $this->version, 'screen' );

			if ( $min ) {
				wp_style_add_data( $ltr['handle'], 'suffix', $min );
			}
		}

		// RTL.
		if ( is_rtl() ) {
			$rtl = $this->locate_asset_in_stack( "buddypress-rtl{$min}.css", 'css' );

			if ( isset( $rtl['location'], $rtl['handle'] ) ) {
				$rtl['handle'] = str_replace( '-css', '-css-rtl', $rtl['handle'] );  // Backwards compatibility.
				wp_enqueue_style( $rtl['handle'], $rtl['location'], array( 'bp-tooltips' ), $this->version, 'screen' );

				if ( $min ) {
					wp_style_add_data( $rtl['handle'], 'suffix', $min );
				}
			}
		}

		// Compatibility stylesheets for specific themes.
		$theme = $this->locate_asset_in_stack( get_template() . "{$min}.css", 'css' );
		if ( ! is_rtl() && isset( $theme['location'] ) ) {
			// Use a unique handle.
			$theme['handle'] = 'bp-' . get_template();
			wp_enqueue_style( $theme['handle'], $theme['location'], array( 'bp-tooltips' ), $this->version, 'screen' );

			if ( $min ) {
				wp_style_add_data( $theme['handle'], 'suffix', $min );
			}
		}

		// Compatibility stylesheet for specific themes, RTL-version.
		if ( is_rtl() ) {
			$theme_rtl = $this->locate_asset_in_stack( get_template() . "-rtl{$min}.css", 'css' );

			if ( isset( $theme_rtl['location'] ) ) {
				$theme_rtl['handle'] = $theme['handle'] . '-rtl';
				wp_enqueue_style( $theme_rtl['handle'], $theme_rtl['location'], array( 'bp-tooltips' ), $this->version, 'screen' );

				if ( $min ) {
					wp_style_add_data( $theme_rtl['handle'], 'suffix', $min );
				}
			}
		}
	}

	/**
	 * Enqueue the required JavaScript files
	 *
	 * @since 1.7.0
	 */
	public function enqueue_scripts() {
		$min = bp_core_get_minified_asset_suffix();

		// Locate the BP JS file.
		$asset = $this->locate_asset_in_stack( "buddypress{$min}.js", 'js' );

		// Enqueue the global JS, if found - AJAX will not work
		// without it.
		if ( isset( $asset['location'], $asset['handle'] ) ) {
			wp_enqueue_script( $asset['handle'], $asset['location'], bp_core_get_js_dependencies(), $this->version );
		}

		/**
		 * Filters whether directory filter settings ('scope', etc) should be stored in a persistent cookie.
		 *
		 * @since 4.0.0
		 *
		 * @param bool $store_filter_settings Whether to store settings. Defaults to true for logged-in users.
		 */
		$store_filter_settings = apply_filters( 'bp_legacy_store_filter_settings', is_user_logged_in() );

		/**
		 * Filters core JavaScript strings for internationalization before AJAX usage.
		 *
		 * @since 2.0.0
		 *
		 * @param array $value Array of key/value pairs for AJAX usage.
		 */
		$params = apply_filters( 'bp_core_get_js_strings', array(
			// Strings for display.
			'accepted'            => __( 'Accepted', 'buddypress' ),
			'close'               => __( 'Close', 'buddypress' ),
			'comments'            => __( 'comments', 'buddypress' ),
			'leave_group_confirm' => __( 'Are you sure you want to leave this group?', 'buddypress' ),
			'mark_as_fav'	      => __( 'Favorite', 'buddypress' ),
			'my_favs'             => __( 'My Favorites', 'buddypress' ),
			'rejected'            => __( 'Rejected', 'buddypress' ),
			'remove_fav'	      => __( 'Remove Favorite', 'buddypress' ),
			'show_all'            => __( 'Show all', 'buddypress' ),
			'show_all_comments'   => __( 'Show all comments for this thread', 'buddypress' ),

			/* translators: %s: number of activity comments */
			'show_x_comments'     => __( 'Show all comments (%d)', 'buddypress' ),
			'unsaved_changes'     => __( 'Your profile has unsaved changes. If you leave the page, the changes will be lost.', 'buddypress' ),
			'view'                => __( 'View', 'buddypress' ),

			// Settings.
			'store_filter_settings' => $store_filter_settings,
			'canonical_url'         => bp_get_canonical_url(),
		) );
		wp_localize_script( $asset['handle'], 'BP_DTheme', $params );

		// Maybe enqueue comment reply JS.
		if ( is_singular() && bp_is_blog_page() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Maybe enqueue password verify JS (register page or user settings page).
		if ( bp_is_register_page() || ( function_exists( 'bp_is_user_settings_general' ) && bp_is_user_settings_general() ) ) {

			// Locate the Register Page JS file.
			$asset = $this->locate_asset_in_stack( "password-verify{$min}.js", 'js', 'bp-legacy-password-verify' );

			$dependencies = array_merge( bp_core_get_js_dependencies(), array(
				'password-strength-meter',
			) );

			// Enqueue script.
			wp_enqueue_script( $asset['handle'] . '-password-verify', $asset['location'], $dependencies, $this->version );
			wp_localize_script(
				$asset['handle'] . '-password-verify',
				'bpPasswordVerify',
				array(
					'tooWeakPasswordWarning' => __( 'Your password is too weak, please use a stronger password.', 'buddypress' ),
					'requiredPassStrength'   => bp_members_user_pass_required_strength(),
				)
			);
		}

		// Star private messages.
		if ( bp_is_active( 'messages', 'star' ) && bp_is_user_messages() ) {
			wp_localize_script( $asset['handle'], 'BP_PM_Star', array(
				'strings' => array(
					'text_unstar'  => __( 'Unstar', 'buddypress' ),
					'text_star'    => __( 'Star', 'buddypress' ),
					'title_unstar' => __( 'Starred', 'buddypress' ),
					'title_star'   => __( 'Not starred', 'buddypress' ),
					'title_unstar_thread' => __( 'Remove all starred messages in this thread', 'buddypress' ),
					'title_star_thread'   => __( 'Star the first message in this thread', 'buddypress' ),
				),
				'is_single_thread' => (int) bp_is_messages_conversation(),
				'star_counter'     => 0,
				'unstar_counter'   => 0
			) );
		}
	}

	/**
	 * Get the URL and handle of a web-accessible CSS or JS asset
	 *
	 * We provide two levels of customizability with respect to where CSS
	 * and JS files can be stored: (1) the child theme/parent theme/theme
	 * compat hierarchy, and (2) the "template stack" of /buddypress/css/,
	 * /community/css/, and /css/. In this way, CSS and JS assets can be
	 * overloaded, and default versions provided, in exactly the same way
	 * as corresponding PHP templates.
	 *
	 * We are duplicating some of the logic that is currently found in
	 * bp_locate_template() and the _template_stack() functions. Those
	 * functions were built with PHP templates in mind, and will require
	 * refactoring in order to provide "stack" functionality for assets
	 * that must be accessible both using file_exists() (the file path)
	 * and at a public URI.
	 *
	 * This method is marked private, with the understanding that the
	 * implementation is subject to change or removal in an upcoming
	 * release, in favor of a unified _template_stack() system. Plugin
	 * and theme authors should not attempt to use what follows.
	 *
	 * @since 1.8.0
	 * @param string $file A filename like buddypress.css.
	 * @param string $type Optional. Either "js" or "css" (the default).
	 * @param string $script_handle Optional. If set, used as the script name in `wp_enqueue_script`.
	 * @return array An array of data for the wp_enqueue_* function:
	 *   'handle' (eg 'bp-child-css') and a 'location' (the URI of the
	 *   asset)
	 */
	private function locate_asset_in_stack( $file, $type = 'css', $script_handle = '' ) {
		$locations = array();

		// Ensure the assets can be located when running from /src/.
		if ( bp_is_running_from_src_subdirectory() ) {
			$file = str_replace( '.min', '', $file );
		}

		// No need to check child if template == stylesheet.
		if ( is_child_theme() ) {
			$locations[] = array(
				'type' => 'bp-child',
				'dir'  => get_stylesheet_directory(),
				'uri'  => get_stylesheet_directory_uri(),
				'file' => $file,
			);

			$locations[] = array(
				'type' => 'bp-child',
				'dir'  => get_stylesheet_directory(),
				'uri'  => get_stylesheet_directory_uri(),
				'file' => str_replace( '.min', '', $file ),
			);
		}

		$locations[] = array(
			'type' => 'bp-parent',
			'dir'  => get_template_directory(),
			'uri'  => get_template_directory_uri(),
			'file' => str_replace( '.min', '', $file ),
		);

		$locations[] = array(
			'type' => 'bp-legacy',
			'dir'  => bp_get_theme_compat_dir(),
			'uri'  => bp_get_theme_compat_url(),
			'file' => $file,
		);

		// Subdirectories within the top-level $locations directories.
		$subdirs = array(
			'buddypress/' . $type,
			'community/' . $type,
			$type,
		);

		$retval = array();

		foreach ( $locations as $location ) {
			foreach ( $subdirs as $subdir ) {
				if ( file_exists( trailingslashit( $location['dir'] ) . trailingslashit( $subdir ) . $location['file'] ) ) {
					$retval['location'] = trailingslashit( $location['uri'] ) . trailingslashit( $subdir ) . $location['file'];
					$retval['handle']   = ( $script_handle ) ? $script_handle : "{$location['type']}-{$type}";

					break 2;
				}
			}
		}

		return $retval;
	}

	/**
	 * Adds the no-js class to the body tag.
	 *
	 * This function ensures that the <body> element will have the 'no-js' class by default. If you're
	 * using JavaScript for some visual functionality in your theme, and you want to provide noscript
	 * support, apply those styles to body.no-js.
	 *
	 * The no-js class is removed by the JavaScript created in buddypress.js.
	 *
	 * @since 1.7.0
	 *
	 * @param array $classes Array of classes to append to body tag.
	 * @return array $classes
	 */
	public function add_nojs_body_class( $classes ) {
		if ( ! in_array( 'no-js', $classes ) )
			$classes[] = 'no-js';

		return array_unique( $classes );
	}

	/**
	 * Load localizations for topic script.
	 *
	 * These localizations require information that may not be loaded even by init.
	 *
	 * @since 1.7.0
	 */
	public function localize_scripts() {
	}

	/**
	 * Outputs sitewide notices markup in the footer.
	 *
	 * @since 1.7.0
	 *
	 * @see https://buddypress.trac.wordpress.org/ticket/4802
	 */
	public function sitewide_notices() {
		// Do not show notices if user is not logged in.
		if ( ! is_user_logged_in() || is_admin() ) {
			return;
		}

		// Add a class to determine if the admin bar is on or not.
		$class = did_action( 'admin_bar_menu' ) ? 'admin-bar-on' : 'admin-bar-off';

		echo '<div id="sitewide-notice" class="' . esc_attr( $class ) . '">';
		bp_message_get_notices();
		echo '</div>';
	}

	/**
	 * Add secondary avatar image to this activity stream's record, if supported.
	 *
	 * @since 1.7.0
	 *
	 * @param string               $action   The text of this activity.
	 * @param BP_Activity_Activity $activity Activity object.
	 * @return string
	 */
	function secondary_avatars( $action, $activity ) {
		switch ( $activity->component ) {
			case 'groups' :
			case 'friends' :
				// Only insert avatar if one exists.
				if ( $secondary_avatar = bp_get_activity_secondary_avatar() ) {
					$reverse_content = strrev( $action );
					$position        = strpos( $reverse_content, 'a<' );
					$action          = substr_replace( $action, $secondary_avatar, -$position - 2, 0 );
				}
				break;
		}

		return $action;
	}

	/**
	 * Filter the default theme compatibility root template hierarchy, and prepend
	 * a page template to the front if it's set.
	 *
	 * @see https://buddypress.trac.wordpress.org/ticket/6065
	 *
	 * @since 2.2.0
	 *
	 * @param  array $templates Array of templates.
	 *                         to use the defined page template for component's directory and its single items
	 * @return array
	 */
	public function theme_compat_page_templates( $templates = array() ) {

		/**
		 * Filters whether or not we are looking at a directory to determine if to return early.
		 *
		 * @since 2.2.0
		 *
		 * @param bool $value Whether or not we are viewing a directory.
		 */
		if ( true === (bool) apply_filters( 'bp_legacy_theme_compat_page_templates_directory_only', ! bp_is_directory() ) ) {
			return $templates;
		}

		// No page ID yet.
		$page_id = 0;

		// Get the WordPress Page ID for the current view.
		foreach ( (array) buddypress()->pages as $component => $bp_page ) {

			// Handles the majority of components.
			if ( bp_is_current_component( $component ) ) {
				$page_id = (int) $bp_page->id;
			}

			// Stop if not on a user page.
			if ( ! bp_is_user() && ! empty( $page_id ) ) {
				break;
			}

			// The Members component requires an explicit check due to overlapping components.
			if ( bp_is_user() && ( 'members' === $component ) ) {
				$page_id = (int) $bp_page->id;
				break;
			}
		}

		// Bail if no directory page set.
		if ( 0 === $page_id ) {
			return $templates;
		}

		// Check for page template.
		$page_template = get_page_template_slug( $page_id );

		// Add it to the beginning of the templates array so it takes precedence
		// over the default hierarchy.
		if ( ! empty( $page_template ) ) {

			/**
			 * Check for existence of template before adding it to template
			 * stack to avoid accidentally including an unintended file.
			 *
			 * @see: https://buddypress.trac.wordpress.org/ticket/6190
			 */
			if ( '' !== locate_template( $page_template ) ) {
				array_unshift( $templates, $page_template );
			}
		}

		return $templates;
	}
}
new BP_Legacy();
endif;

/**
 * Add the Create a Group button to the Groups directory title.
 *
 * The bp-legacy puts the Create a Group button into the page title, to mimic
 * the behavior of bp-default.
 *
 * @since 2.0.0
 * @deprecated 12.0.0
 *
 * @param string $title Groups directory title.
 * @return string
 */
function bp_legacy_theme_group_create_button( $title ) {
	_deprecated_function( __FUNCTION__, '12.0.0' );
	return $title . ' ' . bp_get_group_create_button();
}

/**
 * Add the Create a Group nav to the Groups directory navigation.
 *
 * The bp-legacy puts the Create a Group nav at the last position of
 * the Groups directory navigation.
 *
 * @since 2.2.0
 *
 */
function bp_legacy_theme_group_create_nav() {
	bp_group_create_nav_item();
}

/**
 * Renders the group ID hidden input on group admin screens.
 *
 * @since 2.7.0
 *
 * @return string|null html
 */
function bp_legacy_groups_admin_screen_hidden_input() {
	?>
 	<input type="hidden" name="group-id" id="group-id" value="<?php bp_group_id(); ?>" />
	<?php
}

/**
 * Add the Create a Site button to the Sites directory title.
 *
 * The bp-legacy puts the Create a Site button into the page title, to mimic
 * the behavior of bp-default.
 *
 * @since 2.0.0
 * @deprecated 12.0.0
 *
 * @param string $title Sites directory title.
 * @return string
 */
function bp_legacy_theme_blog_create_button( $title ) {
	_deprecated_function( __FUNCTION__, '12.0.0' );
	return $title . ' ' . bp_get_blog_create_button();
}

/**
 * Add the Create a Site nav to the Sites directory navigation.
 *
 * The bp-legacy puts the Create a Site nav at the last position of
 * the Sites directory navigation.
 *
 * @since 2.2.0
 *
 */
function bp_legacy_theme_blog_create_nav() {
	bp_blog_create_nav_item();
}

/**
 * This function looks scarier than it actually is. :)
 * Each object loop (activity/members/groups/blogs/forums) contains default
 * parameters to show specific information based on the page we are currently
 * looking at.
 *
 * The following function will take into account any cookies set in the JS and
 * allow us to override the parameters sent. That way we can change the results
 * returned without reloading the page.
 *
 * By using cookies we can also make sure that user settings are retained
 * across page loads.
 *
 * @since 1.2.0
 *
 * @param string $query_string Query string for the current request.
 * @param string $object       Object for cookie.
 * @return string Query string for the component loops.
 */
function bp_legacy_theme_ajax_querystring( $query_string, $object ) {
	if ( empty( $object ) ) {
		return '';
	}

	// Set up the cookies passed on this AJAX request. Store a local var to avoid conflicts.
	if ( ! empty( $_POST['cookie'] ) ) {
		$_BP_COOKIE = bp_parse_args(
			str_replace( '; ', '&', urldecode( $_POST['cookie'] ) )
		);
	} elseif ( ! empty( $_POST['data']['bp_heartbeat'] ) ) {
		$_BP_COOKIE = bp_parse_args(
			str_replace( '; ', '&', urldecode( $_POST['data']['bp_heartbeat'] ) )
		);
	} else {
		$_BP_COOKIE = &$_COOKIE;
	}

	$qs = array();

	/**
	 * Check if any cookie values are set. If there are then override the
	 * default params passed to the template loop.
	 */

	// Activity stream filtering on action.
	if ( ! empty( $_BP_COOKIE['bp-' . $object . '-filter'] ) && '-1' != $_BP_COOKIE['bp-' . $object . '-filter'] ) {
		$qs[] = 'type=' . urlencode( $_BP_COOKIE['bp-' . $object . '-filter'] );

		if ( bp_is_active( 'activity' ) ) {
			$actions = bp_activity_get_actions_for_context();

			// Handle multiple actions (eg. 'friendship_accepted,friendship_created')
			$action_filter = explode( ',', $_BP_COOKIE['bp-' . $object . '-filter'] );

			// See if action filter matches registered actions. If so, add it to qs.
			if ( ! array_diff( $action_filter, wp_list_pluck( $actions, 'key' ) ) ) {
				$qs[] = 'action=' . join( ',', $action_filter );
			}
		}
	}

	if ( ! empty( $_BP_COOKIE['bp-' . $object . '-scope'] ) ) {
		if ( 'personal' == $_BP_COOKIE['bp-' . $object . '-scope'] ) {
			$user_id = ( bp_displayed_user_id() ) ? bp_displayed_user_id() : bp_loggedin_user_id();
			$qs[] = 'user_id=' . $user_id;
		}

		// Activity stream scope only on activity directory.
		if ( 'all' != $_BP_COOKIE['bp-' . $object . '-scope'] && ! bp_displayed_user_id() && ! bp_is_single_item() ) {
			$qs[] = 'scope=' . urlencode( $_BP_COOKIE['bp-' . $object . '-scope'] );
		}
	}

	// If page and search_terms have been passed via the AJAX post request, use those.
	if ( ! empty( $_POST['page'] ) && '-1' != $_POST['page'] ) {
		$qs[] = 'page=' . absint( $_POST['page'] );
	}

	// Excludes activity just posted and avoids duplicate ids.
	if ( ! empty( $_POST['exclude_just_posted'] ) ) {
		$just_posted = wp_parse_id_list( $_POST['exclude_just_posted'] );
		$qs[] = 'exclude=' . implode( ',', $just_posted );
	}

	// To get newest activities.
	if ( ! empty( $_POST['offset'] ) ) {
		$qs[] = 'offset=' . intval( $_POST['offset'] );
	}

	if ( ! empty( $_POST['offset_lower'] ) ) {
		$qs[] = 'offset_lower=' . intval( $_POST['offset_lower'] );
	}

	$object_search_text = bp_get_search_default_text( $object );
	if ( ! empty( $_POST['search_terms'] ) && is_string( $_POST['search_terms'] ) && $object_search_text != $_POST['search_terms'] && 'false' != $_POST['search_terms'] && 'undefined' != $_POST['search_terms'] ) {
		$qs[] = 'search_terms=' . urlencode( $_POST['search_terms'] );
	}

	// Now pass the querystring to override default values.
	$query_string = empty( $qs ) ? '' : join( '&', (array) $qs );

	$object_filter = '';
	if ( isset( $_BP_COOKIE['bp-' . $object . '-filter'] ) ) {
		$object_filter = $_BP_COOKIE['bp-' . $object . '-filter'];
	}

	$object_scope = '';
	if ( isset( $_BP_COOKIE['bp-' . $object . '-scope'] ) ) {
		$object_scope = $_BP_COOKIE['bp-' . $object . '-scope'];
	}

	$object_page = '';
	if ( isset( $_BP_COOKIE['bp-' . $object . '-page'] ) ) {
		$object_page = $_BP_COOKIE['bp-' . $object . '-page'];
	}

	$object_search_terms = '';
	if ( isset( $_BP_COOKIE['bp-' . $object . '-search-terms'] ) ) {
		$object_search_terms = $_BP_COOKIE['bp-' . $object . '-search-terms'];
	}

	$object_extras = '';
	if ( isset( $_BP_COOKIE['bp-' . $object . '-extras'] ) ) {
		$object_extras = $_BP_COOKIE['bp-' . $object . '-extras'];
	}

	/**
	 * Filters the AJAX query string for the component loops.
	 *
	 * @since 1.7.0
	 *
	 * @param string $query_string        The query string we are working with.
	 * @param string $object              The type of page we are on.
	 * @param string $object_filter       The current object filter.
	 * @param string $object_scope        The current object scope.
	 * @param string $object_page         The current object page.
	 * @param string $object_search_terms The current object search terms.
	 * @param string $object_extras       The current object extras.
	 */
	return apply_filters( 'bp_legacy_theme_ajax_querystring', $query_string, $object, $object_filter, $object_scope, $object_page, $object_search_terms, $object_extras );
}

/**
 * Load the template loop for the current object.
 *
 * @since 1.2.0
 *
 * @return string|null Prints template loop for the specified object
 */
function bp_legacy_theme_object_template_loader() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	// Bail if no object passed.
	if ( empty( $_POST['object'] ) ) {
		return;
	}

	// Sanitize the object.
	$object = sanitize_title( $_POST['object'] );

	// Bail if object is not an active component to prevent arbitrary file inclusion.
	if ( ! bp_is_active( $object ) ) {
		return;
	}

	/**
	 * AJAX requests happen too early to be seen by bp_update_is_directory()
	 * so we do it manually here to ensure templates load with the correct
	 * context. Without this check, templates will load the 'single' version
	 * of themselves rather than the directory version.
	 */
	if ( ! bp_current_action() )
		bp_update_is_directory( true, bp_current_component() );

	// The template part can be overridden by the calling JS function.
	if ( ! empty( $_POST['template'] ) && 'groups/single/members' === $_POST['template'] ) {
		$template_part = 'groups/single/members.php';
	} else {
		$template_part = $object . '/' . $object . '-loop.php';
	}

	$template_path = bp_locate_template( array( $template_part ), false );

	$template_path = apply_filters( 'bp_legacy_object_template_path', $template_path );

	load_template( $template_path );
	exit();
}

/**
 * Load messages template loop when searched on the private message page
 *
 * @since 1.6.0
 *
 * @return string|null Prints template loop for the Messages component.
 */
function bp_legacy_theme_messages_template_loader() {
	bp_get_template_part( 'members/single/messages/messages-loop' );
	exit();
}

/**
 * Load group invitations loop to handle pagination requests sent via AJAX.
 *
 * @since 2.0.0
 */
function bp_legacy_theme_invite_template_loader() {
	bp_get_template_part( 'groups/single/invites-loop' );
	exit();
}

/**
 * Load group membership requests loop to handle pagination requests sent via AJAX.
 *
 * @since 2.0.0
 */
function bp_legacy_theme_requests_template_loader() {
	bp_get_template_part( 'groups/single/requests-loop' );
	exit();
}

/**
 * Load the activity loop template when activity is requested via AJAX.
 *
 * @since 1.2.0
 *
 * @return string|null JSON object containing 'contents' (output of the template loop
 *                     for the Activity component) and 'feed_url' (URL to the relevant RSS feed).
 */
function bp_legacy_theme_activity_template_loader() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	$scope = '';
	if ( ! empty( $_POST['scope'] ) ) {
		$scope = $_POST['scope'];
	}

	// We need to calculate and return the feed URL for each scope.
	switch ( $scope ) {
		case 'friends':
			$feed_url = bp_loggedin_user_url( bp_members_get_path_chunks( array( bp_get_activity_slug(), 'friends', array( 'feed' ) ) ) );
			break;
		case 'groups':
			$feed_url = bp_loggedin_user_url( bp_members_get_path_chunks( array( bp_get_activity_slug(), 'groups', array( 'feed' ) ) ) );
			break;
		case 'favorites':
			$feed_url = bp_loggedin_user_url( bp_members_get_path_chunks( array( bp_get_activity_slug(), 'favorites', array( 'feed' ) ) ) );
			break;
		case 'mentions':
			$feed_url = bp_loggedin_user_url( bp_members_get_path_chunks( array( bp_get_activity_slug(), 'mentions', array( 'feed' ) ) ) );

			if ( isset( $_POST['_wpnonce_activity_filter'] ) && wp_verify_nonce( wp_unslash( $_POST['_wpnonce_activity_filter'] ), 'activity_filter' ) ) {
				bp_activity_clear_new_mentions( bp_loggedin_user_id() );
			}

			break;
		default:
			$feed_url = bp_rewrites_get_url(
				array(
					'component_id'       => 'activity',
					'single_item_action' => 'feed',
				)
			);
			break;
	}

	// Buffer the loop in the template to a var for JS to spit out.
	ob_start();
	bp_get_template_part( 'activity/activity-loop' );
	$result['contents'] = ob_get_contents();

	/**
	 * Filters the feed URL for when activity is requested via AJAX.
	 *
	 * @since 1.7.0
	 *
	 * @param string $feed_url URL for the feed to be used.
	 * @param string $scope    Scope for the activity request.
	 */
	$result['feed_url'] = apply_filters( 'bp_legacy_theme_activity_feed_url', $feed_url, $scope );
	ob_end_clean();

	exit( json_encode( $result ) );
}

/**
 * Processes Activity updates received via a POST request.
 *
 * @since 1.2.0
 *
 * @return string|null HTML
 */
function bp_legacy_theme_post_update() {
	$bp = buddypress();

	if ( ! bp_is_post_request() ) {
		return;
	}

	// Check the nonce.
	check_admin_referer( 'post_update', '_wpnonce_post_update' );

	if ( ! is_user_logged_in() ) {
		exit( '-1' );
	}

	if ( empty( $_POST['content'] ) ) {
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . esc_html__( 'Please enter some content to post.', 'buddypress' ) . '</p></div>' );
	}

	$activity_id = 0;
	$item_id     = 0;
	$object      = '';


	// Try to get the item id from posted variables.
	if ( ! empty( $_POST['item_id'] ) ) {
		$item_id = (int) $_POST['item_id'];
	}

	// Try to get the object from posted variables.
	if ( ! empty( $_POST['object'] ) ) {
		$object  = sanitize_key( $_POST['object'] );

	// If the object is not set and we're in a group, set the item id and the object
	} elseif ( bp_is_group() ) {
		$item_id = bp_get_current_group_id();
		$object = 'groups';
	}

	if ( ! $object && bp_is_active( 'activity' ) ) {
		$activity_id = bp_activity_post_update( array( 'content' => $_POST['content'], 'error_type' => 'wp_error' ) );

	} elseif ( 'groups' === $object ) {
		if ( $item_id && bp_is_active( 'groups' ) )
			$activity_id = groups_post_update( array( 'content' => $_POST['content'], 'group_id' => $item_id, 'error_type' => 'wp_error' ) );

	} else {

		/** This filter is documented in bp-activity/actions/post.php */
		$activity_id = apply_filters( 'bp_activity_custom_update', false, $object, $item_id, $_POST['content'] );
	}

	if ( false === $activity_id ) {
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . esc_html__( 'There was a problem posting your update. Please try again.', 'buddypress' ) . '</p></div>' );
	} elseif ( is_wp_error( $activity_id ) && $activity_id->get_error_code() ) {
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . esc_html( $activity_id->get_error_message() ) . '</p></div>' );
	}

	$last_recorded = ! empty( $_POST['since'] ) ? date( 'Y-m-d H:i:s', intval( $_POST['since'] ) ) : 0;
	if ( $last_recorded ) {
		$activity_args = array( 'since' => $last_recorded );
		$bp->activity->last_recorded = $last_recorded;
		add_filter( 'bp_get_activity_css_class', 'bp_activity_newest_class', 10, 1 );
	} else {
		$activity_args = array( 'include' => $activity_id );
	}

	if ( bp_has_activities ( $activity_args ) ) {
		while ( bp_activities() ) {
			bp_the_activity();
			bp_get_template_part( 'activity/entry' );
		}
	}

	if ( ! empty( $last_recorded ) ) {
		remove_filter( 'bp_get_activity_css_class', 'bp_activity_newest_class', 10 );
	}

	exit;
}

/**
 * Posts new Activity comments received via a POST request.
 *
 * @since 1.2.0
 *
 * @global BP_Activity_Template $activities_template The main activity template loop class.
 *
 * @return string|null HTML
 */
function bp_legacy_theme_new_activity_comment() {
	global $activities_template;

	if ( ! bp_is_post_request() ) {
		return;
	}

	// Check the nonce.
	check_admin_referer( 'new_activity_comment', '_wpnonce_new_activity_comment' );

	if ( ! is_user_logged_in() ) {
		exit( '-1' );
	}

	$feedback = __( 'There was an error posting your reply. Please try again.', 'buddypress' );

	if ( empty( $_POST['content'] ) ) {
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . esc_html__( 'Please do not leave the comment area blank.', 'buddypress' ) . '</p></div>' );
	}

	if ( empty( $_POST['form_id'] ) || empty( $_POST['comment_id'] ) || ! is_numeric( $_POST['form_id'] ) || ! is_numeric( $_POST['comment_id'] ) ) {
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . esc_html( $feedback ) . '</p></div>' );
	}

	$activity_id   = (int) $_POST['form_id'];
	$activity_item = new BP_Activity_Activity( $activity_id );
	if ( ! bp_activity_user_can_read( $activity_item ) ) {
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . esc_html( $feedback ) . '</p></div>' );
	}

	$comment_id = bp_activity_new_comment( array(
		'activity_id' => $activity_id,
		'content'     => $_POST['content'],
		'parent_id'   => $_POST['comment_id'],
		'error_type'  => 'wp_error'
	) );

	if ( is_wp_error( $comment_id ) ) {
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . esc_html( $comment_id->get_error_message() ) . '</p></div>' );
	}

	// Load the new activity item into the $activities_template global.
	bp_has_activities( 'display_comments=stream&hide_spam=false&show_hidden=true&include=' . $comment_id );

	// Swap the current comment with the activity item we just loaded.
	if ( isset( $activities_template->activities[0] ) ) {
		$activities_template->activity = new stdClass();
		$activities_template->activity->id              = $activities_template->activities[0]->item_id;
		$activities_template->activity->current_comment = $activities_template->activities[0];

		// Because the whole tree has not been loaded, we manually
		// determine depth.
		$depth = 1;
		$parent_id = (int) $activities_template->activities[0]->secondary_item_id;
		while ( $parent_id !== (int) $activities_template->activities[0]->item_id ) {
			$depth++;
			$p_obj = new BP_Activity_Activity( $parent_id );
			$parent_id = (int) $p_obj->secondary_item_id;
		}
		$activities_template->activity->current_comment->depth = $depth;
	}

	// Get activity comment template part.
	bp_get_template_part( 'activity/comment' );

	unset( $activities_template );
	exit;
}

/**
 * Deletes an Activity item received via a POST request.
 *
 * @since 1.2.0
 */
function bp_legacy_theme_delete_activity() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	// Check the nonce.
	check_admin_referer( 'bp_activity_delete_link' );

	if ( ! is_user_logged_in() ) {
		exit( '-1' );
	}

	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {
		exit( '-1' );
	}

	$activity = new BP_Activity_Activity( (int) $_POST['id'] );

	// Check access.
	if ( ! bp_activity_user_can_delete( $activity ) ) {
		exit( '-1' );
	}

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_before_action_delete_activity', $activity->id, $activity->user_id );

	if ( ! bp_activity_delete( array( 'id' => $activity->id, 'user_id' => $activity->user_id ) ) ) {
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . esc_html__( 'There was a problem when deleting. Please try again.', 'buddypress' ) . '</p></div>' );
	}

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_action_delete_activity', $activity->id, $activity->user_id );
	exit;
}

/**
 * Deletes an Activity comment received via a POST request.
 *
 * @since 1.2.0
 */
function bp_legacy_theme_delete_activity_comment() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	// Check the nonce.
	check_admin_referer( 'bp_activity_delete_link' );

	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {
		exit( '-1' );
	}

	if ( ! is_user_logged_in() ) {
		exit( '-1' );
	}

	$comment = new BP_Activity_Activity( $_POST['id'] );

	// Check access.
	if ( ! bp_current_user_can( 'bp_moderate' ) && $comment->user_id != bp_loggedin_user_id() ) {
		exit( '-1' );
	}

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_before_action_delete_activity', $_POST['id'], $comment->user_id );

	if ( ! bp_activity_delete_comment( $comment->item_id, $comment->id ) ) {
		exit( '-1<div id="message" class="error bp-ajax-message"><p>' . esc_html__( 'There was a problem when deleting. Please try again.', 'buddypress' ) . '</p></div>' );
	}

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_action_delete_activity', $_POST['id'], $comment->user_id );
	exit;
}

/**
 * AJAX spam an activity item or comment.
 *
 * @since 1.6.0
 */
function bp_legacy_theme_spam_activity() {
	$bp = buddypress();

	if ( ! bp_is_post_request() ) {
		return;
	}

	// Check that user is logged in, Activity Streams are enabled, and Akismet is present.
	if ( ! is_user_logged_in() || ! bp_is_active( 'activity' ) || empty( $bp->activity->akismet ) ) {
		exit( '-1' );
	}

	// Check an item ID was passed.
	if ( empty( $_POST['id'] ) || ! is_numeric( $_POST['id'] ) ) {
		exit( '-1' );
	}

	// Is the current user allowed to spam items?
	if ( ! bp_activity_user_can_mark_spam() ) {
		exit( '-1' );
	}

	// Load up the activity item.
	$activity = new BP_Activity_Activity( (int) $_POST['id'] );
	if ( empty( $activity->component ) ) {
		exit( '-1' );
	}

	// Check nonce.
	check_admin_referer( 'bp_activity_akismet_spam_' . $activity->id );

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_before_action_spam_activity', $activity->id, $activity );

	// Mark as spam.
	bp_activity_mark_as_spam( $activity );
	$activity->save();

	/** This action is documented in bp-activity/bp-activity-actions.php */
	do_action( 'bp_activity_action_spam_activity', $activity->id, $activity->user_id );
	exit;
}

/**
 * Mark an activity as a favourite via a POST request.
 *
 * @since 1.2.0
 *
 * @return string|null HTML
 */
function bp_legacy_theme_mark_activity_favorite() {
	// Bail if not a POST action.
	if ( ! bp_is_post_request() ) {
		return;
	}

	if ( ! isset( $_POST['nonce'] ) ) {
		return;
	}

	// Either the 'mark' or 'unmark' nonce is accepted, for backward compatibility.
	$nonce = wp_unslash( $_POST['nonce'] );
	if ( ! wp_verify_nonce( $nonce, 'mark_favorite' ) && ! wp_verify_nonce( $nonce, 'unmark_favorite' ) ) {
		return;
	}

	$activity_id   = (int) $_POST['id'];
	$activity_item = new BP_Activity_Activity( $activity_id );
	if ( empty( $activity_item->id ) || ! bp_activity_user_can_read( $activity_item, bp_loggedin_user_id() ) ) {
		return;
	}

	if ( bp_activity_add_user_favorite( $activity_id ) ) {
		esc_html_e( 'Remove Favorite', 'buddypress' );
	} else {
		esc_html_e( 'Favorite', 'buddypress' );
	}

	exit;
}

/**
 * Un-favourite an activity via a POST request.
 *
 * @since 1.2.0
 *
 * @return string|null HTML
 */
function bp_legacy_theme_unmark_activity_favorite() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	if ( ! isset( $_POST['nonce'] ) ) {
		return;
	}

	// Either the 'mark' or 'unmark' nonce is accepted, for backward compatibility.
	$nonce = wp_unslash( $_POST['nonce'] );
	if ( ! wp_verify_nonce( $nonce, 'mark_favorite' ) && ! wp_verify_nonce( $nonce, 'unmark_favorite' ) ) {
		return;
	}

	$activity_id = (int) $_POST['id'];

	if ( bp_activity_remove_user_favorite( $activity_id ) ) {
		esc_html_e( 'Favorite', 'buddypress' );
	} else {
		esc_html_e( 'Remove Favorite', 'buddypress' );
	}

	exit;
}

/**
 * Fetches an activity's full, non-excerpted content via a POST request.
 * Used for the 'Read More' link on long activity items.
 *
 * @since 1.5.0
 *
 * @return string|null HTML
 */
function bp_legacy_theme_get_single_activity_content() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	$activity_array = bp_activity_get_specific( array(
		'activity_ids'     => $_POST['activity_id'],
		'display_comments' => 'stream'
	) );

	$activity = ! empty( $activity_array['activities'][0] ) ? $activity_array['activities'][0] : false;

	if ( empty( $activity ) ) {
		exit; // @todo: error?
	}

	/**
	 * Fires before the return of an activity's full, non-excerpted content via a POST request.
	 *
	 * @since 1.7.0
	 *
	 * @param string $activity Activity content. Passed by reference.
	 */
	do_action_ref_array( 'bp_legacy_theme_get_single_activity_content', array( &$activity ) );

	// Activity content retrieved through AJAX should run through normal filters, but not be truncated.
	remove_filter( 'bp_get_activity_content_body', 'bp_activity_truncate_entry', 5 );

	/** This filter is documented in bp-activity/bp-activity-template.php */
	$content = apply_filters_ref_array( 'bp_get_activity_content_body', array( $activity->content, &$activity ) );

	// phpcs:ignore WordPress.Security.EscapeOutput
	exit( $content );
}

/**
 * Invites a friend to join a group via a POST request.
 *
 * @since 1.2.0
 *
 * @todo Audit return types
 */
function bp_legacy_theme_ajax_invite_user() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	check_ajax_referer( 'groups_invite_uninvite_user' );

	if ( ! $_POST['friend_id'] || ! $_POST['friend_action'] || ! $_POST['group_id'] ) {
		return;
	}

	if ( ! bp_groups_user_can_send_invites( $_POST['group_id'] ) ) {
		return;
	}

	$group_id = (int) $_POST['group_id'];
	$friend_id = (int) $_POST['friend_id'];

	if ( 'invite' == $_POST['friend_action'] ) {
		if ( ! friends_check_friendship( bp_loggedin_user_id(), $_POST['friend_id'] ) ) {
			return;
		}

		$group = groups_get_group( $group_id );

		// Users who have previously requested membership do not need
		// another invitation created for them.
		if ( groups_check_for_membership_request( $friend_id, $group_id ) ) {
			$user_status = 'is_pending';

		// Create the user invitation.
		} elseif ( groups_invite_user( array( 'user_id' => $friend_id, 'group_id' => $group_id ) ) ) {
			$user_status = 'is_invited';

		// Miscellaneous failure.
		} else {
			return;
		}

		$user = new BP_Core_User( $friend_id );

		if ( bp_is_current_action( 'create' ) ) {
			$uninvite_url = add_query_arg(
				'user_id',
				$user_id,
				bp_get_groups_directory_url( bp_groups_get_path_chunks( array( 'group-invites' ), 'create' ) )
			);
		} else {
			$path_chunks  = bp_groups_get_path_chunks( array( 'send-invites', 'remove', $friend_id ) );
			$uninvite_url = bp_get_group_url( $group, $path_chunks );
		}

		// phpcs:disable WordPress.Security.EscapeOutput
		echo '<li id="uid-' . esc_attr( $user->id ) . '">';
		echo $user->avatar_thumb;
		echo '<h4>' . $user->user_link . '</h4>';
		echo '<span class="activity">' . esc_attr( $user->last_active ) . '</span>';
		echo '<div class="action">
				<a class="button remove" href="' . esc_url( wp_nonce_url( $uninvite_url, 'groups_invite_uninvite_user' ) ) . '" id="uid-' . esc_attr( $user->id ) . '">' . esc_html__( 'Remove Invite', 'buddypress' ) . '</a>
			  </div>';

		if ( 'is_pending' == $user_status ) {
			/* translators: %s: user link */
			echo '<p class="description">' . sprintf( esc_html__( '%s has previously requested to join this group. Sending an invitation will automatically add the member to the group.', 'buddypress' ), $user->user_link ) . '</p>';
		}

		echo '</li>';
		// phpcs:enable
		exit;

	} elseif ( 'uninvite' == $_POST['friend_action'] ) {
		// Users who have previously requested membership should not
		// have their requests deleted on the "uninvite" action.
		if ( BP_Groups_Member::check_for_membership_request( $friend_id, $group_id ) ) {
			return;
		}

		// Remove the unsent invitation.
		if ( ! groups_uninvite_user( $friend_id, $group_id ) ) {
			return;
		}

		exit;

	} else {
		return;
	}
}

/**
 * Friend/un-friend a user via a POST request.
 *
 * @since 1.2.0
 *
 * @return string|null HTML
 */
function bp_legacy_theme_ajax_addremove_friend() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	// Cast fid as an integer.
	$friend_id = (int) $_POST['fid'];

	$user = get_user_by( 'id', $friend_id );
	if ( ! $user ) {
		die( esc_html__( 'No member found by that ID.', 'buddypress' ) );
	}

	// Trying to cancel friendship.
	if ( 'is_friend' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
		check_ajax_referer( 'friends_remove_friend' );

		if ( ! friends_remove_friend( bp_loggedin_user_id(), $friend_id ) ) {
			esc_html_e( 'Friendship could not be canceled.', 'buddypress' );
		} else {
			$url = bp_loggedin_user_url( bp_members_get_path_chunks( array( bp_get_friends_slug(), 'add-friend', array( $friend_id ) ) ) );
			echo '<a id="friend-' . esc_attr( $friend_id ) . '" class="friendship-button not_friends add" rel="add" href="' . esc_url( wp_nonce_url( $url, 'friends_add_friend' ) ) . '">' . esc_html__( 'Add Friend', 'buddypress' ) . '</a>';
		}

	// Trying to request friendship.
	} elseif ( 'not_friends' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
		check_ajax_referer( 'friends_add_friend' );

		if ( ! friends_add_friend( bp_loggedin_user_id(), $friend_id ) ) {
			esc_html_e(' Friendship could not be requested.', 'buddypress' );
		} else {
			$url = bp_loggedin_user_url( bp_members_get_path_chunks( array( bp_get_friends_slug(), 'requests', array( 'cancel', $friend_id ) ) ) );
			echo '<a id="friend-' . esc_attr( $friend_id ) . '" class="remove friendship-button pending_friend requested" rel="remove" href="' . esc_url( wp_nonce_url( $url, 'friends_withdraw_friendship' ) ) . '" class="requested">' . esc_html__( 'Cancel Friendship Request', 'buddypress' ) . '</a>';
		}

	// Trying to cancel pending request.
	} elseif ( 'pending' == BP_Friends_Friendship::check_is_friend( bp_loggedin_user_id(), $friend_id ) ) {
		check_ajax_referer( 'friends_withdraw_friendship' );

		if ( friends_withdraw_friendship( bp_loggedin_user_id(), $friend_id ) ) {
			$url = bp_loggedin_user_url( bp_members_get_path_chunks( array( bp_get_friends_slug(), 'add-friend', array( $friend_id ) ) ) );
			echo '<a id="friend-' . esc_attr( $friend_id ) . '" class="friendship-button not_friends add" rel="add" href="' . esc_url( wp_nonce_url( $url, 'friends_add_friend' ) ) . '">' . esc_html__( 'Add Friend', 'buddypress' ) . '</a>';
		} else {
			esc_html_e("Friendship request could not be cancelled.", 'buddypress');
		}

	// Request already pending.
	} else {
		esc_html_e( 'Request Pending', 'buddypress' );
	}

	exit;
}

/**
 * Accept a user friendship request via a POST request.
 *
 * @since 1.2.0
 */
function bp_legacy_theme_ajax_accept_friendship() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	check_admin_referer( 'friends_accept_friendship' );

	if ( ! friends_accept_friendship( (int) $_POST['id'] ) ) {
		echo "-1<div id='message' class='error'><p>" . esc_html__( 'There was a problem accepting that request. Please try again.', 'buddypress' ) . '</p></div>';
	}

	exit;
}

/**
 * Reject a user friendship request via a POST request.
 *
 * @since 1.2.0
 */
function bp_legacy_theme_ajax_reject_friendship() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	check_admin_referer( 'friends_reject_friendship' );

	if ( ! friends_reject_friendship( (int) $_POST['id'] ) ) {
		echo "-1<div id='message' class='error'><p>" . esc_html__( 'There was a problem rejecting that request. Please try again.', 'buddypress' ) . '</p></div>';
	}

	exit;
}

/**
 * Join or leave a group when clicking the "join/leave" button via a POST request.
 *
 * @since 1.2.0
 *
 * @return string|null HTML
 */
function bp_legacy_theme_ajax_joinleave_group() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	// Cast gid as integer.
	$group_id = (int) $_POST['gid'];

	if ( groups_is_user_banned( bp_loggedin_user_id(), $group_id ) ) {
		return;
	}

	$group = groups_get_group( $group_id );

	if ( ! $group ) {
		return;
	}

	$action = '';
	if ( isset( $_POST['action'] ) ) {
		$action = sanitize_key( wp_unslash( $_POST['action'] ) );
	}

	// Client doesn't distinguish between different request types, so we infer from user status.
	if ( groups_is_user_member( bp_loggedin_user_id(), $group->id ) ) {
		$request_type = 'leave_group';
	} elseif ( groups_check_user_has_invite( bp_loggedin_user_id(), $group->id ) ) {
		$request_type = 'accept_invite';
	} elseif ( 'private' === $group->status ) {
		$request_type = 'request_membership';
	} else {
		$request_type = 'join_group';
	}

	switch ( $request_type ) {
		case 'join_group' :
			if ( ! bp_current_user_can( 'groups_join_group', array( 'group_id' => $group->id ) ) ) {
				esc_html_e( 'Error joining group', 'buddypress' );
			}

			/*
			 * Ensure that the invite_status key exists, to avoid a group
			 * being joinable when its creation process was interrupted.
			 */
			if ( ! groups_get_groupmeta( $group->id, 'invite_status' ) ) {
				esc_html_e( 'Error joining group', 'buddypress' );
				break;
			}

			check_ajax_referer( 'groups_join_group' );

			if ( ! groups_join_group( $group->id ) ) {
				esc_html_e( 'Error joining group', 'buddypress' );
			} else {
				$leave_url = wp_nonce_url(
					bp_get_group_url(
						$group,
						bp_groups_get_path_chunks( array( 'leave-group' ) )
					),
					'groups_leave_group'
				);
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="group-button leave-group" rel="leave" href="' . esc_url( $leave_url ) . '">' . esc_html__( 'Leave Group', 'buddypress' ) . '</a>';
			}
		break;

		case 'accept_invite' :
			check_ajax_referer( 'groups_accept_invite' );

			if ( ! groups_accept_invite( bp_loggedin_user_id(), $group->id ) ) {
				esc_html_e( 'Error requesting membership', 'buddypress' );
			} else {
				$leave_url = wp_nonce_url(
					bp_get_group_url(
						$group,
						bp_groups_get_path_chunks( array( 'leave-group' ) )
					),
					'groups_leave_group'
				);
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="group-button leave-group" rel="leave" href="' . esc_url( $leave_url ) . '">' . esc_html__( 'Leave Group', 'buddypress' ) . '</a>';
			}
		break;

		case 'request_membership' :
			check_ajax_referer( 'groups_request_membership' );

			if ( ! bp_current_user_can( 'groups_request_membership', array( 'group_id' => $group->id ) ) || ! groups_send_membership_request( [ 'user_id' => bp_loggedin_user_id(), 'group_id' => $group->id ] ) ) {
				esc_html_e( 'Error requesting membership', 'buddypress' );
			} else {
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="group-button disabled pending membership-requested" rel="membership-requested" href="' . esc_url( bp_get_group_url( $group ) ) . '">' . esc_html__( 'Request Sent', 'buddypress' ) . '</a>';
			}
		break;

		case 'leave_group' :
			check_ajax_referer( 'groups_leave_group' );

			if ( ! groups_leave_group( $group->id ) ) {
				esc_html_e( 'Error leaving group', 'buddypress' );
			} elseif ( 'public' === $group->status ) {
				$join_url = wp_nonce_url(
					bp_get_group_url(
						$group,
						bp_groups_get_path_chunks( array( 'join' ) )
					),
					'groups_join_group'
				);
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="group-button join-group" rel="join" href="' . esc_url( $join_url ) . '">' . esc_html__( 'Join Group', 'buddypress' ) . '</a>';
			} else {
				$request_url = wp_nonce_url(
					bp_get_group_url(
						$group,
						bp_groups_get_path_chunks( array( 'request-membership' ) )
					),
					'groups_request_membership'
				);
				echo '<a id="group-' . esc_attr( $group->id ) . '" class="group-button request-membership" rel="join" href="' . esc_url( $request_url ) . '">' . esc_html__( 'Request Membership', 'buddypress' ) . '</a>';
			}
		break;
	}

	exit;
}

/**
 * Close and keep closed site wide notices from an admin in the sidebar, via a POST request.
 *
 * @since 1.2.0
 */
function bp_legacy_theme_ajax_close_notice() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	$nonce_check = isset( $_POST['nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['nonce'] ), 'bp_messages_close_notice' );

	if ( ! $nonce_check || ! isset( $_POST['notice_id'] ) ) {
		echo "-1<div id='message' class='error'><p>" . esc_html__( 'There was a problem closing the notice.', 'buddypress' ) . '</p></div>';

	} else {
		bp_messages_dismiss_sitewide_notice( bp_loggedin_user_id(), (int) $_POST['notice_id'] );
	}

	exit;
}

/**
 * Send a private message reply to a thread via a POST request.
 *
 * @since 1.2.0
 *
 * @return string|null HTML
 */
function bp_legacy_theme_ajax_messages_send_reply() {
	if ( ! bp_is_post_request() ) {
		return;
	}

	check_ajax_referer( 'messages_send_message' );

	$thread_id = (int) $_POST['thread_id'];

	// Cannot respond to a thread you're not already a recipient on.
	if ( ! bp_current_user_can( 'bp_moderate' ) && ( ! messages_is_valid_thread( $thread_id ) || ! messages_check_thread_access( $thread_id ) ) ) {
		echo "-1<div id='message' class='error'><p>" . esc_html__( 'There was a problem sending that reply. Please try again.', 'buddypress' ) . '</p></div>';
		die;
	}

	$result = messages_new_message( array( 'thread_id' => $thread_id, 'content' => $_REQUEST['content'] ) );

	if ( ! empty( $result ) ) {

		// Pretend we're in the message loop.
		global $thread_template;

		bp_thread_has_messages( array( 'thread_id' => $thread_id ) );

		// Set the current message to the 2nd last.
		$thread_template->message = end( $thread_template->thread->messages );
		$thread_template->message = prev( $thread_template->thread->messages );

		// Set current message to current key.
		$thread_template->current_message = key( $thread_template->thread->messages );

		// Now manually iterate message like we're in the loop.
		bp_thread_the_message();

		// Manually call oEmbed
		// this is needed because we're not at the beginning of the loop.
		bp_messages_embed();

		// Add new-message css class.
		add_filter( 'bp_get_the_thread_message_css_class', function ( $retval ) {
			$retval[] = 'new-message';
			return $retval;
		} );

		// Output single message template part.
		bp_get_template_part( 'members/single/messages/message' );

		// Clean up the loop.
		bp_thread_messages();

	} else {
		echo "-1<div id='message' class='error'><p>" . esc_html__( 'There was a problem sending that reply. Please try again.', 'buddypress' ) . '</p></div>';
	}

	exit;
}

/**
 * Mark a private message as unread in your inbox via a POST request.
 *
 * No longer used.
 *
 * @since 1.2.0
 * @deprecated 2.2.0
 */
function bp_legacy_theme_ajax_message_markunread() {
	die( '-1' );
}

/**
 * Mark a private message as read in your inbox via a POST request.
 *
 * No longer used.
 *
 * @since 1.2.0
 * @deprecated 2.2.0
 */
function bp_legacy_theme_ajax_message_markread() {
	die( '-1' );
}

/**
 * Delete a private message(s) in your inbox via a POST request.
 *
 * No longer used.
 *
 * @since 1.2.0
 * @deprecated 2.2.0
 */
function bp_legacy_theme_ajax_messages_delete() {
	die( '-1' );
}

/**
 * AJAX handler for autocomplete.
 *
 * Displays friends only, unless BP_MESSAGES_AUTOCOMPLETE_ALL is defined.
 *
 * @since 1.2.0
 */
function bp_legacy_theme_ajax_messages_autocomplete_results() {

	/**
	 * Filters the max results default value for ajax messages autocomplete results.
	 *
	 * @since 1.5.0
	 *
	 * @param int $value Max results for autocomplete. Default 10.
	 */
	$limit = isset( $_GET['limit'] ) ? absint( $_GET['limit'] )          : (int) apply_filters( 'bp_autocomplete_max_results', 10 );
	$term  = isset( $_GET['q'] )     ? sanitize_text_field( $_GET['q'] ) : '';

	// Include everyone in the autocomplete, or just friends?
	if ( bp_is_current_component( bp_get_messages_slug() ) ) {
		$only_friends = ( buddypress()->messages->autocomplete_all === false );
	} else {
		$only_friends = true;
	}

	$suggestions = bp_core_get_suggestions( array(
		'limit'        => $limit,
		'only_friends' => $only_friends,
		'term'         => $term,
		'type'         => 'members',
	) );

	if ( $suggestions && ! is_wp_error( $suggestions ) ) {
		foreach ( $suggestions as $user ) {

			// Note that the final line break acts as a delimiter for the
			// autocomplete JavaScript and thus should not be removed.
			printf( '<span id="%s" href="#"></span><img src="%s" style="width: 15px"> &nbsp; %s (%s)' . "\n",
				esc_attr( 'link-' . $user->ID ),
				esc_url( $user->image ),
				esc_html( $user->name ),
				esc_html( $user->ID )
			);
		}
	}

	exit;
}

/**
 * AJAX callback to set a message's star status.
 *
 * @since 2.3.0
 */
function bp_legacy_theme_ajax_messages_star_handler() {
	if ( false === bp_is_active( 'messages', 'star' ) || empty( $_POST['message_id'] ) ) {
		return;
	}

	// Check nonce.
	check_ajax_referer( 'bp-messages-star-' . (int) $_POST['message_id'], 'nonce' );

	// Check capability.
	if ( ! is_user_logged_in() || ! bp_core_can_edit_settings() ) {
		return;
	}

	if ( true === bp_messages_star_set_action( array(
		'action'     => $_POST['star_status'],
		'message_id' => (int) $_POST['message_id'],
		'bulk'       => ! empty( $_POST['bulk'] ) ? true : false
	 ) ) ) {
		echo '1';
		die();
	}

	echo '-1';
	die();
}

/**
 * BP Legacy's callback for the cover image feature.
 *
 * @since  2.4.0
 *
 * @param  array $params the current component's feature parameters.
 * @return null|string An array to inform about the css handle to attach the css rules to
 */
function bp_legacy_theme_cover_image( $params = array() ) {
	if ( empty( $params ) ) {
		return;
	}

	// Avatar height - padding - 1/2 avatar height.
	$avatar_offset = $params['height'] - 5 - round( (int) bp_core_avatar_full_height() / 2 );

	// Header content offset + spacing.
	$top_offset  = bp_core_avatar_full_height() - 10;
	$left_offset = bp_core_avatar_full_width() + 20;

	$cover_image = ( !empty( $params['cover_image'] ) ) ? 'background-image: url(' . $params['cover_image'] . ');' : '';

	$hide_avatar_style = '';

	// Adjust the cover image header, in case avatars are completely disabled.
	if ( ! buddypress()->avatar->show_avatars ) {
		$hide_avatar_style = '
			#buddypress #item-header-cover-image #item-header-avatar {
				display:  none;
			}
		';

		if ( bp_is_user() ) {
			$hide_avatar_style = '
				#buddypress #item-header-cover-image #item-header-avatar a {
					display: block;
					height: ' . $top_offset . 'px;
					margin: 0 15px 19px 0;
				}

				#buddypress div#item-header #item-header-cover-image #item-header-content {
					margin-left: auto;
				}
			';
		}
	}

	return '
		/* Cover image */
		#buddypress #header-cover-image {
			height: ' . $params["height"] . 'px;
			' . $cover_image . '
		}

		#buddypress #create-group-form #header-cover-image {
			margin: 1em 0;
			position: relative;
		}

		.bp-user #buddypress #item-header {
			padding-top: 0;
		}

		#buddypress #item-header-cover-image #item-header-avatar {
			margin-top: '. $avatar_offset .'px;
			float: left;
			overflow: visible;
			width: auto;
		}

		#buddypress div#item-header #item-header-cover-image #item-header-content {
			clear: both;
			float: left;
			margin-left: ' . $left_offset . 'px;
			margin-top: -' . $top_offset . 'px;
			width: auto;
		}

		body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-header-content,
		body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-actions {
			clear: none;
			margin-top: ' . $params["height"] . 'px;
			margin-left: 0;
			max-width: 50%;
		}

		body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-actions {
			max-width: 20%;
			padding-top: 20px;
		}

		' . $hide_avatar_style . '

		#buddypress div#item-header-cover-image .user-nicename a,
		#buddypress div#item-header-cover-image .user-nicename {
			font-size: 200%;
			color: #fff;
			margin: 0 0 0.6em;
			text-rendering: optimizelegibility;
			text-shadow: 0 0 3px rgba( 0, 0, 0, 0.8 );
		}

		#buddypress #item-header-cover-image #item-header-avatar img.avatar {
			background: rgba( 255, 255, 255, 0.8 );
			border: solid 2px #fff;
		}

		#buddypress #item-header-cover-image #item-header-avatar a {
			border: 0;
			text-decoration: none;
		}

		#buddypress #item-header-cover-image #item-buttons {
			margin: 0 0 10px;
			padding: 0 0 5px;
		}

		#buddypress #item-header-cover-image #item-buttons:after {
			clear: both;
			content: "";
			display: table;
		}

		@media screen and (max-width: 782px) {
			#buddypress #item-header-cover-image #item-header-avatar,
			.bp-user #buddypress #item-header #item-header-cover-image #item-header-avatar,
			#buddypress div#item-header #item-header-cover-image #item-header-content {
				width: 100%;
				text-align: center;
			}

			#buddypress #item-header-cover-image #item-header-avatar a {
				display: inline-block;
			}

			#buddypress #item-header-cover-image #item-header-avatar img {
				margin: 0;
			}

			#buddypress div#item-header #item-header-cover-image #item-header-content,
			body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-header-content,
			body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-actions {
				margin: 0;
			}

			body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-header-content,
			body.single-item.groups #buddypress div#item-header #item-header-cover-image #item-actions {
				max-width: 100%;
			}

			#buddypress div#item-header-cover-image h2 a,
			#buddypress div#item-header-cover-image h2 {
				color: inherit;
				text-shadow: none;
				margin: 25px 0 0;
				font-size: 200%;
			}

			#buddypress #item-header-cover-image #item-buttons div {
				float: none;
				display: inline-block;
			}

			#buddypress #item-header-cover-image #item-buttons:before {
				content: "";
			}

			#buddypress #item-header-cover-image #item-buttons {
				margin: 5px 0;
			}
		}
	';
}

/**
 * Add a search box to a single group's manage members screen.
 *
 * @since 2.7.0
 *
 * @return string|null HTML for the search form.
 */
function bp_legacy_theme_group_manage_members_add_search() {
	if ( bp_is_action_variable( 'manage-members' ) ) :
		?>
		<div id="members-dir-search" class="dir-search no-ajax" role="search">
			<?php bp_directory_members_search_form(); ?>
		</div>
		<?php
	endif;
}

/**
 * Modify welcome message in Legacy template pack when
 * community invitations are enabled.
 *
 * @since 8.0.0
 */
function bp_members_invitations_add_legacy_welcome_message() {
	$message = bp_members_invitations_get_registration_welcome_message();

	if ( $message ) {
		// Surround the message with `<p>` tags.
		$message = wpautop( $message );

		echo wp_kses(
			$message,
			array(
				'p' => true,
				'a' => array(
					'href' => true,
				),
			)
		);
	}
}
add_action( 'bp_before_register_page', 'bp_members_invitations_add_legacy_welcome_message' );


/**
 * Modify "registration disabled" message in Legacy template pack when
 * community invitations are enabled.
 *
 * @since 8.0.0
 */
function bp_members_invitations_add_legacy_registration_disabled_message() {
	$message = bp_members_invitations_get_modified_registration_disabled_message();

	if ( $message ) {
		// Surround the message with `<p>` tags.
		$message = wpautop( $message );

		echo wp_kses(
			$message,
			array(
				'p' => true,
				'a' => array(
					'href' => true,
				),
			)
		);
	}
}
add_action( 'bp_after_registration_disabled', 'bp_members_invitations_add_legacy_registration_disabled_message' );
