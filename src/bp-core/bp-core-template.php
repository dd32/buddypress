<?php
/**
 * Core component template tag functions.
 *
 * @package BuddyPress
 * @subpackage Core
 * @since 1.5.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Output the "options nav", the secondary-level single item navigation menu.
 *
 * Uses the component's nav global to render out the sub navigation for the
 * current component. Each component adds to its sub navigation array within
 * its own setup_nav() function.
 *
 * This sub navigation array is the secondary level navigation, so for profile
 * it contains:
 *      [Public, Edit Profile, Change Avatar]
 *
 * The function will also analyze the current action for the current component
 * to determine whether or not to highlight a particular sub nav item.
 *
 * @since 1.0.0
 *
 *       viewed user.
 *
 * @param string $parent_slug Options nav slug.
 * @return string
 */
function bp_get_options_nav( $parent_slug = '' ) {
	$bp = buddypress();

	// If we are looking at a member profile, then the we can use the current
	// component as an index. Otherwise we need to use the component's root_slug.
	$component_index = !empty( $bp->displayed_user ) ? bp_current_component() : bp_get_root_slug( bp_current_component() );
	$selected_item   = bp_current_action();

	// Default to the Members nav.
	if ( ! bp_is_single_item() ) {
		// Set the parent slug, if not provided.
		if ( empty( $parent_slug ) ) {
			$parent_slug = $component_index;
		}

		$secondary_nav_items = $bp->members->nav->get_secondary( array( 'parent_slug' => $parent_slug ) );

		if ( ! $secondary_nav_items ) {
			return false;
		}

	// For a single item, try to use the component's nav.
	} else {
		$current_item = bp_current_item();
		$single_item_component = bp_current_component();

		// Adjust the selected nav item for the current single item if needed.
		if ( ! empty( $parent_slug ) ) {
			$current_item  = $parent_slug;
			$selected_item = bp_action_variable( 0 );
		}

		// If the nav is not defined by the parent component, look in the Members nav.
		if ( ! isset( $bp->{$single_item_component}->nav ) ) {
			$secondary_nav_items = $bp->members->nav->get_secondary( array( 'parent_slug' => $current_item ) );
		} else {
			$secondary_nav_items = $bp->{$single_item_component}->nav->get_secondary( array( 'parent_slug' => $current_item ) );
		}

		if ( ! $secondary_nav_items ) {
			return false;
		}
	}

	// Loop through each navigation item.
	foreach ( $secondary_nav_items as $subnav_item ) {
		if ( empty( $subnav_item->user_has_access ) ) {
			continue;
		}

		// If the current action or an action variable matches the nav item id, then add a highlight CSS class.
		if ( $subnav_item->slug === $selected_item ) {
			$selected = ' class="current selected"';
		} else {
			$selected = '';
		}

		// List type depends on our current component.
		$list_type = bp_is_group() ? 'groups' : 'personal';

		// phpcs:ignore WordPress.Security.EscapeOutput
		echo apply_filters(
			/**
			 * Filters the "options nav", the secondary-level single item navigation menu.
			 *
			 * This is a dynamic filter that is dependent on the provided css_id value.
			 *
			 * @since 1.1.0
			 *
			 * @param string $value         HTML list item for the submenu item.
			 * @param array  $subnav_item   Submenu array item being displayed.
			 * @param string $selected_item Current action.
			 */
			'bp_get_options_nav_' . $subnav_item->css_id,
			'<li id="' . esc_attr( $subnav_item->css_id . '-' . $list_type . '-li' ) . '" ' . $selected . '><a id="' . esc_attr( $subnav_item->css_id ) . '" href="' . esc_url( $subnav_item->link ) . '">' . wp_kses( $subnav_item->name, array( 'span' => array( 'class' => true ) ) ) . '</a></li>',
			$subnav_item,
			$selected_item
		);
	}
}

/**
 * Get the directory title for a component.
 *
 * Used for the <title> element and the page header on the component directory
 * page.
 *
 * @since 2.0.0
 *
 * @param string $component Component to get directory title for.
 * @return string
 */
function bp_get_directory_title( $component = '' ) {
	$title = '';

	// Use the string provided by the component.
	if ( isset( buddypress()->{$component}->directory_title ) && buddypress()->{$component}->directory_title ) {
		$title = buddypress()->{$component}->directory_title;

	// If none is found, concatenate.
	} elseif ( isset( buddypress()->{$component}->name ) ) {
		/* translators: %s: Name of the BuddyPress component */
		$title = sprintf( __( '%s Directory', 'buddypress' ), buddypress()->{$component}->name );
	}

	/**
	 * Filters the directory title for a component.
	 *
	 * @since 2.0.0
	 *
	 * @param string $title     Text to be used in <title> tag.
	 * @param string $component Current component being displayed.
	 */
	return apply_filters( 'bp_get_directory_title', $title, $component );
}

/** Avatars *******************************************************************/

/**
 * Output the current avatar upload step.
 *
 * @since 1.1.0
 */
function bp_avatar_admin_step() {
	echo esc_html( bp_get_avatar_admin_step() );
}
	/**
	 * Return the current avatar upload step.
	 *
	 * @since 1.1.0
	 *
	 * @return string The current avatar upload step. Returns 'upload-image'
	 *         if none is found.
	 */
	function bp_get_avatar_admin_step() {
		$bp   = buddypress();
		$step = isset( $bp->avatar_admin->step )
			? $step = $bp->avatar_admin->step
			: 'upload-image';

		/**
		 * Filters the current avatar upload step.
		 *
		 * @since 1.1.0
		 *
		 * @param string $step The current avatar upload step.
		 */
		return apply_filters( 'bp_get_avatar_admin_step', $step );
	}

/**
 * Output the URL of the avatar to crop.
 *
 * @since 1.1.0
 */
function bp_avatar_to_crop() {
	echo esc_url( bp_get_avatar_to_crop() );
}
	/**
	 * Return the URL of the avatar to crop.
	 *
	 * @since 1.1.0
	 *
	 * @return string URL of the avatar awaiting cropping.
	 */
	function bp_get_avatar_to_crop() {
		$bp  = buddypress();
		$url = isset( $bp->avatar_admin->image->url )
			? $bp->avatar_admin->image->url
			: '';

		/**
		 * Filters the URL of the avatar to crop.
		 *
		 * @since 1.1.0
		 *
		 * @param string $url URL for the avatar.
		 */
		return apply_filters( 'bp_get_avatar_to_crop', $url );
	}

/**
 * Output the relative file path to the avatar to crop.
 *
 * @since 1.1.0
 */
function bp_avatar_to_crop_src() {
	echo esc_attr( bp_get_avatar_to_crop_src() );
}
	/**
	 * Return the relative file path to the avatar to crop.
	 *
	 * @since 1.1.0
	 *
	 * @return string Relative file path to the avatar.
	 */
	function bp_get_avatar_to_crop_src() {
		$bp  = buddypress();
		$src = isset( $bp->avatar_admin->image->dir )
			? str_replace( WP_CONTENT_DIR, '', $bp->avatar_admin->image->dir )
			: '';

		/**
		 * Filters the relative file path to the avatar to crop.
		 *
		 * @since 1.1.0
		 *
		 * @param string $src Relative file path for the avatar.
		 */
		return apply_filters( 'bp_get_avatar_to_crop_src', $src );
	}

/**
 * Output the name of the BP site. Used in RSS headers.
 *
 * @since 1.0.0
 */
function bp_site_name() {
	echo esc_html( bp_get_site_name() );
}
	/**
	 * Returns the name of the BP site. Used in RSS headers.
	 *
	 * @since 1.6.0
	 *
	 * @return string
	 */
	function bp_get_site_name() {

		/**
		 * Filters the name of the BP site. Used in RSS headers.
		 *
		 * @since 1.0.0
		 *
		 * @param string $value Current BP site name.
		 */
		return apply_filters( 'bp_site_name', get_bloginfo( 'name', 'display' ) );
	}

/**
 * Format a date based on a UNIX timestamp.
 *
 * This function can be used to turn a UNIX timestamp into a properly formatted
 * (and possibly localized) string, useful for outputting the date & time an
 * action took place.
 *
 * Not to be confused with `bp_core_time_since()`, this function is best used
 * for displaying a more exact date and time vs. a human-readable time.
 *
 * Note: This function may be improved or removed at a later date, as it is
 * hardly used and adds an additional layer of complexity to calculating dates
 * and times together with timezone offsets and i18n.
 *
 * @since 1.1.0
 *
 * @param int|string $time         The UNIX timestamp to be formatted.
 * @param bool       $exclude_time Optional. True to return only the month + day, false
 *                                 to return month, day, and time. Default: false.
 * @param bool       $gmt          Optional. True to display in local time, false to
 *                                  leave in GMT. Default: true.
 * @return mixed A string representation of $time, in the format
 *               "March 18, 2014 at 2:00 pm" (or whatever your
 *               'date_format' and 'time_format' settings are
 *               on your root blog). False on failure.
 */
function bp_format_time( $time = '', $exclude_time = false, $gmt = true ) {

	// Bail if time is empty or not numeric
	// @todo We should output something smarter here.
	if ( empty( $time ) || ! is_numeric( $time ) ) {
		return false;
	}

	// Get GMT offset from root blog.
	if ( true === $gmt ) {

		// Use Timezone string if set.
		$timezone_string = bp_get_option( 'timezone_string' );
		if ( ! empty( $timezone_string ) ) {
			$timezone_object = timezone_open( $timezone_string );
			$datetime_object = date_create( "@{$time}" );
			$timezone_offset = timezone_offset_get( $timezone_object, $datetime_object ) / HOUR_IN_SECONDS;

		// Fall back on less reliable gmt_offset.
		} else {
			$timezone_offset = bp_get_option( 'gmt_offset' );
		}

		// Calculate time based on the offset.
		$calculated_time = $time + ( $timezone_offset * HOUR_IN_SECONDS );

	// No localizing, so just use the time that was submitted.
	} else {
		$calculated_time = $time;
	}

	// Formatted date: "March 18, 2014".
	$formatted_date = date_i18n( bp_get_option( 'date_format' ), $calculated_time, $gmt );

	// Should we show the time also?
	if ( true !== $exclude_time ) {

		// Formatted time: "2:00 pm".
		$formatted_time = date_i18n( bp_get_option( 'time_format' ), $calculated_time, $gmt );

		// Return string formatted with date and time.
		$formatted_date = sprintf( esc_html__( '%1$s at %2$s', 'buddypress' ), $formatted_date, $formatted_time );
	}

	/**
	 * Filters the date based on a UNIX timestamp.
	 *
	 * @since 1.0.0
	 *
	 * @param string $formatted_date Formatted date from the timestamp.
	 */
	return apply_filters( 'bp_format_time', $formatted_date );
}

/**
 * Select between two dynamic strings, according to context.
 *
 * This function can be used in cases where a phrase used in a template will
 * differ for a user looking at his own profile and a user looking at another
 * user's profile (eg, "My Friends" and "Joe's Friends"). Pass both versions
 * of the phrase, and bp_word_or_name() will detect which is appropriate, and
 * do the necessary argument swapping for dynamic phrases.
 *
 * @since 1.0.0
 *
 * @param string $youtext    The "you" version of the phrase (eg "Your Friends").
 * @param string $nametext   The other-user version of the phrase. Should be in
 *                           a format appropriate for sprintf() - use %s in place of the displayed
 *                           user's name (eg "%'s Friends").
 * @param bool   $capitalize Optional. Force into title case. Default: true.
 * @param bool   $echo       Optional. True to echo the results, false to return them.
 *                           Default: true.
 * @return string|null $nametext If ! $echo, returns the appropriate string.
 */
function bp_word_or_name( $youtext, $nametext, $capitalize = true, $echo = true ) {

	if ( ! empty( $capitalize ) ) {
		$youtext = bp_core_ucfirst( $youtext );
	}

	if ( bp_displayed_user_id() == bp_loggedin_user_id() ) {
		if ( true == $echo ) {

			/**
			 * Filters the text used based on context of own profile or someone else's profile.
			 *
			 * @since 1.0.0
			 *
			 * @param string $youtext Context-determined string to display.
			 */
			echo esc_html( apply_filters( 'bp_word_or_name', $youtext ) );
		} else {

			/** This filter is documented in bp-core/bp-core-template.php */
			return apply_filters( 'bp_word_or_name', $youtext );
		}
	} else {
		$fullname = bp_get_displayed_user_fullname();
		$fullname = (array) explode( ' ', $fullname );
		$nametext = sprintf( $nametext, $fullname[0] );
		if ( true == $echo ) {

			/** This filter is documented in bp-core/bp-core-template.php */
			echo esc_html( apply_filters( 'bp_word_or_name', $nametext ) );
		} else {

			/** This filter is documented in bp-core/bp-core-template.php */
			return apply_filters( 'bp_word_or_name', $nametext );
		}
	}
}

/** Search Form ***************************************************************/

/**
 * Return the "action" attribute for search forms.
 *
 * @since 1.0.0
 *
 * @return string URL action attribute for search forms, eg example.com/search/.
 */
function bp_search_form_action() {
	$url = bp_rewrites_get_url(
		array(
			'component_id'     => 'core',
			'community_search' => 1,
		)
	);

	/**
	 * Filters the "action" attribute for search forms.
	 *
	 * @since 1.0.0
	 *
	 * @param string $url Search form action url.
	 */
	return apply_filters( 'bp_search_form_action', $url );
}

