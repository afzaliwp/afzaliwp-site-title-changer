<?php

namespace AfzaliWP\TitleChanger\Includes;

defined( 'ABSPATH' ) || die();

class Ajax {

	public function __construct() {
		add_action( 'wp_ajax_nopriv_afzaliwp_tc_login', [ $this, 'handle_login' ] );
		add_action( 'wp_ajax_afzaliwp_tc_update_title', [ $this, 'handle_update_title' ] );
		add_action( 'wp_ajax_afzaliwp_tc_get_changelog', [ $this, 'handle_get_changelog' ] );
	}

	public function handle_login() {
		check_ajax_referer( 'afzaliwp_tc_nonce', '_ajax_nonce' );

		$username = isset( $_POST[ 'username' ] ) ? sanitize_user( wp_unslash( $_POST[ 'username' ] ) ) : '';
		$password = isset( $_POST[ 'password' ] ) ? $_POST[ 'password' ] : '';

		if ( empty( $username ) || empty( $password ) ) {
			do_action( 'wp_login_failed', $username );

			wp_send_json_error( [
				'html' => '<div class="alert alert-error mb-4"><span>' . esc_html__( 'Please enter both username and password.', 'afzaliwp-tc' ) . '</span></div>',
			] );
		}

		$user = wp_authenticate( $username, $password );

		if ( is_wp_error( $user ) ) {
			do_action( 'wp_login_failed', $username );

			wp_send_json_error( [
				'html' => '<div class="alert alert-error mb-4"><span>' . esc_html__( 'Invalid username or password.', 'afzaliwp-tc' ) . '</span></div>',
			] );
		}

		if ( ! user_can( $user, 'manage_options' ) ) {
			wp_send_json_error( [
				'html' => '<div class="alert alert-warning mb-4"><span>' . esc_html__( 'You do not have permission to access this page.', 'afzaliwp-tc' ) . '</span></div>',
			] );
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );

		do_action( 'wp_login', $user->user_login, $user );

		wp_send_json_success( [
			'redirect' => true,
		] );
	}

	public function handle_update_title() {
		check_ajax_referer( 'afzaliwp_tc_nonce', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'html' => '<div class="alert alert-error mb-4"><span>' . esc_html__( 'Unauthorized access.', 'afzaliwp-tc' ) . '</span></div>',
			] );
		}

		$new_title = isset( $_POST[ 'site_title' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'site_title' ] ) ) : '';

		if ( empty( $new_title ) ) {
			wp_send_json_error( [
				'html' => '<div class="alert alert-error mb-4"><span>' . esc_html__( 'Site title cannot be empty.', 'afzaliwp-tc' ) . '</span></div>',
			] );
		}

		$old_title = get_bloginfo( 'name' );

		if ( $old_title === $new_title ) {
			wp_send_json_error( [
				'html' => '<div class="alert alert-warning mb-4"><span>' . esc_html__( 'The new title is the same as the current one.', 'afzaliwp-tc' ) . '</span></div>',
			] );
		}

		update_option( 'blogname', $new_title );

		$this->add_changelog_entry( $old_title, $new_title );

		wp_send_json_success( [
			'html' => '<div class="alert alert-success mb-4"><span class="text-white">' . esc_html__( 'Site title updated successfully.', 'afzaliwp-tc' ) . '</span></div>',
		] );
	}

	public function handle_get_changelog() {
		check_ajax_referer( 'afzaliwp_tc_nonce', '_ajax_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [
				'html' => '<p class="text-error">' . esc_html__( 'Unauthorized access.', 'afzaliwp-tc' ) . '</p>',
			] );
		}

		$changelog = get_option( 'afzaliwp_tc_changelog', [] );

		if ( empty( $changelog ) ) {
			wp_send_json_success( [
				'html' => '<p class="text-center text-base-content/60">' . esc_html__( 'No changes recorded yet.', 'afzaliwp-tc' ) . '</p>',
			] );
		}

		$html = '<div class="overflow-x-auto"><table class="table table-zebra w-full">';
		$html .= '<thead><tr>';
		$html .= '<th>' . esc_html__( 'Date', 'afzaliwp-tc' ) . '</th>';
		$html .= '<th>' . esc_html__( 'User', 'afzaliwp-tc' ) . '</th>';
		$html .= '<th>' . esc_html__( 'From', 'afzaliwp-tc' ) . '</th>';
		$html .= '<th>' . esc_html__( 'To', 'afzaliwp-tc' ) . '</th>';
		$html .= '</tr></thead><tbody>';

		$reversed = array_reverse( $changelog );

		foreach ( $reversed as $entry ) {
			$html .= '<tr>';
			$html .= '<td class="text-sm">' . esc_html( $entry[ 'date' ] ) . '</td>';
			$html .= '<td class="text-sm">' . esc_html( $entry[ 'user' ] ) . '</td>';
			$html .= '<td class="text-sm"><code>' . esc_html( $entry[ 'from' ] ) . '</code></td>';
			$html .= '<td class="text-sm"><code>' . esc_html( $entry[ 'to' ] ) . '</code></td>';
			$html .= '</tr>';
		}

		$html .= '</tbody></table></div>';

		wp_send_json_success( [ 'html' => $html ] );
	}

	private function add_changelog_entry( $old_title, $new_title ) {
		$changelog = get_option( 'afzaliwp_tc_changelog', [] );

		$current_user = wp_get_current_user();

		$changelog[] = [
			'date' => current_time( 'Y-m-d H:i:s' ),
			'user' => $current_user->display_name,
			'from' => $old_title,
			'to'   => $new_title,
		];

		update_option( 'afzaliwp_tc_changelog', $changelog, false );
	}
}
