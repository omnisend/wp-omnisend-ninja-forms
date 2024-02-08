<?php
/**
 * Omnisend Tracker service
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Service;

/**
 * Class TrackerService
 *
 * @package Omnisend\NinjaFormsAddon\Service
 */
class TrackerService {

	/**
	 * Enables web tracking.
	 *
	 * @param string $email The email.
	 * @param string $phone The phone number.
	 * @param string $snippet_path The snippet path.
	 * @return void
	 */
	public function enable_web_tracking( string $email, string $phone, string $snippet_path ): void {
		$identifiers = array_filter(
			array(
				'email' => sanitize_email( $email ),
				'phone' => sanitize_text_field( $phone ),
			)
		);

		wp_enqueue_script( 'omnisend-snippet-script', $snippet_path, array(), '1.0.4', true );
		wp_localize_script( 'omnisend-snippet-script', 'omnisendIdentifiers', $identifiers );
	}
}
