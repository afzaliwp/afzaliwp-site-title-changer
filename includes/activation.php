<?php

namespace AfzaliWP\TitleChanger\Includes;

defined( 'ABSPATH' ) || die();

class Activation {

	public function __construct() {
		$this->create_page();
	}

	private function create_page() {
		$page_id = get_option( 'afzaliwp_tc_page_id' );

		if ( $page_id && get_post( $page_id ) ) {
			wp_update_post( [
				'ID'          => $page_id,
				'post_status' => 'publish',
			] );

			return;
		}

		$new_page_id = wp_insert_post( [
			'post_title'   => __( 'Title Changer', 'afzaliwp-tc' ),
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => get_current_user_id(),
		] );

		if ( $new_page_id && ! is_wp_error( $new_page_id ) ) {
			update_option( 'afzaliwp_tc_page_id', $new_page_id );
		}
	}
}
