<?php
/**
 * Omnisend Api client
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Client;

/**
 * Omnisend Api client.
 */
class OmnisendApiClient {

	/**
	 * Creates an Omnisend contact.
	 *
	 * @param array $body The contact data.
	 * @return mixed The response from the API.
	 */
	public function create_omnisend_contact( array $body ) {
		$api_key  = get_option( 'omnisend_api_key', null );
		$endpoint = 'https://api.omnisend.com/v3/contacts';

		$data = array(
			'body'    => wp_json_encode( $body ),
			'headers' => array(
				'Content-Type' => 'application/json',
				'X-API-Key'    => $api_key,
			),
			'timeout' => 10,
		);

		$response = wp_remote_post( $endpoint, $data );

		return $response;
	}
}
