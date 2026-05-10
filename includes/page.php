<?php

namespace AfzaliWP\TitleChanger\Includes;

defined( 'ABSPATH' ) || die();

class Page {

	public function __construct() {
		add_filter( 'the_content', [ $this, 'render_page_content' ] );
		add_action( 'template_redirect', [ $this, 'handle_redirect' ] );
	}

	public function handle_redirect() {
		$page_id = get_option( 'afzaliwp_tc_page_id' );

		if ( ! $page_id || ! is_page( $page_id ) ) {
			return;
		}

		if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}
	}

	public function render_page_content( $content ) {
		$page_id = get_option( 'afzaliwp_tc_page_id' );

		if ( ! $page_id || ! is_page( $page_id ) ) {
			return $content;
		}

		if ( ! is_user_logged_in() ) {
			return $this->render_login_form();
		}

		if ( current_user_can( 'manage_options' ) ) {
			return $this->render_title_form();
		}

		return $content;
	}

	private function render_login_form() {
		$html = '<div id="afzaliwp-tc-app" class="afzaliwp-tc-container">';
		$html .= '<div class="card bg-base-100 shadow-xl max-w-md mx-auto">';
		$html .= '<div class="card-body">';
		$html .= '<h2 class="card-title justify-center text-2xl mb-6">' . esc_html__( 'Login', 'afzaliwp-tc' ) . '</h2>';
		$html .= '<div id="afzaliwp-tc-login-message"></div>';
		$html .= '<form id="afzaliwp-tc-login-form">';
		$html .= '<div class="form-control w-full mb-4">';
		$html .= '<label class="label"><span class="label-text">' . esc_html__( 'Username', 'afzaliwp-tc' ) . '</span></label>';
		$html .= '<input type="text" name="username" class="input input-bordered w-full" required />';
		$html .= '</div>';
		$html .= '<div class="form-control w-full mb-6">';
		$html .= '<label class="label"><span class="label-text">' . esc_html__( 'Password', 'afzaliwp-tc' ) . '</span></label>';
		$html .= '<input type="password" name="password" class="input input-bordered w-full" required />';
		$html .= '</div>';
		$html .= '<div class="form-control">';
		$html .= '<button type="submit" class="btn btn-primary w-full">';
		$html .= '<span class="afzaliwp-tc-btn-text">' . esc_html__( 'Sign In', 'afzaliwp-tc' ) . '</span>';
		$html .= '<span class="loading loading-spinner loading-sm afzaliwp-tc-btn-loading hidden"></span>';
		$html .= '</button>';
		$html .= '</div>';
		$html .= '</form>';
		$html .= '</div></div></div>';

		return $html;
	}

	private function render_title_form() {
		$current_title = get_bloginfo( 'name' );

		$html = '<div id="afzaliwp-tc-app" class="afzaliwp-tc-container">';
		$html .= '<div class="card bg-base-100 shadow-xl max-w-lg mx-auto">';
		$html .= '<div class="card-body">';
		$html .= '<h2 class="card-title justify-center text-2xl mb-6">' . esc_html__( 'Change Site Title', 'afzaliwp-tc' ) . '</h2>';
		$html .= '<div id="afzaliwp-tc-title-message"></div>';
		$html .= '<form id="afzaliwp-tc-title-form">';
		$html .= '<div class="form-control w-full mb-6">';
		$html .= '<label class="label"><span class="label-text">' . esc_html__( 'Site Title', 'afzaliwp-tc' ) . '</span></label>';
		$html .= '<input type="text" name="site_title" class="input input-bordered w-full" value="' . esc_attr( $current_title ) . '" required />';
		$html .= '</div>';
		$html .= '<div class="form-control">';
		$html .= '<button type="submit" class="btn btn-primary w-full">';
		$html .= '<span class="afzaliwp-tc-btn-text">' . esc_html__( 'Update Title', 'afzaliwp-tc' ) . '</span>';
		$html .= '<span class="loading loading-spinner loading-sm afzaliwp-tc-btn-loading hidden"></span>';
		$html .= '</button>';
		$html .= '</div>';
		$html .= '</form>';
		$html .= '<div class="divider"></div>';
		$html .= '<div class="text-center">';
		$html .= '<button id="afzaliwp-tc-changelog-btn" class="btn btn-outline btn-sm">' . esc_html__( 'View Changelog', 'afzaliwp-tc' ) . '</button>';
		$html .= '</div>';
		$html .= '</div></div>';

		$html .= '<dialog id="afzaliwp-tc-changelog-modal" class="modal">';
		$html .= '<div class="modal-box max-w-2xl">';
		$html .= '<form method="dialog"><button class="!btn !btn-sm !btn-circle !btn-ghost !rounded-full !p-1 !w-4 !h-4 !mb-1">✕</button></form>';
		$html .= '<h3 class="font-bold text-lg mb-4">' . esc_html__( 'Title Change History', 'afzaliwp-tc' ) . '</h3>';
		$html .= '<div id="afzaliwp-tc-changelog-content">';
		$html .= '<span class="loading loading-spinner loading-md"></span>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<form method="dialog" class="modal-backdrop"><button>' . esc_html__( 'Close', 'afzaliwp-tc' ) . '</button></form>';
		$html .= '</dialog>';

		$html .= '</div>';

		return $html;
	}
}