/**
 * Generate the basic search form as used in BP-Default's header.
 *
 * @since 1.0.0
 *
 * @return string HTML <select> element.
 */
function bp_search_form_type_select() {

	$options = array();

	if ( bp_is_active( 'xprofile' ) ) {
		$options['members'] = _x( 'Members', 'search form', 'buddypress' );
	}

	if ( bp_is_active( 'groups' ) ) {
		$options['groups']  = _x( 'Groups', 'search form', 'buddypress' );
	}

	if ( bp_is_active( 'blogs' ) && is_multisite() ) {
		$options['blogs']   = _x( 'Blogs', 'search form', 'buddypress' );
	}

	$options['posts'] = _x( 'Posts', 'search form', 'buddypress' );

	// Eventually this won't be needed and a page will be built to integrate all search results.
	$selection_box  = '<label for="search-which" class="accessibly-hidden">' . esc_html_x( 'Search these:', 'search form', 'buddypress' ) . '</label>';
	$selection_box .= '<select name="search-which" id="search-which" style="width: auto">';

	/**
	 * Filters all of the component options available for search scope.
	 *
	 * @since 1.5.0
	 *
	 * @param array $options Array of options to add to select field.
	 */
	$options = apply_filters( 'bp_search_form_type_select_options', $options );
	foreach ( (array) $options as $option_value => $option_title ) {
		$selection_box .= sprintf( '<option value="%s">%s</option>', esc_attr( $option_value ), esc_html( $option_title ) );
	}

	$selection_box .= '</select>';

	/**
	 * Filters the complete <select> input used for search scope.
	 *
	 * @since 1.0.0
	 *
	 * @param string $selection_box <select> input for selecting search scope.
	 */
	return apply_filters( 'bp_search_form_type_select', $selection_box );
}

/**
 * Output the 'name' attribute for search form input element.
 *
 * @since 2.7.0
 *
 * @param string $component See bp_get_search_input_name().
 */
function bp_search_input_name( $component = '' ) {
	echo esc_attr( bp_get_search_input_name( $component ) );
}

/**
 * Get the 'name' attribute for the search form input element.
 *
 * @since 2.7.0
 *
 * @param string $component Component name. Defaults to current component.
 * @return string Text for the 'name' attribute.
 */
function bp_get_search_input_name( $component = '' ) {
	if ( ! $component ) {
		$component = bp_current_component();
	}

	$bp = buddypress();

	$name = '';
	if ( isset( $bp->{$component}->id ) ) {
		$name = $bp->{$component}->id . '_search';
	}

	return $name;
}

/**
 * Output the placeholder text for the search box for a given component.
 *
 * @since 2.7.0
 *
 * @param string $component See bp_get_search_placeholder().
 */
function bp_search_placeholder( $component = '' ) {
	echo esc_attr( bp_get_search_placeholder( $component ) );
}

/**
 * Get the placeholder text for the search box for a given component.
 *
 * @since 2.7.0
 *
 * @param string $component Component name. Defaults to current component.
 * @return string Placeholder text for the search field.
 */
function bp_get_search_placeholder( $component = '' ) {
	$query_arg = bp_core_get_component_search_query_arg( $component );

	if ( $query_arg && ! empty( $_REQUEST[ $query_arg ] ) ) {
		$placeholder = wp_unslash( $_REQUEST[ $query_arg ] );
	} else {
		$placeholder = bp_get_search_default_text( $component );
	}

	return $placeholder;
}

/**
 * Output the default text for the search box for a given component.
 *
 * @since 1.5.0
 *
 * @see bp_get_search_default_text()
 *
 * @param string $component See {@link bp_get_search_default_text()}.
 */
function bp_search_default_text( $component = '' ) {
	echo esc_attr( bp_get_search_default_text( $component ) );
}
	/**
	 * Return the default text for the search box for a given component.
	 *
	 * @since 1.5.0
	 *
	 * @param string $component Component name. Default: current component.
	 * @return string Placeholder text for search field.
	 */
	function bp_get_search_default_text( $component = '' ) {

		$bp = buddypress();

		if ( empty( $component ) ) {
			$component = bp_current_component();
		}

		$default_text = __( 'Search anything...', 'buddypress' );

		// Most of the time, $component will be the actual component ID.
		if ( !empty( $component ) ) {
			if ( !empty( $bp->{$component}->search_string ) ) {
				$default_text = $bp->{$component}->search_string;
			} else {
				// When the request comes through AJAX, we need to get the component
				// name out of $bp->pages.
				if ( !empty( $bp->pages->{$component}->slug ) ) {
					$key = $bp->pages->{$component}->slug;
					if ( !empty( $bp->{$key}->search_string ) ) {
						$default_text = $bp->{$key}->search_string;
					}
				}
			}
		}

		/**
		 * Filters the default text for the search box for a given component.
		 *
		 * @since 1.5.0
		 *
		 * @param string $default_text Default text for search box.
		 * @param string $component    Current component displayed.
		 */
		return apply_filters( 'bp_get_search_default_text', $default_text, $component );
	}

/**
 * Output the attributes for a form field.
 *
 * @since 2.2.0
 *
 * @param string $name       The field name to output attributes for.
 * @param array  $attributes Array of existing attributes to add.
 */
function bp_form_field_attributes( $name = '', $attributes = array() ) {
	// phpcs:ignore WordPress.Security.EscapeOutput
	echo bp_get_form_field_attributes( $name, $attributes );
}
	/**
	 * Get the attributes for a form field.
	 *
	 * Primarily to add better support for touchscreen devices, but plugin devs
	 * can use the 'bp_get_form_field_extra_attributes' filter for further
	 * manipulation.
	 *
	 * @since 2.2.0
	 *
	 * @param string $name       The field name to get attributes for.
	 * @param array  $attributes Array of existing attributes to add.
	 * @return string
	 */
	function bp_get_form_field_attributes( $name = '', $attributes = array() ) {
		$retval = '';

		if ( empty( $attributes ) ) {
			$attributes = array();
		}

		$name = strtolower( $name );

		switch ( $name ) {
			case 'username' :
			case 'blogname' :
				$attributes['autocomplete']   = 'off';
				$attributes['autocapitalize'] = 'none';
				break;

			case 'email' :
				if ( wp_is_mobile() ) {
					$attributes['autocapitalize'] = 'none';
				}
				break;

			case 'password' :
				$attributes['spellcheck']   = 'false';
				$attributes['autocomplete'] = 'off';

				if ( wp_is_mobile() ) {
					$attributes['autocorrect']    = 'false';
					$attributes['autocapitalize'] = 'none';
				}
				break;
		}

		/**
		 * Filter the attributes for a field before rendering output.
		 *
		 * @since 2.2.0
		 *
		 * @param array  $attributes The field attributes.
		 * @param string $name       The field name.
		 */
		$attributes = (array) apply_filters( 'bp_get_form_field_attributes', $attributes, $name );

		foreach ( $attributes as $attr => $value ) {
			// Numeric keyed array.
			if (is_numeric( $attr ) ) {
				$retval .= sprintf( ' %s', esc_attr( $value ) );

			// Associative keyed array.
			} else {
				$retval .= sprintf( ' %s="%s"', sanitize_key( $attr ), esc_attr( $value ) );
			}
		}

		return $retval;
	}

/**
 * Create and output a button.
 *
 * @since 1.2.6
 *
 * @see bp_get_button()
 *
 * @param array|string $args See {@link BP_Button}.
 */
function bp_button( $args = '' ) {
	// Escaping is done in `BP_Core_HTML_Element()`.
	// phpcs:ignore WordPress.Security.EscapeOutput
	echo bp_get_button( $args );
}
	/**
	 * Create and return a button.
	 *
	 * @since 1.2.6
	 *
	 * @see BP_Button for a description of arguments and return value.
	 *
	 * @param array|string $args See {@link BP_Button}.
	 * @return string HTML markup for the button.
	 */
	function bp_get_button( $args = '' ) {
		$button = new BP_Button( $args );

		/**
		 * Filters the requested button output.
		 *
		 * @since 1.2.6
		 *
		 * @param string    $contents  Button context to be used.
		 * @param array     $args      Array of args for the button.
		 * @param BP_Button $button    BP_Button object.
		 */
		return apply_filters( 'bp_get_button', $button->contents, $args, $button );
	}

/**
 * Truncate text.
 *
 * Cuts a string to the length of $length and replaces the last characters
 * with the ending if the text is longer than length.
 *
 * This function is borrowed from CakePHP v2.0, under the MIT license. See
 * http://book.cakephp.org/view/1469/Text#truncate-1625
 *
 * @since 1.0.0
 * @since 2.6.0 Added 'strip_tags' and 'remove_links' as $options args.
 *
 * @param string $text   String to truncate.
 * @param int    $length Optional. Length of returned string, including ellipsis.
 *                       Default: 225.
 * @param array  $options {
 *     An array of HTML attributes and options. Each item is optional.
 *     @type string $ending            The string used after truncation.
 *                                     Default: ' [&hellip;]'.
 *     @type bool   $exact             If true, $text will be trimmed to exactly $length.
 *                                     If false, $text will not be cut mid-word. Default: false.
 *     @type bool   $html              If true, don't include HTML tags when calculating
 *                                     excerpt length. Default: true.
 *     @type bool   $filter_shortcodes If true, shortcodes will be stripped.
 *                                     Default: true.
 *     @type bool   $strip_tags        If true, HTML tags will be stripped. Default: false.
 *                                     Only applicable if $html is set to false.
 *     @type bool   $remove_links      If true, URLs will be stripped. Default: false.
 *                                     Only applicable if $html is set to false.
 * }
 * @return string Trimmed string.
 */
