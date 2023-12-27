<?php
/**
 * Omnisend API response validator
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Validator;

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
	 * @param mixed $response The API response.
	 * @return bool True if the response is valid, false otherwise.
	 */
	public function validate_response( $response ): bool {
		if ( is_wp_error( $response ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'wp_remote_post error: ' . $response->get_error_message() );
			}

			return false;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		if ( $http_code >= 400 ) {
			$body = wp_remote_retrieve_body( $response );
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( "HTTP error: {$http_code} - " . wp_remote_retrieve_response_message( $response ) . " - {$body}" );
			}

			return false;
		}

		return true;
	}
}
