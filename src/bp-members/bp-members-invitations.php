<?php
/**
 * BuddyPress Membersip Invitations
 *
 * @package BuddyPress
 * @subpackage Members
 * @since 8.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * When a user joins the network via an invitation, skip sending the activation email.
 *
 * @since 8.0.0
 *
 * @param bool   $send       Whether or not to send the activation key.
 * @param int    $user_id    User ID to send activation key to.
 * @param string $user_email User email to send activation key to.
 *
 * @return bool Whether or not to send the activation key.
 */
function bp_members_invitations_cancel_activation_email( $send, $user_id = 0, $user_email = '' ) {
	$invite = bp_members_invitations_get_invites(
		array(
			'invitee_email' => $user_email,
			'invite_sent'   => 'sent',
		)
	);

	if ( $invite ) {
		$send = false;
	}

	return $send;
}
add_filter( 'bp_core_signup_send_activation_key', 'bp_members_invitations_cancel_activation_email', 10, 3 );

/**
 * When a user joins the network via an invitation:
 * - mark all invitations and requests as accepted
 * - activate the user upon signup
 *
 * @since 8.0.0
 *
 * @param bool|WP_Error $user_id True on success, WP_Error on failure.
 */
function bp_members_invitations_complete_signup( $user_id ) {

	// Check to see if this signup is the result of a valid invitation.
	$invite = bp_get_members_invitation_from_request();
	if ( ! $invite->id ) {
		return;
	}

	// User has already verified their email by responding to the invitation, so we can activate.
	$signup = bp_members_get_signup_by( 'user_email', $invite->invitee_email );
	$key = false;
	if ( ! empty( $signup->activation_key ) ) {
		$key = $signup->activation_key;
	}

	if ( $key ) {
		$redirect = bp_get_activation_page();

		/**
		 * Filters the activation signup.
		 *
		 * @since 1.1.0
		 *
		 * @param bool|int $value Value returned by activation.
		 *                        Integer on success, boolean on failure.
		 */
		$user = apply_filters( 'bp_core_activate_account', bp_core_activate_signup( $key ) );

		// Accept the invitation now that the user has been created.
		$invites_class = new BP_Members_Invitation_Manager();
		$args          = array(
			'id' => $invite->id,
		);
		$invites_class->accept_invitation( $args );

		// If there were errors, add a message and redirect.
		if ( ! empty( $user->errors ) ) {
			/**
			 * Filter here to redirect the User to a different URL than the activation page.
			 *
			 * @since 10.0.0
			 *
			 * @param string   $redirect The URL to use to redirect the user.
			 * @param WP_Error $user     The WP Error object.
			 */
			$redirect = apply_filters( 'bp_members_invitations_activation_errored_redirect', $redirect, $user );

			bp_core_add_message( $user->get_error_message(), 'error' );
			bp_core_redirect( $redirect );
		}

		/**
		 * Filter here to redirect the User to a different URL than the activation page.
		 *
		 * @since 10.0.0
		 *
		 * @param string $redirect The URL to use to redirect the user.
		 */
		$redirect = apply_filters( 'bp_members_invitations_activation_successed_redirect', $redirect );

		bp_core_add_message( __( 'Your account is now active!', 'buddypress' ) );
		bp_core_redirect( add_query_arg( 'activated', '1', $redirect ) );
	}
}
add_action( 'bp_core_signup_user', 'bp_members_invitations_complete_signup' );

/**
 * Delete site membership invitations when an opt-out request is saved.
 *
 * @since 8.0.0
 *
 * @param BP_Optout $optout Characteristics of the opt-out just saved.
 */
function bp_members_invitations_delete_optedout_invites( $optout ) {
	bp_members_invitations_delete_invites(
		array(
			'invitee_email' => $optout->email_address,
		)
	);
}
add_action( 'bp_optout_after_save', 'bp_members_invitations_delete_optedout_invites' );

/**
 * If a user submits a site membership request, but there's a
 * sent invitation to her, bypass the manual approval of the request.
 *
 * @since 10.0.0
 *
 * @param bool  $send    Whether or not this membership request should be approved
 *                       immediately and the activation email sent.
 *                       Default is `false` meaning that the request should be
 *                       manually approved by a site admin.
 * @param array $details The details of the request.
 */
function bp_members_invitations_maybe_bypass_request_approval( $send, $details ) {
	if ( ! bp_get_members_invitations_allowed() ) {
		return $send;
	}

	// We'll need the prospective user's email address.
	if ( empty( $details['user_email'] ) ) {
		return $send;
	}

	$invites = bp_members_invitations_get_invites(
		array(
			'invitee_email' => $details['user_email'],
			'invite_sent'   => 'sent'
		)
	);

	// If pending invitations exist, but we're not currently accepting an invite, send the verification mail.
	if ( $invites ) {
		// Is the current request actually a response to an invitation?
		$maybe_inv = bp_get_members_invitation_from_request();

		// Not currently accepting a request.
		if ( ! $maybe_inv->id ) {
			$send = true;
		}
	}

	return $send;
}
add_filter( 'bp_members_membership_requests_bypass_manual_approval', 'bp_members_invitations_maybe_bypass_request_approval', 10, 2 );
add_filter( 'bp_members_membership_requests_bypass_manual_approval_multisite', 'bp_members_invitations_maybe_bypass_request_approval', 10, 2 );

/**
 * Whether a user can access invitations screens.
 * Referred to by BP_Members_Invitations_Component::register_nav().
 *
 * @since 12.0.0
 *
 * @param bool $access Whether the user can view member invitations screens.
 */
function bp_members_invitations_user_can_view_screens() {
	return bp_user_has_access() && bp_user_can( bp_displayed_user_id(), 'bp_members_invitations_view_screens' );
}

/**
 * Whether a user can access the send invitations member screen.
 * Referred to by BP_Members_Invitations_Component::register_nav().
 *
 * @since 12.0.0
 *
 * @param bool $access Whether the user can view member invitations send screen.
 */
function bp_members_invitations_user_can_view_send_screen() {
	return bp_is_my_profile() && bp_user_can( bp_displayed_user_id(), 'bp_members_invitations_view_send_screen' );
}