function bp_create_excerpt( $text, $length = 225, $options = array() ) {

	// Backward compatibility. The third argument used to be a boolean $filter_shortcodes.
	$filter_shortcodes_default = is_bool( $options ) ? $options : true;

	$r = bp_parse_args(
		$options,
		array(
			'ending'            => __( ' [&hellip;]', 'buddypress' ),
			'exact'             => false,
			'html'              => true,
			'filter_shortcodes' => $filter_shortcodes_default,
			'strip_tags'        => false,
			'remove_links'      => false,
		),
		'create_excerpt'
	);

	// Save the original text, to be passed along to the filter.
	$original_text = $text;

	/**
	 * Filters the excerpt length to trim text to.
	 *
	 * @since 1.5.0
	 *
	 * @param int $length Length of returned string, including ellipsis.
	 */
	$length = apply_filters( 'bp_excerpt_length',      $length      );

	/**
	 * Filters the excerpt appended text value.
	 *
	 * @since 1.5.0
	 *
	 * @param string $value Text to append to the end of the excerpt.
	 */
	$ending = apply_filters( 'bp_excerpt_append_text', $r['ending'] );

	// Remove shortcodes if necessary.
	if ( ! empty( $r['filter_shortcodes'] ) ) {
		$text = strip_shortcodes( $text );
	}

	// When $html is true, the excerpt should be created without including HTML tags in the
	// excerpt length.
	if ( ! empty( $r['html'] ) ) {

		// The text is short enough. No need to truncate.
		if ( mb_strlen( preg_replace( '/<.*?>/', '', $text ) ) <= $length ) {
			return $text;
		}

		$totalLength = mb_strlen( wp_strip_all_tags( $ending ) );
		$openTags    = array();
		$truncate    = '';

		// Find all the tags and HTML comments and put them in a stack for later use.
		preg_match_all( '/(<\/?([\w+!]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER );

		foreach ( $tags as $tag ) {
			// Process tags that need to be closed.
			if ( !preg_match( '/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s',  $tag[2] ) ) {
				if ( preg_match( '/<[\w]+[^>]*>/s', $tag[0] ) ) {
					array_unshift( $openTags, $tag[2] );
				} elseif ( preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag ) ) {
					$pos = array_search( $closeTag[1], $openTags );
					if ( $pos !== false ) {
						array_splice( $openTags, $pos, 1 );
					}
				}
			}

			$truncate     .= $tag[1];
			$contentLength = mb_strlen( preg_replace( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3] ) );

			if ( $contentLength + $totalLength > $length ) {
				$left = $length - $totalLength;
				$entitiesLength = 0;
				if ( preg_match_all( '/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE ) ) {
					foreach ( $entities[0] as $entity ) {
						if ( $entity[1] + 1 - $entitiesLength <= $left ) {
							$left--;
							$entitiesLength += mb_strlen( $entity[0] );
						} else {
							break;
						}
					}
				}

				$truncate .= mb_substr( $tag[3], 0 , $left + $entitiesLength );
				break;
			} else {
				$truncate .= $tag[3];
				$totalLength += $contentLength;
			}
			if ( $totalLength >= $length ) {
				break;
			}
		}
	} else {
		// Strip HTML tags if necessary.
		if ( ! empty( $r['strip_tags'] ) ) {
			$text = wp_strip_all_tags( $text );
		}

		// Remove links if necessary.
		if ( ! empty( $r['remove_links'] ) ) {
			$text = preg_replace( '#^\s*(https?://[^\s"]+)\s*$#im', '', $text );
		}

		if ( mb_strlen( $text ) <= $length ) {
			/**
			 * Filters the final generated excerpt.
			 *
			 * @since 1.1.0
			 *
			 * @param string $text          Generated excerpt.
			 * @param string $original_text Original text provided.
			 * @param int    $length        Length of returned string, including ellipsis.
			 * @param array  $options       Array of HTML attributes and options.
			 */
			return apply_filters( 'bp_create_excerpt', $text, $original_text, $length, $options );
		} else {
			$truncate = mb_substr( $text, 0, $length - mb_strlen( $ending ) );
		}
	}

	// If $exact is false, we can't break on words.
	if ( empty( $r['exact'] ) ) {
		// Find the position of the last space character not part of a tag.
		preg_match_all( '/<[a-z\!\/][^>]*>/', $truncate, $_truncate_tags, PREG_OFFSET_CAPTURE );

		// Rekey tags by the string index of their last character.
		$truncate_tags = array();
		if ( ! empty( $_truncate_tags[0] ) ) {
			foreach ( $_truncate_tags[0] as $_tt ) {
				$_tt['start'] = $_tt[1];
				$_tt['end']   = $_tt[1] + strlen( $_tt[0] );
				$truncate_tags[ $_tt['end'] ] = $_tt;
			}
		}

		$truncate_length = mb_strlen( $truncate );
		$spacepos = $truncate_length + 1;
		for ( $pos = $truncate_length - 1; $pos >= 0; $pos-- ) {
			// Word boundaries are spaces and the close of HTML tags, when the tag is preceded by a space.
			$is_word_boundary = ' ' === $truncate[ $pos ];
			if ( ! $is_word_boundary && isset( $truncate_tags[ $pos - 1 ] ) ) {
				$preceding_tag    = $truncate_tags[ $pos - 1 ];
				if ( ' ' === $truncate[ $preceding_tag['start'] - 1 ] ) {
					$is_word_boundary = true;
					break;
				}
			}

			if ( ! $is_word_boundary ) {
				continue;
			}

			// If there are no tags in the string, the first space found is the right one.
			if ( empty( $truncate_tags ) ) {
				$spacepos = $pos;
				break;
			}

			// Look at each tag to see if the space is inside of it.
			$intag = false;
			foreach ( $truncate_tags as $tt ) {
				if ( $pos > $tt['start'] && $pos < $tt['end'] ) {
					$intag = true;
					break;
				}
			}

			if ( ! $intag ) {
				$spacepos = $pos;
				break;
			}
		}

		if ( $r['html'] ) {
			$bits = mb_substr( $truncate, $spacepos );
			preg_match_all( '/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER );
			if ( !empty( $droppedTags ) ) {
				foreach ( $droppedTags as $closingTag ) {
					if ( !in_array( $closingTag[1], $openTags ) ) {
						array_unshift( $openTags, $closingTag[1] );
					}
				}
			}
		}

		$truncate = rtrim( mb_substr( $truncate, 0, $spacepos ) );
	}
	$truncate .= $ending;

	if ( !empty( $r['html'] ) ) {
		foreach ( $openTags as $tag ) {
			$truncate .= '</' . $tag . '>';
		}
	}

	/** This filter is documented in /bp-core/bp-core-template.php */
	return apply_filters( 'bp_create_excerpt', $truncate, $original_text, $length, $options );
}
add_filter( 'bp_create_excerpt', 'stripslashes_deep'  );
add_filter( 'bp_create_excerpt', 'force_balance_tags' );

/**
 * Output the total member count for the site.
 *
 * @since 1.2.0
 */
function bp_total_member_count() {
	echo esc_html( bp_get_total_member_count() );
}
	/**
	 * Return the total member count for the site.
	 *
	 * Since BuddyPress 1.6, this function has used bp_core_get_active_member_count(),
	 * which counts non-spam, non-deleted users who have last_activity.
	 * This value will correctly match the total member count number used
	 * for pagination on member directories.
	 *
	 * Before BuddyPress 1.6, this function used bp_core_get_total_member_count(),
	 * which did not take into account last_activity, and thus often
	 * resulted in higher counts than shown by member directory pagination.
	 *
	 * @since 1.2.0
	 *
	 * @return int Member count.
	 */
	function bp_get_total_member_count() {

		/**
		 * Filters the total member count for the site.
		 *
		 * @since 1.2.0
		 *
		 * @param int $member_count Member count.
		 */
		return apply_filters( 'bp_get_total_member_count', bp_core_get_active_member_count() );
	}
	add_filter( 'bp_get_total_member_count', 'bp_core_number_format' );

	/**
	 * Is blog signup allowed?
	 *
	 * Returns true if is_multisite() and blog creation is enabled at
	 * Network Admin > Settings.
	 *
	 * @since 1.2.0
	 *
	 * @return bool True if blog signup is allowed, otherwise false.
	 */
	function bp_get_blog_signup_allowed() {

		if ( ! is_multisite() ) {
			return false;
		}

		$status = bp_core_get_root_option( 'registration' );
		if ( ( 'none' !== $status ) && ( 'user' !== $status ) ) {
			return true;
		}

		return false;
	}

/**
 * Check whether an activation has just been completed.
 *
 * @since 1.1.0
 *
 * @return bool True if the activation_complete global flag has been set,
 *              otherwise false.
 */
function bp_account_was_activated() {
	$bp = buddypress();

	$activation_complete = ! empty( $bp->activation_complete ) || ( bp_is_current_component( 'activate' ) && ! empty( $_GET['activated'] ) );

	return $activation_complete;
}

/**
 * Check whether registrations require activation on this installation.
 *
 * On a normal BuddyPress installation, all registrations require email
 * activation. This filter exists so that customizations that omit activation
 * can remove certain notification text from the registration screen.
 *
 * @since 1.2.0
 *
 * @return bool True by default.
 */
function bp_registration_needs_activation() {

	/**
	 * Filters whether registrations require activation on this installation.
	 *
	 * @since 1.2.0
	 *
	 * @param bool $value Whether registrations require activation. Default true.
	 */
	return apply_filters( 'bp_registration_needs_activation', true );
}

/**
 * Retrieve a client friendly version of the root blog name.
 *
 * The blogname option is escaped with esc_html on the way into the database in
 * sanitize_option, we want to reverse this for the plain text arena of emails.
 *
 * @since 1.7.0
 * @since 2.5.0 No longer used by BuddyPress, but not deprecated in case any existing plugins use it.
 *
 * @see https://buddypress.trac.wordpress.org/ticket/4401
 *
 * @param array $args {
 *     Array of optional parameters.
 *     @type string $before  String to appear before the site name in the
 *                           email subject. Default: '['.
 *     @type string $after   String to appear after the site name in the
 *                           email subject. Default: ']'.
 *     @type string $default The default site name, to be used when none is
 *                           found in the database. Default: 'Community'.
 *     @type string $text    Text to append to the site name (ie, the main text of
 *                           the email subject).
 * }
 * @return string Sanitized email subject.
 */
function bp_get_email_subject( $args = array() ) {

	$r = bp_parse_args(
		$args,
		array(
			'before'  => '[',
			'after'   => ']',
			'default' => __( 'Community', 'buddypress' ),
			'text'    => '',
		),
		'get_email_subject'
	);

	$subject = $r['before'] . wp_specialchars_decode( bp_get_option( 'blogname', $r['default'] ), ENT_QUOTES ) . $r['after'] . ' ' . $r['text'];

	/**
	 * Filters a client friendly version of the root blog name.
	 *
	 * @since 1.7.0
	 *
	 * @param string $subject Client friendly version of the root blog name.
	 * @param array  $r       Array of arguments for the email subject.
	 */
	return apply_filters( 'bp_get_email_subject', $subject, $r );
}

/**
 * Allow templates to pass parameters directly into the template loops via AJAX.
 *
 * For the most part this will be filtered in a theme's functions.php for
 * example in the default theme it is filtered via bp_dtheme_ajax_querystring().
 *
 * By using this template tag in the templates it will stop them from showing
 * errors if someone copies the templates from the default theme into another
 * WordPress theme without coping the functions from functions.php.
 *
 * @since 1.2.0
 *
 * @param string|bool $object Current template component.
 * @return string The AJAX querystring.
 */
function bp_ajax_querystring( $object = false ) {
	$bp = buddypress();

	if ( ! isset( $bp->ajax_querystring ) ) {
		$bp->ajax_querystring = '';
	}

	/**
	 * Filters the template parameters to be used in the query string.
	 *
	 * Allows templates to pass parameters into the template loops via AJAX.
	 *
	 * @since 1.2.0
	 *
	 * @param string $ajax_querystring Current query string.
	 * @param string $object           Current template component.
	 */
	return apply_filters( 'bp_ajax_querystring', $bp->ajax_querystring, $object );
}

/** Template Classes and _is functions ****************************************/

/**
 * Return the name of the current component.
 *
 * @since 1.0.0
 *
 * @return string Component name.
 */
function bp_current_component() {
	$bp                = buddypress();
	$current_component = !empty( $bp->current_component )
		? $bp->current_component
		: false;

	/**
	 * Filters the name of the current component.
	 *
	 * @since 1.0.0
	 *
	 * @param string|bool $current_component Current component if available or false.
	 */
	return apply_filters( 'bp_current_component', $current_component );
}

/**
 * Return the name of the current action.
 *
 * @since 1.0.0
 *
 * @return string Action name.
 */
function bp_current_action() {
	$bp             = buddypress();
	$current_action = !empty( $bp->current_action )
		? $bp->current_action
		: '';

	/**
	 * Filters the name of the current action.
	 *
	 * @since 1.0.0
	 *
	 * @param string $current_action Current action.
	 */
	return apply_filters( 'bp_current_action', $current_action );
}

/**
 * Return the name of the current item.
 *
 * @since 1.1.0
 *
 * @return string|bool
 */
function bp_current_item() {
	$bp           = buddypress();
	$current_item = !empty( $bp->current_item )
		? $bp->current_item
		: false;

	/**
	 * Filters the name of the current item.
	 *
	 * @since 1.1.0
	 *
	 * @param string|bool $current_item Current item if available or false.
	 */
	return apply_filters( 'bp_current_item', $current_item );
}

/**
 * Return the value of $bp->action_variables.
 *
 * @since 1.0.0
 *
 * @return array|bool $action_variables The action variables array, or false
 *                                      if the array is empty.
 */
function bp_action_variables() {
	$bp               = buddypress();
	$action_variables = !empty( $bp->action_variables )
		? $bp->action_variables
		: false;

	/**
	 * Filters the value of $bp->action_variables.
	 *
	 * @since 1.0.0
	 *
	 * @param array|bool $action_variables Available action variables.
	 */
	return apply_filters( 'bp_action_variables', $action_variables );
}

/**
 * Return the value of a given action variable.
 *
 * @since 1.5.0
 *
 * @param int $position The key of the action_variables array that you want.
 * @return string|bool $action_variable The value of that position in the
 *                                      array, or false if not found.
 */
function bp_action_variable( $position = 0 ) {
	$action_variables = bp_action_variables();
	$action_variable  = isset( $action_variables[ $position ] )
		? $action_variables[ $position ]
		: false;

	/**
	 * Filters the value of a given action variable.
	 *
	 * @since 1.5.0
	 *
	 * @param string|bool $action_variable Requested action variable based on position.
	 * @param int         $position        The key of the action variable requested.
	 */
	return apply_filters( 'bp_action_variable', $action_variable, $position );
}

/**
 * Returns the BP root blog's domain name.
 *
 * @since 12.0.0
 *
 * @return string The BP root blog's domain name.
 */
function bp_get_domain() {
	return wp_parse_url( bp_get_root_url(), PHP_URL_HOST );
}

/**
 * Gets the BP root blog's URL.
 *
 * @since 12.0.0
 *
 * @return string The BP root blog's URL.
 */
function bp_get_root_url() {
	$bp = buddypress();

	if ( ! empty( $bp->root_url ) ) {
		$url = $bp->root_url;
	} else {
		$url          = bp_rewrites_get_root_url();
		$bp->root_url = $url;
	}

	/**
	 * Filters the "root url", the URL of the BP root blog.
	 *
	 * @since 12.0.0
	 *
	 * @param string $url URL of the BP root blog.
	 */
	return apply_filters( 'bp_get_root_url', $url );
}

/**
 * Output the "root url", the URL of the BP root blog.
 *
 * @since 12.0.0
 */
function bp_root_url() {
	echo esc_url( bp_get_root_url() );
}

/**
 * Output the root slug for a given component.
 *
 * @since 1.5.0
 *
 * @param string $component The component name.
 */
function bp_root_slug( $component = '' ) {
	echo esc_attr( bp_get_root_slug( $component ) );
}
	/**
	 * Get the root slug for given component.
	 *
	 * The "root slug" is the string used when concatenating component
	 * directory URLs. For example, on an installation where the Groups
	 * component's directory is located at http://example.com/groups/, the
	 * root slug for the Groups component is 'groups'. This string
	 * generally corresponds to page_name of the component's directory
	 * page.
	 *
	 * In order to maintain backward compatibility, the following procedure
	 * is used:
	 * 1) Use the short slug to get the canonical component name from the
	 *    active component array.
	 * 2) Use the component name to get the root slug out of the
	 *    appropriate part of the $bp global.
	 * 3) If nothing turns up, it probably means that $component is itself
	 *    a root slug.
	 *
	 * Example: If your groups directory is at /community/companies, this
	 * function first uses the short slug 'companies' (ie the current
	 * component) to look up the canonical name 'groups' in
	 * $bp->active_components. Then it uses 'groups' to get the root slug,
	 * from $bp->groups->root_slug.
	 *
	 * @since 1.5.0
	 *
	 * @param string $component Optional. Defaults to the current component.
	 * @return string $root_slug The root slug.
	 */
	function bp_get_root_slug( $component = '' ) {
		$bp        = buddypress();
		$root_slug = '';

		// Use current global component if none passed.
		if ( empty( $component ) ) {
			$component = bp_current_component();
		}

		// Component is active.
		if ( ! empty( $bp->active_components[ $component ] ) ) {

			// Backward compatibility: in legacy plugins, the canonical component id
			// was stored as an array value in $bp->active_components.
			$component_name = ( '1' == $bp->active_components[ $component ] )
				? $component
				: $bp->active_components[$component];

			// Component has specific root slug.
			if ( ! empty( $bp->{$component_name}->root_slug ) ) {
				$root_slug = $bp->{$component_name}->root_slug;
			}
		}

		// No specific root slug, so fall back to component slug.
		if ( empty( $root_slug ) ) {
			$root_slug = $component;
		}

		/**
		 * Filters the root slug for given component.
		 *
		 * @since 1.5.0
		 *
		 * @param string $root_slug Root slug for given component.
		 * @param string $component Current component.
		 */
		return apply_filters( 'bp_get_root_slug', $root_slug, $component );
	}

/**
 * Return the component name based on a root slug.
 *
 * @since 1.5.0
 *
 * @param string $root_slug Needle to our active component haystack.
 * @return mixed False if none found, component name if found.
 */
function bp_get_name_from_root_slug( $root_slug = '' ) {
	$bp = buddypress();

	// If no slug is passed, look at current_component.
	if ( empty( $root_slug ) ) {
		$root_slug = bp_current_component();
	}

	// No current component or root slug, so flee.
	if ( empty( $root_slug ) ) {
		return false;
	}

	// Loop through active components and look for a match.
	foreach ( array_keys( $bp->active_components ) as $component ) {
		if ( ( ! empty( $bp->{$component}->slug ) && ( $bp->{$component}->slug == $root_slug ) ) || ( ! empty( $bp->{$component}->root_slug ) && ( $bp->{$component}->root_slug === $root_slug ) ) ) {
			return $bp->{$component}->name;
		}
	}

	return false;
}

/**
 * Returns whether or not a user has access.
 *
 * @since 1.2.4
 *
 * @return bool
 */
function bp_user_has_access() {
	$has_access = bp_current_user_can( 'bp_moderate' ) || bp_is_my_profile();

	/**
	 * Filters whether or not a user has access.
	 *
	 * @since 1.2.4
	 *
	 * @param bool $has_access Whether or not user has access.
	 */
	return (bool) apply_filters( 'bp_user_has_access', $has_access );
}

/**
 * Output the search slug.
 *
 * @since 1.5.0
 *
 */
function bp_search_slug() {
	echo esc_attr( bp_get_search_slug() );
}
	/**
	 * Return the search slug.
	 *
	 * @since 1.5.0
	 *
	 * @return string The search slug. Default: 'search'.
	 */
	function bp_get_search_slug() {

		/**
		 * Filters the search slug.
		 *
		 * @since 1.5.0
		 *
		 * @const string BP_SEARCH_SLUG The search slug. Default "search".
		 */
		return apply_filters( 'bp_get_search_slug', BP_SEARCH_SLUG );
	}

/**
 * Get the ID of the currently displayed user.
 *
 * @since 1.0.0
 *
 * @return int $id ID of the currently displayed user.
 */
function bp_displayed_user_id() {
	$bp = buddypress();
	$id = !empty( $bp->displayed_user->id )
		? $bp->displayed_user->id
		: 0;

	/**
	 * Filters the ID of the currently displayed user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id ID of the currently displayed user.
	 */
	return (int) apply_filters( 'bp_displayed_user_id', $id );
}

/**
 * Get the ID of the currently logged-in user.
 *
 * @since 1.0.0
 *
 * @return int ID of the logged-in user.
 */
function bp_loggedin_user_id() {
	$bp = buddypress();
	$id = !empty( $bp->loggedin_user->id )
		? $bp->loggedin_user->id
		: 0;

	/**
	 * Filters the ID of the currently logged-in user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id ID of the currently logged-in user.
	 */
	return (int) apply_filters( 'bp_loggedin_user_id', $id );
}

/** The is_() functions to determine the current page *****************************/

/**
 * Check to see whether the current page belongs to the specified component.
 *
 * This function is designed to be generous, accepting several different kinds
 * of value for the $component parameter. It checks $component_name against:
 * - the component's root_slug, which matches the page slug in $bp->pages.
 * - the component's regular slug.
 * - the component's id, or 'canonical' name.
 *
 * @since 1.5.0
 *
 * @param string $component Name of the component being checked.
 * @return bool Returns true if the component matches, or else false.
 */
function bp_is_current_component( $component = '' ) {

	// Default is no match. We'll check a few places for matches.
	$is_current_component = false;

	// Always return false if a null value is passed to the function.
	if ( empty( $component ) ) {
		return false;
	}

	// Backward compatibility: 'xprofile' should be read as 'profile'.
	if ( 'xprofile' === $component ) {
		$component = 'profile';
	}

	$bp = buddypress();

	// Only check if BuddyPress found a current_component.
	if ( ! empty( $bp->current_component ) ) {

		// First, check to see whether $component_name and the current
		// component are a simple match.
		if ( $bp->current_component == $component ) {
			$is_current_component = true;

		// Since the current component is based on the visible URL slug let's
		// check the component being passed and see if its root_slug matches.
		} elseif ( isset( $bp->{$component}->root_slug ) && $bp->{$component}->root_slug == $bp->current_component ) {
			$is_current_component = true;

		// Because slugs can differ from root_slugs, we should check them too.
		} elseif ( isset( $bp->{$component}->slug ) && $bp->{$component}->slug == $bp->current_component ) {
			$is_current_component = true;

		// Next, check to see whether $component is a canonical,
		// non-translatable component name. If so, we can return its
		// corresponding slug from $bp->active_components.
		} elseif ( $key = array_search( $component, $bp->active_components ) ) {
			if ( strstr( $bp->current_component, $key ) ) {
				$is_current_component = true;
			}

		// If we haven't found a match yet, check against the root_slugs
		// created by $bp->pages, as well as the regular slugs.
		} else {
			foreach ( $bp->active_components as $id ) {
				// If the $component parameter does not match the current_component,
				// then move along, these are not the droids you are looking for.
				if ( empty( $bp->{$id}->root_slug ) || $bp->{$id}->root_slug != $bp->current_component ) {
					continue;
				}

				if ( $id == $component ) {
					$is_current_component = true;
					break;
				}
			}
		}
	}

	/**
	 * Filters whether the current page belongs to the specified component.
	 *
	 * @since 1.5.0
	 *
	 * @param bool   $is_current_component Whether or not the current page belongs to specified component.
	 * @param string $component            Name of the component being checked.
	 */
	return apply_filters( 'bp_is_current_component', $is_current_component, $component );
}

/**
 * Check to see whether the current page matches a given action.
 *
 * Along with bp_is_current_component() and bp_is_action_variable(), this
 * function is mostly used to help determine when to use a given screen
 * function.
 *
 * In BP parlance, the current_action is the URL chunk that comes directly
 * after the current item slug. E.g., in
 *   http://example.com/groups/my-group/members
 * the current_action is 'members'.
 *
 * @since 1.5.0
 *
 * @param string $action The action being tested against.
 * @return bool True if the current action matches $action.
 */
function bp_is_current_action( $action = '' ) {
	return (bool) ( $action === bp_current_action() );
}

/**
 * Check to see whether the current page matches a given action_variable.
 *
 * Along with bp_is_current_component() and bp_is_current_action(), this
 * function is mostly used to help determine when to use a given screen
 * function.
 *
 * In BP parlance, action_variables are an array made up of the URL chunks
 * appearing after the current_action in a URL. For example,
 *   http://example.com/groups/my-group/admin/group-settings
 * $action_variables[0] is 'group-settings'.
 *
 * @since 1.5.0
 *
 * @param string   $action_variable The action_variable being tested against.
 * @param int|bool $position        Optional. The array key you're testing against. If you
 *                                  don't provide a $position, the function will return true if the
 *                                  $action_variable is found *anywhere* in the action variables array.
 * @return bool True if $action_variable matches at the $position provided.
 */
function bp_is_action_variable( $action_variable = '', $position = false ) {
	$is_action_variable = false;

	if ( false !== $position ) {
		// When a $position is specified, check that slot in the action_variables array.
		if ( $action_variable ) {
			$is_action_variable = $action_variable == bp_action_variable( $position );
		} else {
			// If no $action_variable is provided, we are essentially checking to see
			// whether the slot is empty.
			$is_action_variable = !bp_action_variable( $position );
		}
	} else {
		// When no $position is specified, check the entire array.
		$is_action_variable = in_array( $action_variable, (array)bp_action_variables() );
	}

	/**
	 * Filters whether the current page matches a given action_variable.
	 *
	 * @since 1.5.0
	 *
	 * @param bool   $is_action_variable Whether the current page matches a given action_variable.
	 * @param string $action_variable    The action_variable being tested against.
	 * @param int    $position           The array key tested against.
	 */
	return apply_filters( 'bp_is_action_variable', $is_action_variable, $action_variable, $position );
}

/**
 * Check against the current_item.
 *
 * @since 1.5.0
 *
 * @param string $item The item being checked.
 * @return bool True if $item is the current item.
 */
function bp_is_current_item( $item = '' ) {
	$retval = ( $item === bp_current_item() );

	/**
	 * Filters whether or not an item is the current item.
	 *
	 * @since 2.1.0
	 *
	 * @param bool   $retval Whether or not an item is the current item.
	 * @param string $item   The item being checked.
	 */
	return (bool) apply_filters( 'bp_is_current_item', $retval, $item );
}

/**
 * Are we looking at a single item? (group, user, etc).
 *
 * @since 1.1.0
 *
 * @return bool True if looking at a single item, otherwise false.
 */
function bp_is_single_item() {
	$bp     = buddypress();
	$retval = false;

	if ( isset( $bp->is_single_item ) ) {
		$retval = $bp->is_single_item;
	}

	/**
	 * Filters whether or not an item is the a single item. (group, user, etc)
	 *
	 * @since 2.1.0
	 *
	 * @param bool $retval Whether or not an item is a single item.
	 */
	return (bool) apply_filters( 'bp_is_single_item', $retval );
}

/**
 * Is the logged-in user an admin for the current item?
 *
 * @since 1.5.0
 *
 * @return bool True if the current user is an admin for the current item,
 *              otherwise false.
 */
function bp_is_item_admin() {
	$bp     = buddypress();
	$retval = false;

	if ( isset( $bp->is_item_admin ) ) {
		$retval = $bp->is_item_admin;
	}

	/**
	 * Filters whether or not the logged-in user is an admin for the current item.
	 *
	 * @since 2.1.0
	 *
	 * @param bool $retval Whether or not the logged-in user is an admin.
	 */
	return (bool) apply_filters( 'bp_is_item_admin', $retval );
}

/**
 * Is the logged-in user a mod for the current item?
 *
 * @since 1.5.0
 *
 * @return bool True if the current user is a mod for the current item,
 *              otherwise false.
 */
function bp_is_item_mod() {
	$bp     = buddypress();
	$retval = false;

	if ( isset( $bp->is_item_mod ) ) {
		$retval = $bp->is_item_mod;
	}

	/**
	 * Filters whether or not the logged-in user is a mod for the current item.
	 *
	 * @since 2.1.0
	 *
	 * @param bool $retval Whether or not the logged-in user is a mod.
	 */
	return (bool) apply_filters( 'bp_is_item_mod', $retval );
}

/**
 * Is this a component directory page?
 *
 * @since 1.0.0
 *
 * @return bool True if the current page is a component directory, otherwise false.
 */
function bp_is_directory() {
	$bp     = buddypress();
	$retval = false;

	if ( isset( $bp->is_directory ) ) {
		$retval = $bp->is_directory;
	}

	/**
	 * Filters whether or not user is on a component directory page.
	 *
	 * @since 2.1.0
	 *
	 * @param bool $retval Whether or not user is on a component directory page.
	 */
	return (bool) apply_filters( 'bp_is_directory', $retval );
}

/**
 * Check to see if a component's URL should be in the root, not under a member page.
 *
 * - Yes ('groups' is root)    : http://example.com/groups/the-group
 * - No  ('groups' is not-root): http://example.com/members/andy/groups/the-group
 *
 * This function is on the chopping block. It's currently only used by a few
 * already deprecated functions.
 *
 * @since 1.5.0
 *
 * @param string $component_name Component name to check.
 *
 * @return bool True if root component, else false.
 */
function bp_is_root_component( $component_name = '' ) {
	$bp     = buddypress();
	$retval = false;

	// Default to the current component if none is passed.
	if ( empty( $component_name ) ) {
		$component_name = bp_current_component();
	}

	// Loop through active components and check for key/slug matches.
	if ( ! empty( $bp->active_components ) ) {
		foreach ( (array) $bp->active_components as $key => $slug ) {
			if ( ( $key === $component_name ) || ( $slug === $component_name ) ) {
				$retval = true;
				break;
			}
		}
	}

	/**
	 * Filters whether or not a component's URL should be in the root, not under a member page.
	 *
	 * @since 2.1.0
	 *
	 * @param bool $retval Whether or not URL should be in the root.
	 */
	return (bool) apply_filters( 'bp_is_root_component', $retval );
}

/**
 * Check if the specified BuddyPress component directory is set to be the front page.
 *
 * Corresponds to the setting in wp-admin's Settings > Reading screen.
 *
 * @since 1.5.0
 *
 * @global int $current_blog WordPress global for the current blog.
 *
 * @param string $component Optional. Name of the component to check for.
 *                          Default: current component.
 * @return bool True if the specified component is set to be the site's front
 *              page, otherwise false.
 */
function bp_is_component_front_page( $component = '' ) {
	global $current_blog;

	$bp = buddypress();

	// Default to the current component if none is passed.
	if ( empty( $component ) ) {
		$component = bp_current_component();
	}

	// Get the path for the current blog/site.
	$path = is_main_site()
		? bp_core_get_site_path()
		: $current_blog->path;

	// Get the front page variables.
	$show_on_front = get_option( 'show_on_front' );
	$page_on_front = get_option( 'page_on_front' );

	if ( ( 'page' !== $show_on_front ) || empty( $component ) || empty( $bp->pages->{$component} ) || ( $_SERVER['REQUEST_URI'] !== $path ) ) {
		return false;
	}

	/**
	 * Filters whether or not the specified BuddyPress component directory is set to be the front page.
	 *
	 * @since 1.5.0
	 *
	 * @param bool   $value     Whether or not the specified component directory is set as front page.
	 * @param string $component Current component being checked.
	 */
	return (bool) apply_filters( 'bp_is_component_front_page', ( $bp->pages->{$component}->id == $page_on_front ), $component );
}

/**
 * Is this a blog page, ie a non-BP page?
 *
 * You can tell if a page is displaying BP content by whether the
 * current_component has been defined.
 *
 * @since 1.0.0
 *
 * @return bool True if it's a non-BP page, false otherwise.
 */
function bp_is_blog_page() {

	$is_blog_page = false;

	// Generally, we can just check to see that there's no current component.
	// The one exception is single user home tabs, where $bp->current_component
	// is unset. Thus the addition of the bp_is_user() check.
	if ( ! bp_current_component() && ! bp_is_user() ) {
		$is_blog_page = true;
	}

	/**
	 * Filters whether or not current page is a blog page or not.
	 *
	 * @since 1.5.0
	 *
	 * @param bool $is_blog_page Whether or not current page is a blog page.
	 */
	return (bool) apply_filters( 'bp_is_blog_page', $is_blog_page );
}

/**
 * Checks whether the requested URL is site home's one.
 *
 * @since 12.1.0
 *
 * @return bool True if the requested URL is site home's one. False otherwise.
 */
function bp_is_site_home() {
	$requested_url = bp_get_requested_url();
	$home_url      = home_url( '/' );

	if ( is_customize_preview() && ! bp_is_email_customizer() ) {
		$requested_url = wp_parse_url( $requested_url, PHP_URL_PATH );
		$home_url      = wp_parse_url( $home_url, PHP_URL_PATH );
	}

	return $home_url === $requested_url;
}

/**
 * Is this a BuddyPress component?
 *
 * You can tell if a page is displaying BP content by whether the
 * current_component has been defined.
 *
 * Generally, we can just check to see that there's no current component.
 * The one exception is single user home tabs, where $bp->current_component
 * is unset. Thus the addition of the bp_is_user() check.
 *
 * @since 1.7.0
 *
 * @return bool True if it's a BuddyPress page, false otherwise.
 */
function is_buddypress() {
	$retval = (bool) ( bp_current_component() || bp_is_user() );

	/**
	 * Filters whether or not this is a BuddyPress component.
	 *
	 * @since 1.7.0
	 *
	 * @param bool $retval Whether or not this is a BuddyPress component.
	 */
	return apply_filters( 'is_buddypress', $retval );
}

/** Components ****************************************************************/

/**
 * Check whether a given component (or feature of a component) is active.
 *
 * @since 1.2.0 See r2539.
 * @since 2.3.0 Added $feature as a parameter.
 *
 * @param string $component The component name.
 * @param string $feature   The feature name.
 * @return bool
 */
function bp_is_active( $component = '', $feature = '' ) {
	$retval = false;

	// Default to the current component if none is passed.
	if ( empty( $component ) ) {
		$component = bp_current_component();
	}

	// Is component in either the active or required components arrays.
	if ( isset( buddypress()->active_components[ $component ] ) || in_array( $component, buddypress()->required_components, true ) ) {
		$retval = true;

		// Is feature active?
		if ( ! empty( $feature ) ) {
			// The xProfile component is specific.
			if ( 'xprofile' === $component ) {
				$component = 'profile';

				// The Cover Image feature has been moved to the Members component in 6.0.0.
				if ( 'cover_image' === $feature && 'profile' === $component ) {
					_doing_it_wrong( 'bp_is_active( \'profile\', \'cover_image\' )', esc_html__( 'The cover image is a Members component feature, please use bp_is_active( \'members\', \'cover_image\' ) instead.', 'buddypress' ), '6.0.0' );
					$members_component = buddypress()->members;

					if ( ! isset( $members_component->features ) || false === in_array( $feature, $members_component->features, true ) ) {
						$retval = false;
					}

					/** This filter is documented in wp-includes/deprecated.php */
					return apply_filters_deprecated( 'bp_is_profile_cover_image_active', array( $retval ), '6.0.0', 'bp_is_members_cover_image_active' );
				}
			}

			$component_features = isset( buddypress()->{$component}->features ) ? buddypress()->{$component}->features : array();

			if ( empty( $component_features ) || false === in_array( $feature, $component_features, true ) ) {
				$retval = false;
			}

			/**
			 * Filters whether or not a given feature for a component is active.
			 *
			 * This is a variable filter that is based on the component and feature
			 * that you are checking of active status of.
			 *
			 * @since 2.3.0
			 *
			 * @param bool $retval
			 */
			$retval = apply_filters( "bp_is_{$component}_{$feature}_active", $retval );
		}
	}

	/**
	 * Filters whether or not a given component has been activated by the admin.
	 *
	 * @since 2.1.0
	 *
	 * @param bool   $retval    Whether or not a given component has been activated by the admin.
	 * @param string $component Current component being checked.
	 */
	return apply_filters( 'bp_is_active', $retval, $component );
}

/**
 * Check whether the current page is part of the Members component.
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is part of the Members component.
 */
function bp_is_members_component() {
	return (bool) bp_is_current_component( 'members' );
}

/**
 * Check whether the current page is part of the Profile component.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of the Profile component.
 */
function bp_is_profile_component() {
	return (bool) bp_is_current_component( 'xprofile' );
}

/**
 * Check whether the current page is part of the Activity component.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of the Activity component.
 */
function bp_is_activity_component() {
	return (bool) bp_is_current_component( 'activity' );
}

/**
 * Check whether the current page is part of the Blogs component.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of the Blogs component.
 */
function bp_is_blogs_component() {
	return (bool) ( is_multisite() && bp_is_current_component( 'blogs' ) );
}

/**
 * Check whether the current page is part of the Messages component.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of the Messages component.
 */
function bp_is_messages_component() {
	return (bool) bp_is_current_component( 'messages' );
}

/**
 * Check whether the current page is part of the Friends component.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of the Friends component.
 */
function bp_is_friends_component() {
	return (bool) bp_is_current_component( 'friends' );
}

/**
 * Check whether the current page is part of the Groups component.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of the Groups component.
 */
function bp_is_groups_component() {
	return (bool) bp_is_current_component( 'groups' );
}

/**
 * Check whether the current page is part of the Forums component.
 *
 * @since 1.5.0
 * @since 3.0.0 Required for bbPress 2 integration.
 *
 * @return bool True if the current page is part of the Forums component.
 */
function bp_is_forums_component() {
	return (bool) bp_is_current_component( 'forums' );
}

/**
 * Check whether the current page is part of the Notifications component.
 *
 * @since 1.9.0
 *
 * @return bool True if the current page is part of the Notifications component.
 */
function bp_is_notifications_component() {
	return (bool) bp_is_current_component( 'notifications' );
}

/**
 * Check whether the current page is part of the Settings component.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of the Settings component.
 */
function bp_is_settings_component() {
	return (bool) bp_is_current_component( 'settings' );
}

/**
 * Check whether the current page is an Invitations screen.
 *
 * @since 8.0.0
 *
 * @return bool True if the current page is an Invitations screen.
 */
function bp_is_members_invitations_screen() {
	return (bool) bp_is_current_component( bp_get_members_invitations_slug() );
}

/**
 * Is the current component an active core component?
 *
 * Use this function when you need to check if the current component is an
 * active core component of BuddyPress. If the current component is inactive, it
 * will return false. If the current component is not part of BuddyPress core,
 * it will return false. If the current component is active, and is part of
 * BuddyPress core, it will return true.
 *
 * @since 1.7.0
 *
 * @return bool True if the current component is active and is one of BP's
 *              packaged components.
 */
function bp_is_current_component_core() {
	$retval = false;

	foreach ( bp_core_get_packaged_component_ids() as $active_component ) {
		if ( bp_is_current_component( $active_component ) ) {
			$retval = true;
			break;
		}
	}

	return $retval;
}

/** Activity ******************************************************************/

/**
 * Is the current page the activity directory?
 *
 * @since 2.0.0
 *
 * @return bool True if the current page is the activity directory.
 */
function bp_is_activity_directory() {
	if ( ! bp_displayed_user_id() && bp_is_activity_component() && ! bp_current_action() ) {
		return true;
	}

	return false;
}

/**
 * Is the current page a single activity item permalink?
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is a single activity item permalink.
 */
function bp_is_single_activity() {
	return (bool) ( bp_is_activity_component() && is_numeric( bp_current_action() ) );
}

/** User **********************************************************************/

/**
 * Is the current page the members directory?
 *
 * @since 2.0.0
 *
 * @return bool True if the current page is the members directory.
 */
function bp_is_members_directory() {
	if ( ! bp_is_user() && bp_is_members_component() ) {
		return true;
	}

	return false;
}

/**
 * Is the current page part of the profile of the logged-in user?
 *
 * Will return true for any subpage of the logged-in user's profile, eg
 * http://example.com/members/joe/friends/.
 *
 * @since 1.2.0
 *
 * @return bool True if the current page is part of the profile of the logged-in user.
 */
function bp_is_my_profile() {
	if ( is_user_logged_in() && bp_loggedin_user_id() == bp_displayed_user_id() ) {
		$my_profile = true;
	} else {
		$my_profile = false;
	}

	/**
	 * Filters whether or not current page is part of the profile for the logged-in user.
	 *
	 * @since 1.2.4
	 *
	 * @param bool $my_profile Whether or not current page is part of the profile for the logged-in user.
	 */
	return apply_filters( 'bp_is_my_profile', $my_profile );
}

/**
 * Is the current page a user page?
 *
 * Will return true anytime there is a displayed user.
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is a user page.
 */
function bp_is_user() {
	return (bool) bp_displayed_user_id();
}

/**
 * Is the current page a user custom front page?
 *
 * Will return true anytime there is a custom front page for the displayed user.
 *
 * @since 2.6.0
 *
 * @return bool True if the current page is a user custom front page.
 */
function bp_is_user_front() {
	return (bool) ( bp_is_user() && bp_is_current_component( 'front' ) );
}

/**
 * Is the current page a user's activity stream page?
 *
 * Eg http://example.com/members/joe/activity/ (or any subpages thereof).
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's activity stream page.
 */
function bp_is_user_activity() {
	return (bool) ( bp_is_user() && bp_is_activity_component() );
}

/**
 * Is the current page a user's Friends activity stream?
 *
 * Eg http://example.com/members/joe/friends/
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's Friends activity stream.
 */
function bp_is_user_friends_activity() {

	if ( ! bp_is_active( 'friends' ) ) {
		return false;
	}

	$slug = bp_get_friends_slug();

	if ( empty( $slug ) ) {
		$slug = 'friends';
	}

	if ( bp_is_user_activity() && bp_is_current_action( $slug ) ) {
		return true;
	}

	return false;
}

/**
 * Is the current page a user's Groups activity stream?
 *
 * Eg http://example.com/members/joe/groups/
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is a user's Groups activity stream.
 */
function bp_is_user_groups_activity() {

	if ( ! bp_is_active( 'groups' ) ) {
		return false;
	}

	$slug = ( bp_get_groups_slug() )
		? bp_get_groups_slug()
		: 'groups';

	if ( bp_is_user_activity() && bp_is_current_action( $slug ) ) {
		return true;
	}

	return false;
}

/**
 * Is the current page part of a user's extended profile?
 *
 * Eg http://example.com/members/joe/profile/ (or a subpage thereof).
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of a user's extended profile.
 */
function bp_is_user_profile() {
	return (bool) ( bp_is_profile_component() || bp_is_current_component( 'profile' ) );
}

/**
 * Is the current page part of a user's profile editing section?
 *
 * Eg http://example.com/members/joe/profile/edit/ (or a subpage thereof).
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is a user's profile edit page.
 */
function bp_is_user_profile_edit() {
	return (bool) ( bp_is_profile_component() && bp_is_current_action( 'edit' ) );
}

/**
 * Is the current page part of a user's profile avatar editing section?
 *
 * Eg http://example.com/members/joe/profile/change-avatar/ (or a subpage thereof).
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is the user's avatar edit page.
 */
function bp_is_user_change_avatar() {
	return (bool) ( bp_is_profile_component() && bp_is_current_action( 'change-avatar' ) );
}

/**
 * Is the current page the a user's change cover image profile page?
 *
 * Eg http://example.com/members/joe/profile/change-cover-image/ (or a subpage thereof).
 *
 * @since 2.4.0
 *
 * @return bool True if the current page is a user's profile edit cover image page.
 */
function bp_is_user_change_cover_image() {
	return (bool) ( bp_is_profile_component() && bp_is_current_action( 'change-cover-image' ) );
}

/**
 * Is the current page part of a user's Groups page?
 *
 * Eg http://example.com/members/joe/groups/ (or a subpage thereof).
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's Groups page.
 */
function bp_is_user_groups() {
	return (bool) ( bp_is_user() && bp_is_groups_component() );
}

/**
 * Is the current page part of a user's Blogs page?
 *
 * Eg http://example.com/members/joe/blogs/ (or a subpage thereof).
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's Blogs page.
 */
function bp_is_user_blogs() {
	return (bool) ( bp_is_user() && bp_is_blogs_component() );
}

/**
 * Is the current page a user's Recent Blog Posts page?
 *
 * Eg http://example.com/members/joe/blogs/recent-posts/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's Recent Blog Posts page.
 */
function bp_is_user_recent_posts() {
	return (bool) ( bp_is_user_blogs() && bp_is_current_action( 'recent-posts' ) );
}

/**
 * Is the current page a user's Recent Blog Comments page?
 *
 * Eg http://example.com/members/joe/blogs/recent-comments/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's Recent Blog Comments page.
 */
function bp_is_user_recent_commments() {
	return (bool) ( bp_is_user_blogs() && bp_is_current_action( 'recent-comments' ) );
}

/**
 * Is the current page a user's Friends page?
 *
 * Eg http://example.com/members/joe/blogs/friends/ (or a subpage thereof).
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's Friends page.
 */
function bp_is_user_friends() {
	return (bool) ( bp_is_user() && bp_is_friends_component() );
}

/**
 * Is the current page a user's Friend Requests page?
 *
 * Eg http://example.com/members/joe/friends/requests/.
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is a user's Friends Requests page.
 */
function bp_is_user_friend_requests() {
	return (bool) ( bp_is_user_friends() && bp_is_current_action( 'requests' ) );
}

/**
 * Is this a user's notifications page?
 *
 * Eg http://example.com/members/joe/notifications/ (or a subpage thereof).
 *
 * @since 1.9.0
 *
 * @return bool True if the current page is a user's Notifications page.
 */
function bp_is_user_notifications() {
	return (bool) ( bp_is_user() && bp_is_notifications_component() );
}

/**
 * Is this a user's settings page?
 *
 * Eg http://example.com/members/joe/settings/ (or a subpage thereof).
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is a user's Settings page.
 */
function bp_is_user_settings() {
	return (bool) ( bp_is_user() && bp_is_settings_component() );
}

/**
 * Is this a user's General Settings page?
 *
 * Eg http://example.com/members/joe/settings/general/.
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is a user's General Settings page.
 */
function bp_is_user_settings_general() {
	return (bool) ( bp_is_user_settings() && bp_is_current_action( 'general' ) );
}

/**
 * Is this a user's Notification Settings page?
 *
 * Eg http://example.com/members/joe/settings/notifications/.
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is a user's Notification Settings page.
 */
function bp_is_user_settings_notifications() {
	return (bool) ( bp_is_user_settings() && bp_is_current_action( 'notifications' ) );
}

/**
 * Is this a user's Account Deletion page?
 *
 * Eg http://example.com/members/joe/settings/delete-account/.
 *
 * @since 1.5.0
 *
 * @return bool True if the current page is a user's Delete Account page.
 */
function bp_is_user_settings_account_delete() {
	return (bool) ( bp_is_user_settings() && bp_is_current_action( 'delete-account' ) );
}

/**
 * Is this a user's profile settings?
 *
 * Eg http://example.com/members/joe/settings/profile/.
 *
 * @since 2.0.0
 *
 * @return bool True if the current page is a user's Profile Settings page.
 */
function bp_is_user_settings_profile() {
	return (bool) ( bp_is_user_settings() && bp_is_current_action( 'profile' ) );
}

/**
 * Is the current page a user's community invitations page?
 *
 * Eg http://example.com/members/cassie/invitations/ (or a subpage thereof).
 *
 * @since 8.0.0
 *
 * @return bool True if the current page is a user's community invitations page.
 */
function bp_is_user_members_invitations() {
	return (bool) ( bp_is_user() && bp_is_members_invitations_screen() );
}

/**
 * Is the current page a user's List Invites page?
 *
 * Eg http://example.com/members/cassie/invitations/list-invites/.
 *
 * @since 8.0.0
 *
 * @return bool True if the current page is a user's List Invites page.
 */
function bp_is_user_members_invitations_list() {
	return (bool) ( bp_is_user_members_invitations() && bp_is_current_action( 'list-invites' ) );
}

/**
 * Is the current page a user's Send Invites page?
 *
 * Eg http://example.com/members/cassie/invitations/send-invites/.
 *
 * @since 8.0.0
 *
 * @return bool True if the current page is a user's Send Invites page.
 */
function bp_is_user_members_invitations_send_screen() {
	return (bool) ( bp_is_user_members_invitations() && bp_is_current_action( 'send-invites' ) );
}

/** Groups ********************************************************************/

/**
 * Is the current page the groups directory?
 *
 * @since 2.0.0
 *
 * @return bool True if the current page is the groups directory.
 */
function bp_is_groups_directory() {
	$return = false;

	if ( bp_is_groups_component() && ! bp_is_group() ) {
		$return = ! bp_current_action() || ! empty( buddypress()->groups->current_directory_type );
	}

	return $return;
}

/**
 * Does the current page belong to a single group?
 *
 * Will return true for any subpage of a single group.
 *
 * @since 1.2.0
 *
 * @return bool True if the current page is part of a single group.
 */
function bp_is_group() {
	$retval = bp_is_active( 'groups' );

	if ( ! empty( $retval ) ) {
		$retval = bp_is_groups_component() && groups_get_current_group();
	}

	return (bool) $retval;
}

/**
 * Is the current page a single group's home page?
 *
 * URL will vary depending on which group tab is set to be the "home". By
 * default, it's the group's recent activity.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a single group's home page.
 */
function bp_is_group_home() {
	if ( bp_is_single_item() && bp_is_groups_component() && ( ! bp_current_action() || bp_is_current_action( 'home' ) ) ) {
		return true;
	}

	return false;
}

/**
 * Is the current page part of the group creation process?
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of the group creation process.
 */
function bp_is_group_create() {
	return (bool) ( bp_is_groups_component() && bp_is_current_action( 'create' ) );
}

/**
 * Is the current page part of a single group's admin screens?
 *
 * Eg http://example.com/groups/mygroup/admin/settings/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of a single group's admin.
 */
function bp_is_group_admin_page() {
	return (bool) ( bp_is_single_item() && bp_is_groups_component() && bp_is_current_action( 'admin' ) );
}

/**
 * Is the current page a group's activity page?
 *
 * @since 1.2.1
 *
 * @return bool True if the current page is a group's activity page.
 */
function bp_is_group_activity() {
	$retval = false;

	if ( bp_is_single_item() && bp_is_groups_component() && bp_is_current_action( 'activity' ) ) {
		$retval = true;
	}

	if ( bp_is_group_home() && bp_is_active( 'activity' ) && ! bp_is_group_custom_front() ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is the current page a group forum topic?
 *
 * @since 1.1.0
 * @since 3.0.0 Required for bbPress 2 integration.
 *
 * @return bool True if the current page is part of a group forum topic.
 */
function bp_is_group_forum_topic() {
	return (bool) ( bp_is_single_item() && bp_is_groups_component() && bp_is_current_action( 'forum' ) && bp_is_action_variable( 'topic', 0 ) );
}

/**
 * Is the current page a group forum topic edit page?
 *
 * @since 1.2.0
 * @since 3.0.0 Required for bbPress 2 integration.
 *
 * @return bool True if the current page is part of a group forum topic edit page.
 */
function bp_is_group_forum_topic_edit() {
	return (bool) ( bp_is_single_item() && bp_is_groups_component() && bp_is_current_action( 'forum' ) && bp_is_action_variable( 'topic', 0 ) && bp_is_action_variable( 'edit', 2 ) );
}

/**
 * Is the current page a group's Members page?
 *
 * Eg http://example.com/groups/mygroup/members/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is part of a group's Members page.
 */
function bp_is_group_members() {
	$retval = false;

	if ( bp_is_single_item() && bp_is_groups_component() && bp_is_current_action( 'members' ) ) {
		$retval = true;
	}

	if ( bp_is_group_home() && ! bp_is_active( 'activity' ) && ! bp_is_group_custom_front() ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Is the current page a group's Invites page?
 *
 * Eg http://example.com/groups/mygroup/send-invites/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a group's Send Invites page.
 */
function bp_is_group_invites() {
	return (bool) ( bp_is_groups_component() && bp_is_current_action( 'send-invites' ) );
}

/**
 * Is the current page a group's Request Membership page?
 *
 * Eg http://example.com/groups/mygroup/request-membership/.
 *
 * @since 1.2.0
 *
 * @return bool True if the current page is a group's Request Membership page.
 */
function bp_is_group_membership_request() {
	return (bool) ( bp_is_groups_component() && bp_is_current_action( 'request-membership' ) );
}

/**
 * Is the current page a leave group attempt?
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a Leave Group attempt.
 */
function bp_is_group_leave() {
	return (bool) ( bp_is_groups_component() && bp_is_single_item() && bp_is_current_action( 'leave-group' ) );
}

/**
 * Is the current page part of a single group?
 *
 * Not currently used by BuddyPress.
 *
 * @todo How is this functionally different from bp_is_group()?
 *
 * @return bool True if the current page is part of a single group.
 */
function bp_is_group_single() {
	return (bool) ( bp_is_groups_component() && bp_is_single_item() );
}

/**
 * Is the current group page a custom front?
 *
 * @since 2.4.0
 *
 * @return bool True if the current group page is a custom front.
 */
function bp_is_group_custom_front() {
	$bp = buddypress();
	return (bool) bp_is_group_home() && ! empty( $bp->groups->current_group->front_template );
}

/**
 * Is the current page the Create a Blog page?
 *
 * Eg http://example.com/sites/create/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is the Create a Blog page.
 */
function bp_is_create_blog() {
	return (bool) ( bp_is_blogs_component() && bp_is_current_action( 'create' ) );
}

/**
 * Is the current page the blogs directory ?
 *
 * @since 2.0.0
 *
 * @return bool True if the current page is the blogs directory.
 */
function bp_is_blogs_directory() {
	if ( is_multisite() && bp_is_blogs_component() && ! bp_current_action() ) {
		return true;
	}

	return false;
}

/** Messages ******************************************************************/

/**
 * Is the current page part of a user's Messages pages?
 *
 * Eg http://example.com/members/joe/messages/ (or a subpage thereof).
 *
 * @since 1.2.0
 *
 * @return bool True if the current page is part of a user's Messages pages.
 */
function bp_is_user_messages() {
	return (bool) ( bp_is_user() && bp_is_messages_component() );
}

/**
 * Is the current page a user's Messages Inbox?
 *
 * Eg http://example.com/members/joe/messages/inbox/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's Messages Inbox.
 */
function bp_is_messages_inbox() {
	if ( bp_is_user_messages() && ( ! bp_current_action() || bp_is_current_action( 'inbox' ) ) ) {
		return true;
	}

	return false;
}

/**
 * Is the current page a user's Messages Sentbox?
 *
 * Eg http://example.com/members/joe/messages/sentbox/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's Messages Sentbox.
 */
function bp_is_messages_sentbox() {
	return (bool) ( bp_is_user_messages() && bp_is_current_action( 'sentbox' ) );
}

/**
 * Is the current page a user's Messages Compose screen??
 *
 * Eg http://example.com/members/joe/messages/compose/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is a user's Messages Compose screen.
 */
function bp_is_messages_compose_screen() {
	return (bool) ( bp_is_user_messages() && bp_is_current_action( 'compose' ) );
}

/**
 * Is the current page the Notices screen?
 *
 * Eg http://example.com/members/joe/messages/notices/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is the Notices screen.
 */
function bp_is_notices() {
	return (bool) ( bp_is_user_messages() && bp_is_current_action( 'notices' ) );
}

/**
 * Is the current page a single Messages conversation thread?
 *
 * @since 1.6.0
 *
 * @return bool True if the current page a single Messages conversation thread?
 */
function bp_is_messages_conversation() {
	return (bool) ( bp_is_user_messages() && ( bp_is_current_action( 'view' ) ) );
}

/**
 * Not currently used by BuddyPress.
 *
 * @param string $component Current component to check for.
 * @param string $callback  Callback to invoke.
 * @return bool
 */
function bp_is_single( $component, $callback ) {
	return (bool) ( bp_is_current_component( $component ) && ( true === call_user_func( $callback ) ) );
}

/** Registration **************************************************************/

/**
 * Is the current page the Activate page?
 *
 * Eg http://example.com/activate/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is the Activate page.
 */
function bp_is_activation_page() {
	return (bool) bp_is_current_component( 'activate' );
}

/**
 * Is the current page the Register page?
 *
 * Eg http://example.com/register/.
 *
 * @since 1.1.0
 *
 * @return bool True if the current page is the Register page.
 */
function bp_is_register_page() {
	return (bool) bp_is_current_component( 'register' );
}

/**
 * Get the title parts of the BuddyPress displayed page
 *
 * @since 2.4.3
 *
 * @param string $seplocation Location for the separator.
 * @return array the title parts
 */
function bp_get_title_parts( $seplocation = 'right' ) {
	$bp = buddypress();

	// Defaults to an empty array.
	$bp_title_parts = array();

	// If this is not a BP page, return the empty array.
	if ( bp_is_blog_page() ) {
		return $bp_title_parts;
	}

	// If this is a 404, return the empty array.
	if ( is_404() ) {
		return $bp_title_parts;
	}

	// If this is the front page of the site, return the empty array.
	if ( is_front_page() || is_home() ) {
		return $bp_title_parts;
	}

	// Return the empty array if not a BuddyPress page.
	if ( ! is_buddypress() ) {
		return $bp_title_parts;
	}

	// Now we can build the BP Title Parts.
	// Is there a displayed user, and do they have a name?
	$displayed_user_name = bp_get_displayed_user_fullname();

	// Displayed user.
	if ( ! empty( $displayed_user_name ) && ! is_404() ) {

		// Get the component's ID to try and get its name.
		$component_id = $component_name = bp_current_component();

		// Set empty subnav name.
		$component_subnav_name = '';

		if ( ! empty( $bp->members->nav ) ) {
			$primary_nav_item = (array) $bp->members->nav->get_primary( array( 'slug' => $component_id ), false );
			$primary_nav_item = reset( $primary_nav_item );
		}

		// Use the component nav name.
		if ( ! empty( $primary_nav_item->name ) ) {
			$component_name = _bp_strip_spans_from_title( $primary_nav_item->name );

		// Fall back on the component ID.
		} elseif ( ! empty( $bp->{$component_id}->id ) ) {
			$component_name = ucwords( $bp->{$component_id}->id );
		}

		if ( ! empty( $bp->members->nav ) ) {
			$secondary_nav_item = $bp->members->nav->get_secondary( array(
				'parent_slug' => $component_id,
				'slug'        => bp_current_action()
			), false );

			if ( $secondary_nav_item ) {
				$secondary_nav_item = reset( $secondary_nav_item );
			}
		}

		// Append action name if we're on a member component sub-page.
		if ( ! empty( $secondary_nav_item->name ) && ! empty( $bp->canonical_stack['action'] ) ) {
			$component_subnav_name = $secondary_nav_item->name;
		}

		// If on the user profile's landing page, just use the fullname.
		if ( bp_is_current_component( $bp->default_component ) && ( bp_get_requested_url() === bp_displayed_user_url() ) ) {
			$bp_title_parts[] = $displayed_user_name;

		// Use component name on member pages.
		} else {
			$bp_title_parts = array_merge( $bp_title_parts, array_map( 'wp_strip_all_tags', array(
				$displayed_user_name,
				$component_name,
			) ) );

			// If we have a subnav name, add it separately for localization.
			if ( ! empty( $component_subnav_name ) ) {
				$bp_title_parts[] = wp_strip_all_tags( $component_subnav_name );
			}
		}

	// A single item from a component other than Members.
	} elseif ( bp_is_single_item() ) {
		$component_id = bp_current_component();

		if ( ! empty( $bp->{$component_id}->nav ) ) {
			$secondary_nav_item = $bp->{$component_id}->nav->get_secondary( array(
				'parent_slug' => bp_current_item(),
				'slug'        => bp_current_action()
			), false );

			if ( $secondary_nav_item ) {
				$secondary_nav_item = reset( $secondary_nav_item );
			}
		}

		$single_item_subnav = '';

		if ( ! empty( $secondary_nav_item->name ) ) {
			$single_item_subnav = $secondary_nav_item->name;
		}

		$bp_title_parts = array( $bp->bp_options_title, $single_item_subnav );

	// An index or directory.
	} elseif ( bp_is_directory() ) {
		$current_component = bp_current_component();

		// No current component (when does this happen?).
		$bp_title_parts = array( _x( 'Directory', 'component directory title', 'buddypress' ) );

		if ( ! empty( $current_component ) ) {
			$bp_title_parts = array( bp_get_directory_title( $current_component ) );
		}

	// Sign up page.
	} elseif ( bp_is_register_page() ) {
		if ( bp_get_membership_requests_required() ) {
			$bp_title_parts = array( __( 'Request Membership', 'buddypress' ) );
		} else {
			$bp_title_parts = array( __( 'Create an Account', 'buddypress' ) );
		}

	// Activation page.
	} elseif ( bp_is_activation_page() ) {
		$bp_title_parts = array( __( 'Activate Your Account', 'buddypress' ) );

	// Group creation page.
	} elseif ( bp_is_group_create() ) {
		$bp_title_parts = array( __( 'Create a Group', 'buddypress' ) );

	// Blog creation page.
	} elseif ( bp_is_create_blog() ) {
		$bp_title_parts = array( __( 'Create a Site', 'buddypress' ) );
	}

	// Strip spans.
	$bp_title_parts = array_map( '_bp_strip_spans_from_title', $bp_title_parts );

	// Sep on right, so reverse the order.
	if ( 'right' === $seplocation ) {
		$bp_title_parts = array_reverse( $bp_title_parts );
	}

	/**
	 * Filter BuddyPress title parts before joining.
	 *
	 * @since 2.4.3
	 *
	 * @param array $bp_title_parts Current BuddyPress title parts.
	 * @return array
	 */
	return (array) apply_filters( 'bp_get_title_parts', $bp_title_parts );
}

/**
 * Customize the body class, according to the currently displayed BP content.
 *
 * @since 1.1.0
 */
function bp_the_body_class() {
	echo implode( ' ', array_map( 'sanitize_html_class', bp_get_the_body_class() ) );
}
	/**
	 * Customize the body class, according to the currently displayed BP content.
	 *
	 * Uses the above is_() functions to output a body class for each scenario.
	 *
	 * @since 1.1.0
	 *
	 * @param array      $wp_classes     The body classes coming from WP.
	 * @param array|bool $custom_classes Classes that were passed to get_body_class().
	 * @return array $classes The BP-adjusted body classes.
	 */
	function bp_get_the_body_class( $wp_classes = array(), $custom_classes = false ) {

		$bp_classes = array();

		/* Pages *************************************************************/

		if ( is_front_page() ) {
			$bp_classes[] = 'home-page';
		}

		if ( bp_is_directory() ) {
			$bp_classes[] = 'directory';
		}

		if ( bp_is_single_item() ) {
			$bp_classes[] = 'single-item';
		}

		/* Components ********************************************************/

		if ( ! bp_is_blog_page() ) {
			if ( bp_is_user_profile() )  {
				$bp_classes[] = 'xprofile';
			}

			if ( bp_is_activity_component() ) {
				$bp_classes[] = 'activity';
			}

			if ( bp_is_blogs_component() ) {
				$bp_classes[] = 'blogs';
			}

			if ( bp_is_messages_component() ) {
				$bp_classes[] = 'messages';
			}

			if ( bp_is_friends_component() ) {
				$bp_classes[] = 'friends';
			}

			if ( bp_is_groups_component() ) {
				$bp_classes[] = 'groups';
			}

			if ( bp_is_settings_component()  ) {
				$bp_classes[] = 'settings';
			}
		}

		/* User **************************************************************/

		if ( bp_is_user() ) {
			$bp_classes[] = 'bp-user';

			// Add current user member types.
			if ( $member_types = bp_get_member_type( bp_displayed_user_id(), false ) ) {
				foreach ( $member_types as $member_type ) {
					$bp_classes[] = sprintf( 'member-type-%s', esc_attr( $member_type ) );
				}
			}
		}

		if ( ! bp_is_directory() ) {
			if ( bp_is_user_blogs() ) {
				$bp_classes[] = 'my-blogs';
			}

			if ( bp_is_user_groups() ) {
				$bp_classes[] = 'my-groups';
			}

			if ( bp_is_user_activity() ) {
				$bp_classes[] = 'my-activity';
			}
		} else {
			if ( bp_get_current_member_type() || ( bp_is_groups_directory() && bp_get_current_group_directory_type() ) ) {
				$bp_classes[] = 'type';
			}
		}

		if ( bp_is_my_profile() ) {
			$bp_classes[] = 'my-account';
		}

		if ( bp_is_user_profile() ) {
			$bp_classes[] = 'my-profile';
		}

		if ( bp_is_user_friends() ) {
			$bp_classes[] = 'my-friends';
		}

		if ( bp_is_user_messages() ) {
			$bp_classes[] = 'my-messages';
		}

		if ( bp_is_user_recent_commments() ) {
			$bp_classes[] = 'recent-comments';
		}

		if ( bp_is_user_recent_posts() ) {
			$bp_classes[] = 'recent-posts';
		}

		if ( bp_is_user_change_avatar() ) {
			$bp_classes[] = 'change-avatar';
		}

		if ( bp_is_user_profile_edit() ) {
			$bp_classes[] = 'profile-edit';
		}

		if ( bp_is_user_friends_activity() ) {
			$bp_classes[] = 'friends-activity';
		}

		if ( bp_is_user_groups_activity() ) {
			$bp_classes[] = 'groups-activity';
		}

		/* Messages **********************************************************/

		if ( bp_is_messages_inbox() ) {
			$bp_classes[] = 'inbox';
		}

		if ( bp_is_messages_sentbox() ) {
			$bp_classes[] = 'sentbox';
		}

		if ( bp_is_messages_compose_screen() ) {
			$bp_classes[] = 'compose';
		}

		if ( bp_is_notices() ) {
			$bp_classes[] = 'notices';
		}

		if ( bp_is_user_friend_requests() ) {
			$bp_classes[] = 'friend-requests';
		}

		if ( bp_is_create_blog() ) {
			$bp_classes[] = 'create-blog';
		}

		/* Groups ************************************************************/

		if ( bp_is_group() ) {
			$bp_classes[] = 'group-' . groups_get_current_group()->slug;

			// Add current group types.
			if ( $group_types = bp_groups_get_group_type( bp_get_current_group_id(), false ) ) {
				foreach ( $group_types as $group_type ) {
					$bp_classes[] = sprintf( 'group-type-%s', esc_attr( $group_type ) );
				}
			}
		}

		if ( bp_is_group_leave() ) {
			$bp_classes[] = 'leave-group';
		}

		if ( bp_is_group_invites() ) {
			$bp_classes[] = 'group-invites';
		}

		if ( bp_is_group_members() ) {
			$bp_classes[] = 'group-members';
		}

		if ( bp_is_group_admin_page() ) {
			$bp_classes[] = 'group-admin';
			$bp_classes[] = bp_get_group_current_admin_tab();
		}

		if ( bp_is_group_create() ) {
			$bp_classes[] = 'group-create';
			$bp_classes[] = bp_get_groups_current_create_step();
		}

		if ( bp_is_group_home() ) {
			$bp_classes[] = 'group-home';
		}

		if ( bp_is_single_activity() ) {
			$bp_classes[] = 'activity-permalink';
		}

		/* Registration ******************************************************/

		if ( bp_is_register_page() ) {
			$bp_classes[] = 'registration';
		}

		if ( bp_is_activation_page() ) {
			$bp_classes[] = 'activation';
		}

		/* Current Component & Action ****************************************/

		if ( ! bp_is_blog_page() ) {
			$bp_classes[] = bp_current_component();
			$bp_classes[] = bp_current_action();
		}

		/* Clean up ***********************************************************/

		// Add BuddyPress class if we are within a BuddyPress page.
		if ( ! bp_is_blog_page() ) {
			$bp_classes[] = 'buddypress';
		}

		// Add the theme name/id to the body classes
		$bp_classes[] = 'bp-' . bp_get_theme_compat_id();

		// Merge WP classes with BuddyPress classes and remove any duplicates.
		$classes = array_unique( array_merge( (array) $bp_classes, (array) $wp_classes ) );

		/**
		 * Filters the BuddyPress classes to be added to body_class()
		 *
		 * @since 1.1.0
		 *
		 * @param array $classes        Array of body classes to add.
		 * @param array $bp_classes     Array of BuddyPress-based classes.
		 * @param array $wp_classes     Array of WordPress-based classes.
		 * @param array $custom_classes Array of classes that were passed to get_body_class().
		 */
		return apply_filters( 'bp_get_the_body_class', $classes, $bp_classes, $wp_classes, $custom_classes );
	}
	add_filter( 'body_class', 'bp_get_the_body_class', 10, 2 );

/**
 * Customizes the post CSS class according to BuddyPress content.
 *
 * Hooked to the 'post_class' filter.
 *
 * @since 2.1.0
 *
 * @param array $wp_classes The post classes coming from WordPress.
 * @return array
 */
function bp_get_the_post_class( $wp_classes = array() ) {
	// Don't do anything if we're not on a BP page.
	if ( ! is_buddypress() ) {
		return $wp_classes;
	}

	$bp_classes = array();

	if ( bp_is_user() || bp_is_single_activity() ) {
		$bp_classes[] = 'bp_members';

	} elseif ( bp_is_group() ) {
		$bp_classes[] = 'bp_group';

	} elseif ( bp_is_activity_component() ) {
		$bp_classes[] = 'bp_activity';

	} elseif ( bp_is_blogs_component() ) {
		$bp_classes[] = 'bp_blogs';

	} elseif ( bp_is_register_page() ) {
		$bp_classes[] = 'bp_register';

	} elseif ( bp_is_activation_page() ) {
		$bp_classes[] = 'bp_activate';
	}

	if ( empty( $bp_classes ) ) {
		return $wp_classes;
	}

	// Emulate post type css class.
	foreach ( $bp_classes as $bp_class ) {
		$bp_classes[] = "type-{$bp_class}";
	}

	// Okay let's merge!
	return array_unique( array_merge( $bp_classes, $wp_classes ) );
}
add_filter( 'post_class', 'bp_get_the_post_class' );

/**
 * Sort BuddyPress nav menu items by their position property.
 *
 * This is an internal convenience function and it will probably be removed in
 * a later release. Do not use.
 *
 * @access private
 * @since 1.7.0
 *
 * @param array $a First item.
 * @param array $b Second item.
 * @return int Returns an integer less than, equal to, or greater than zero if
 *             the first argument is considered to be respectively less than,
 *             equal to, or greater than the second.
 */
function _bp_nav_menu_sort( $a, $b ) {
	if ( $a['position'] == $b['position'] ) {
		return 0;
	} elseif ( $a['position'] < $b['position'] ) {
		return -1;
	} else {
		return 1;
	}
}

/**
 * Get the items registered in the primary and secondary BuddyPress navigation menus.
 *
 * @since 1.7.0
 * @since 2.6.0 Introduced the `$component` parameter.
 *
 * @param string $component Optional. Component whose nav items are being fetched.
 * @return array A multidimensional array of all navigation items.
 */
function bp_get_nav_menu_items( $component = 'members' ) {
	$bp    = buddypress();
	$menus = array();

	if ( ! isset( $bp->{$component}->nav ) ) {
		return $menus;
	}

	// Get the item nav and build the menus.
	foreach ( $bp->{$component}->nav->get_item_nav() as $nav_menu ) {
		// Get the correct menu link. See https://buddypress.trac.wordpress.org/ticket/4624.
		$link = $nav_menu->link;
		if ( bp_loggedin_user_url() ) {
			$link = str_replace( bp_loggedin_user_url(), bp_displayed_user_url(), $nav_menu->link );
		}

		// Add this menu.
		$menu         = new stdClass;
		$menu->class  = array( 'menu-parent' );
		$menu->css_id = $nav_menu->css_id;
		$menu->link   = $link;
		$menu->name   = $nav_menu->name;
		$menu->parent = 0;

		if ( ! empty( $nav_menu->children ) ) {
			$submenus = array();

			foreach ( $nav_menu->children as $sub_menu ) {
				$submenu = new stdClass;
				$submenu->class  = array( 'menu-child' );
				$submenu->css_id = $sub_menu->css_id;
				$submenu->link   = $sub_menu->link;
				$submenu->name   = $sub_menu->name;
				$submenu->parent = $nav_menu->slug;

				// If we're viewing this item's screen, record that we need to mark its parent menu to be selected.
				if ( bp_is_current_action( $sub_menu->slug ) && bp_is_current_component( $nav_menu->slug ) ) {
					$menu->class[]    = 'current-menu-parent';
					$submenu->class[] = 'current-menu-item';
				}

				$submenus[] = $submenu;
			}
		}

		$menus[] = $menu;

		if ( ! empty( $submenus ) ) {
			$menus = array_merge( $menus, $submenus );
		}
	}

	/**
	 * Filters the items registered in the primary and secondary BuddyPress navigation menus.
	 *
	 * @since 1.7.0
	 *
	 * @param array $menus Array of items registered in the primary and secondary BuddyPress navigation.
	 */
	return apply_filters( 'bp_get_nav_menu_items', $menus );
}

/**
 * Display a navigation menu.
 *
 * @since 1.7.0
 *
 * @param string|array $args {
 *     An array of optional arguments.
 *
 *     @type string $after           Text after the link text. Default: ''.
 *     @type string $before          Text before the link text. Default: ''.
 *     @type string $container       The name of the element to wrap the navigation
 *                                   with. 'div' or 'nav'. Default: 'div'.
 *     @type string $container_class The class that is applied to the container.
 *                                   Default: 'menu-bp-container'.
 *     @type string $container_id    The ID that is applied to the container.
 *                                   Default: ''.
 *     @type int    $depth           How many levels of the hierarchy are to be included.
 *                                   0 means all. Default: 0.
 *     @type bool   $echo            True to echo the menu, false to return it.
 *                                   Default: true.
 *     @type bool   $fallback_cb     If the menu doesn't exist, should a callback
 *                                   function be fired? Default: false (no fallback).
 *     @type string $items_wrap      How the list items should be wrapped. Should be
 *                                   in the form of a printf()-friendly string, using numbered
 *                                   placeholders. Default: '<ul id="%1$s" class="%2$s">%3$s</ul>'.
 *     @type string $link_after      Text after the link. Default: ''.
 *     @type string $link_before     Text before the link. Default: ''.
 *     @type string $menu_class      CSS class to use for the <ul> element which
 *                                   forms the menu. Default: 'menu'.
 *     @type string $menu_id         The ID that is applied to the <ul> element which
 *                                   forms the menu. Default: 'menu-bp', incremented.
 *     @type string $walker          Allows a custom walker class to be specified.
 *                                   Default: 'BP_Walker_Nav_Menu'.
 * }
 * @return string|null If $echo is false, returns a string containing the nav
 *                     menu markup.
 */
function bp_nav_menu( $args = array() ) {
	static $menu_id_slugs = array();

	$defaults = array(
		'after'           => '',
		'before'          => '',
		'container'       => 'div',
		'container_class' => '',
		'container_id'    => '',
		'depth'           => 0,
		'echo'            => true,
		'fallback_cb'     => false,
		'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		'link_after'      => '',
		'link_before'     => '',
		'menu_class'      => 'menu',
		'menu_id'         => '',
		'walker'          => '',
	);

	$args = bp_parse_args(
		$args,
		$defaults
	);

	/**
	 * Filters the parsed bp_nav_menu arguments.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args Array of parsed arguments.
	 */
	$args = apply_filters( 'bp_nav_menu_args', $args );
	$args = (object) $args;

	$items = $nav_menu = '';
	$show_container = false;

	// Create custom walker if one wasn't set.
	if ( empty( $args->walker ) ) {
		$args->walker = new BP_Walker_Nav_Menu;
	}

	// Sanitize values for class and ID.
	$args->container_class = sanitize_html_class( $args->container_class );
	$args->container_id    = sanitize_html_class( $args->container_id );

	// Whether to wrap the ul, and what to wrap it with.
	if ( $args->container ) {

		/**
		 * Filters the allowed tags for the wp_nav_menu_container.
		 *
		 * @since 1.7.0
		 *
		 * @param array $value Array of allowed tags. Default 'div' and 'nav'.
		 */
		$allowed_tags = apply_filters( 'wp_nav_menu_container_allowedtags', array( 'div', 'nav', ) );

		if ( in_array( $args->container, $allowed_tags ) ) {
			$show_container = true;

			$class     = $args->container_class ? ' class="' . esc_attr( $args->container_class ) . '"' : ' class="menu-bp-container"';
			$id        = $args->container_id    ? ' id="' . esc_attr( $args->container_id ) . '"'       : '';
			$nav_menu .= '<' . $args->container . $id . $class . '>';
		}
	}

	/**
	 * Filters the BuddyPress menu objects.
	 *
	 * @since 1.7.0
	 *
	 * @param array $value Array of nav menu objects.
	 * @param array $args  Array of arguments for the menu.
	 */
	$menu_items = apply_filters( 'bp_nav_menu_objects', bp_get_nav_menu_items(), $args );
	$items      = walk_nav_menu_tree( $menu_items, $args->depth, $args );
	unset( $menu_items );

	// Set the ID that is applied to the ul element which forms the menu.
	if ( ! empty( $args->menu_id ) ) {
		$wrap_id = $args->menu_id;

	} else {
		$wrap_id = 'menu-bp';

		// If a specific ID wasn't requested, and there are multiple menus on the same screen, make sure the autogenerated ID is unique.
		while ( in_array( $wrap_id, $menu_id_slugs ) ) {
			if ( preg_match( '#-(\d+)$#', $wrap_id, $matches ) ) {
				$wrap_id = preg_replace('#-(\d+)$#', '-' . ++$matches[1], $wrap_id );
			} else {
				$wrap_id = $wrap_id . '-1';
			}
		}
	}
	$menu_id_slugs[] = $wrap_id;

	/**
	 * Filters the BuddyPress menu items.
	 *
	 * Allow plugins to hook into the menu to add their own <li>'s
	 *
	 * @since 1.7.0
	 *
	 * @param array $items Array of nav menu items.
	 * @param array $args  Array of arguments for the menu.
	 */
	$items = apply_filters( 'bp_nav_menu_items', $items, $args );

	// Build the output.
	$wrap_class  = $args->menu_class ? $args->menu_class : '';
	$nav_menu   .= sprintf( $args->items_wrap, esc_attr( $wrap_id ), esc_attr( $wrap_class ), $items );
	unset( $items );

	// If we've wrapped the ul, close it.
	if ( ! empty( $show_container ) ) {
		$nav_menu .= '</' . $args->container . '>';
	}

	/**
	 * Filters the final BuddyPress menu output.
	 *
	 * @since 1.7.0
	 *
	 * @param string $nav_menu Final nav menu output.
	 * @param array  $args     Array of arguments for the menu.
	 */
	$nav_menu = apply_filters( 'bp_nav_menu', $nav_menu, $args );

	if ( ! empty( $args->echo ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput
		echo $nav_menu;
	} else {
		return $nav_menu;
	}
}

/**
 * Prints the Recipient Salutation.
 *
 * @since 2.5.0
 *
 * @param array $settings Email Settings.
 */
function bp_email_the_salutation( $settings = array() ) {
	echo esc_html( bp_email_get_salutation( $settings ) );
}

	/**
	 * Gets the Recipient Salutation.
	 *
	 * @since 2.5.0
	 * @since 8.0.0 Checks current BP Email type schema to eventually use the unnamed salutation.
	 *
	 * @param array $settings Email Settings.
	 * @return string The Recipient Salutation.
	 */
	function bp_email_get_salutation( $settings = array() ) {
		$email_type = bp_email_get_type();
		$salutation  = '';

		if ( $email_type ) {
			$types_schema = bp_email_get_type_schema( 'named_salutation' );

			if ( isset( $types_schema[ $email_type ] ) && false === $types_schema[ $email_type ] ) {
				/**
				 * Filters The Recipient Unnamed Salutation inside the Email Template.
				 *
				 * @since 8.0.0
				 *
				 * @param string $value    The Recipient Salutation.
				 * @param array  $settings Email Settings.
				 */
				$salutation = apply_filters(
					'bp_email_get_unnamed_salutation',
					_x( 'Hi,', 'Unnamed recipient salutation', 'buddypress' ),
					$settings
				);
			}
		}

		// Named salutations are default.
		if ( ! $salutation ) {
			$token = '{{recipient.name}}';

			/**
			 * Filters The Recipient Named Salutation inside the Email Template.
			 *
			 * @since 2.5.0
			 *
			 * @param string $value    The Recipient Salutation.
			 * @param array  $settings Email Settings.
			 * @param string $token    The Recipient token.
			 */
			$salutation = apply_filters(
				'bp_email_get_salutation',
				sprintf(
					/* translators: %s: the email token for the recipient name */
					_x( 'Hi %s,', 'Named recipient salutation', 'buddypress' ),
					$token
				),
				$settings,
				$token
			);
		}

		return $salutation;
	}

/**
 * Outputs the BP Email's template footer.
 *
 * @since 12.1.0
 */
function bp_email_footer() {
	// `the_block_template_skip_link()` was deprecated in WP 6.4.0.
	if ( bp_is_running_wp( '6.4.0', '>=' ) && has_action( 'wp_footer', 'the_block_template_skip_link' ) ) {
		remove_action( 'wp_footer', 'the_block_template_skip_link' );
	}

	wp_footer();
}

/**
 * Checks if a Widget/Block is active.
 *
 * @since 9.0.0
 *
 * @param string $block_name     The Block name to check (eg: 'bp/sitewide-notices'). Optional.
 * @param string $widget_id_base The Widget ID base to check (eg: 'bp_messages_sitewide_notices_widget' ). Optional.
 * @return bool True if the Widget/Block is active. False otherwise.
 */
function bp_is_widget_block_active( $block_name = '', $widget_id_base = '' ) {
	$is_active = array(
		'widget' => false,
		'block'  => false,
	);

	if ( $block_name ) {
		$widget_blocks = get_option( 'widget_block', array() );
		$sidebars      = wp_get_sidebars_widgets();

		if ( ! $widget_blocks || ! $sidebars ) {
			return false;
		}

		// Neutralize inactive sidebar.
		unset( $sidebars['wp_inactive_widgets'] );

		$widgets_content = '';
		foreach ( $widget_blocks as $key => $widget_block ) {
			$widget_block_reference = 'block-' . $key;

			if ( ! isset( $widget_block['content'] ) || ! $widget_block['content'] ) {
				continue;
			}

			foreach ( $sidebars as $sidebar ) {
				if ( is_array( $sidebar ) && in_array( $widget_block_reference, $sidebar, true ) ) {
					$widgets_content .= $widget_block['content'] . "\n";
				}
			}
		}

		$is_active['block'] = has_block( $block_name, $widgets_content );
	}

	if ( $widget_id_base ) {
		$is_active['widget'] = is_active_widget( false, false, $widget_id_base, true );
	}

	return 0 !== count( array_filter( $is_active ) );
}
