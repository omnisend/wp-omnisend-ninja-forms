<?php
/**
 * Omnisend response factory
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Factory;

use Omnisend\NinjaFormsAddon\OmnisendResponse;

/**
 * Omnisend response factory.
 */
class OmnisendResponseFactory {

	/**
	 * Create an Omnisend response.
	 *
	 * @param bool        $success Whether the response is successful.
	 * @param string      $email The email associated with the response.
	 * @param string|null $phone The phone number associated with the response.
	 * @return OmnisendResponse The created Omnisend response.
	 */
	public function create( bool $success, string $email = '', ?string $phone = '' ): OmnisendResponse {
		$omnisend_response = new OmnisendResponse();

		$omnisend_response->set_success( $success );

		if ( ! empty( $email ) ) {
			$omnisend_response->set_email( $email );
		}

		if ( ! empty( $phone ) ) {
			$omnisend_response->set_phone( $phone );
		}

		return $omnisend_response;
	}
}
