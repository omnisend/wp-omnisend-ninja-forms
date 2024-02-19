<?php
/**
 * Omnisend Request body builder
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Builder;

use Omnisend\NinjaFormsAddon\Actions\OmnisendAddOnAction;
use Omnisend\SDK\V1\Contact;

/**
 * Class RequestBodyBuilder
 */
class RequestBodyBuilder {
	const FORM_NAME_REGEXP = '/[^A-Za-z0-9\-]/';

	/**
	 * Get the request body for Omnisend API.
	 *
	 * @param array  $mapped_fields The mapped fields.
	 * @param string $form_name     The form name.
	 * @return Contact|null The request body.
	 */
	public function get_body( array $mapped_fields, string $form_name ): ?Contact {
		$form_name     = preg_replace( self::FORM_NAME_REGEXP, '', $form_name );
		$email_consent = 'nonSubscribed';
		$phone_consent = 'nonSubscribed';
		$contact       = new Contact();

		if ( ! isset( $_SERVER['REMOTE_ADDR'] ) && ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			error_log( 'Unable to fetch REMOTE_ADDR and HTTP_USER_AGENT.' );

			return null;
		}

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

		if ( '' === $email ) {
			return null;
		}

		$contact->set_email( $email );
		if ( 'subscribed' === $email_consent ) {
			$contact->set_email_consent( 'ninja-forms-' . $form_name );
			$contact->set_email_opt_in( 'ninja-forms-' . $form_name );
		}

		if ( '' !== $phone_number ) {
			$contact->set_phone( $phone_number );
			if ( 'subscribed' === $phone_consent ) {
				$contact->set_phone_consent( 'ninja-forms-' . $form_name );
				$contact->set_phone_opt_in( 'ninja-forms-' . $form_name );
			}
		}

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
				$method = 'set_' . $variable;
				$contact->$method( $$variable );
			}
		}

		if ( isset( $mapped_fields['sendWelcomeEmail'] ) ) {
			$contact->set_welcome_email( $mapped_fields['sendWelcomeEmail'] );
		}

		$contact->add_tag( 'ninja_forms' );
		$contact->add_tag( 'ninja_forms ' . $form_name );

		foreach ( $mapped_fields['customFields'] as $field_name => $field_value ) {
			$contact->add_custom_property( $field_name, $field_value );
		}

		return $contact;
	}
}
