<?php
/**
 * BuddyPress Members Who's Online Widget.
 *
 * @package BuddyPress
 * @subpackage Members
 * @since 1.0.0
 * @deprecated 12.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

_deprecated_file( basename( __FILE__ ), '12.0.0', '', esc_html__( 'BuddyPress does not include Legacy Widgets anymore, you can restore it using the BP Classic plugin', 'buddypress' ) );

/**
 * Who's Online Widget.
 *
 * @since 1.0.3
 * @since 9.0.0 Adds the `show_instance_in_rest` property to Widget options.
 * @deprecated 12.0.0
 */
class BP_Core_Whos_Online_Widget extends WP_Widget {

	/**
	 * Constructor method.
	 *
	 * @since 1.5.0
	 * @since 9.0.0 Adds the `show_instance_in_rest` property to Widget options.
	 * @deprecated 12.0.0
	 */
	public function __construct() {
		_deprecated_function( __METHOD__, '12.0.0' );
	}

	/**
	 * Display the Who's Online widget.
	 *
	 * @since 1.0.3
	 * @deprecated 12.0.0
	 *
	 * @see WP_Widget::widget() for description of parameters.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget settings, as saved by the user.
	 */
	public function widget( $args, $instance ) {
		_deprecated_function( __METHOD__, '12.0.0' );
	}

	/**
	 * Update the Who's Online widget options.
	 *
	 * @since 1.0.3
	 * @deprecated 12.0.0
	 *
	 * @param array $new_instance The new instance options.
	 * @param array $old_instance The old instance options.
	 */
	public function update( $new_instance, $old_instance ) {
		_deprecated_function( __METHOD__, '12.0.0' );
	}

	/**
	 * Output the Who's Online widget options form.
	 *
	 * @since 1.0.3
	 * @deprecated 12.0.0
	 *
	 * @param array $instance Widget instance settings.
	 */
	public function form( $instance ) {
		_deprecated_function( __METHOD__, '12.0.0' );
	}

	/**
	 * Merge the widget settings into defaults array.
	 *
	 * @since 2.3.0
	 * @deprecated 12.0.0
	 *
	 * @param array $instance Widget instance settings.
	 */
	public function parse_settings( $instance = array() ) {
		_deprecated_function( __METHOD__, '12.0.0' );
	}
}
