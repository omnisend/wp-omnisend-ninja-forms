<?php
/**
 * Omnisend Request body builder
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Builder;

use Omnisend\NinjaFormsAddon\Actions\OmnisendAddOnAction;

/**
 * Class RequestBodyBuilder
 */
class RequestBodyBuilder {

	/**
	 * Get the request body for Omnisend API.
	 *
	 * @param array  $mapped_fields The mapped fields.
	 * @param string $form_name     The form name.
	 * @return array The request body.
	 */
	public function get_body( array $mapped_fields, string $form_name ): array {
		$email_consent = 'nonSubscribed';
		$phone_consent = 'nonSubscribed';

		if ( ! isset( $_SERVER['REMOTE_ADDR'] ) && ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return array();
		}

		$consent_object = array(
			'source'    => 'ninja-forms',
			'createdAt' => gmdate( 'c' ),
			'ip'        => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ),
			'userAgent' => sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ),
		);

		$fields_to_process = array(
			OmnisendAddOnAction::EMAIL,
			OmnisendAddOnAction::ADDRESS,
			OmnisendAddOnAction::COUNTRY,
			OmnisendAddOnAction::CITY,
			OmnisendAddOnAction::STATE,
			OmnisendAddOnAction::FIRST_NAME,
			OmnisendAddOnAction::LAST_NAME,
			OmnisendAddOnAction::BIRTHDAY,
			OmnisendAddOnAction::PHONE_NUMBER,
			OmnisendAddOnAction::POSTAL_CODE,
			OmnisendAddOnAction::EMAIL_CONSENT,
			OmnisendAddOnAction::PHONE_CONSENT,
		);

		$email        = '';
		$phone_number = '';
		$postal_code  = '';
		$address      = '';
		$country      = '';
		$city         = '';
		$state        = '';
		$first_name   = '';
		$last_name    = '';
		$birthday     = '';

		foreach ( $fields_to_process as $field ) {
			if ( isset( $mapped_fields[ $field ] ) && $mapped_fields[ $field ] != '-1' ) {
				${$field} = $mapped_fields[ $field ];
			}
		}

		if ( empty( $email ) && empty( $phone_number ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Email and phone number are not mapped. Skipping Omnisend contact creation.' );
			}

			return array();
		}

		$identifiers = array();
		if ( '' !== $email ) {
			$email_identifier = array(
				'type'     => 'email',
				'channels' => array(
					'email' => array(
						'status'     => $email_consent,
						'statusDate' => gmdate( 'c' ),
					),
				),
				'id'       => $email,
			);

			if ( 'subscribed' === $email_consent ) {
				$email_identifier['consent'] = $consent_object;
			}
			array_push( $identifiers, $email_identifier );
		}

		if ( '' !== $phone_number ) {
			$phone_identifier = array(
				'type'     => 'phone',
				'channels' => array(
					'sms' => array(
						'status'     => $phone_consent,
						'statusDate' => gmdate( 'c' ),
					),
				),
				'id'       => $phone_number,
			);
			if ( 'subscribed' === $phone_consent ) {
				$phone_identifier['consent'] = $consent_object;
			}

			array_push( $identifiers, $phone_identifier );
		}

		$data                = array( 'identifiers' => $identifiers );
		$fields_to_data_keys = array(
			'first_name'  => 'firstName',
			'last_name'   => 'lastName',
			'birthday'    => 'birthdate',
			'postal_code' => 'postalCode',
			'address'     => 'address',
			'state'       => 'state',
			'country'     => 'country',
			'city'        => 'city',
		);

		foreach ( $fields_to_data_keys as $variable => $data_key ) {
			if ( ! empty( $$variable ) ) {
				$data[ $data_key ] = $$variable;
			}
		}

		if ( isset( $mapped_fields['sendWelcomeEmail'] ) ) {
			$data['sendWelcomeEmail'] = $mapped_fields['sendWelcomeEmail'];
		}

		$form_name    = preg_replace( '/[^A-Za-z0-9\-]/', '', $form_name );
		$data['tags'] = array( 'ninja_forms', 'ninja_forms ' . $form_name );

		if ( ! empty( $mapped_fields['customFields'] ) ) {
			$data['customProperties'] = (object) $mapped_fields['customFields'];
		}

		return $data;
	}
}
