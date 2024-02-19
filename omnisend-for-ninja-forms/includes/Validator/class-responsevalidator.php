<?php
/**
 * Omnisend API response validator
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Validator;

use Omnisend\SDK\V1\CreateContactResponse;

/**
 * Class ResponseValidator
 *
 * Omnisend API response validator.
 *
 * @package OmnisendNinjaFormsPlugin
 */
class ResponseValidator {

	/**
	 * Validates the API response.
	 *
	 * @param CreateContactResponse $response The API response.
	 * @return bool True if the response is valid, false otherwise.
	 */
	public function validate_response( CreateContactResponse $response ): bool {
		if ( $response->get_wp_error()->has_errors() ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'wp_remote_post error: ' . $response->get_error_message() );
			}

			return false;
		}

		if ( empty( $response->get_contact_id() ) ) {
			return false;
		}

		return true;
	}
}
