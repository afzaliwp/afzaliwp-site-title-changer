<?php

namespace AfzaliWP\TitleChanger\Includes;

defined( 'ABSPATH' ) || die();

class Deactivation {

	public function __construct() {
		$this->draft_page();
	}

	private function draft_page() {
		$page_id = get_option( 'afzaliwp_tc_page_id' );

		if ( $page_id && get_post( $page_id ) ) {
			wp_update_post( [
				'ID'          => $page_id,
				'post_status' => 'draft',
			] );
		}
	}
}
