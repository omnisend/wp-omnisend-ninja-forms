<?php
/**
 * Form fields mapper
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Mapper;

use Omnisend\NinjaFormsAddon\Actions\OmnisendAddOnAction;
use Omnisend\NinjaFormsAddon\Provider\OmnisendActionSettingsProvider;

/**
 * Class FormFieldsMapper
 */
class FormFieldsMapper {

	const NAME_REGEXP = '/[^A-Za-z0-9_]/';

	/**
	 * Get field mappings.
	 *
	 * @param array $action_settings Action settings.
	 * @param array $form_fields     Form fields.
	 * @return array Field mappings.
	 */
	public function get_field_mappings( array $action_settings, array $form_fields ): array {
		$field_mappings = array();

		foreach ( OmnisendAddOnAction::OMNISEND_FIELDS as $key => $label ) {
			if ( ! isset( $action_settings[ $key ] ) ) {
				continue;
			}

			if ( 'default' === $action_settings[ $key ] || '-' === $action_settings[ $key ] ) {
				continue;
			}

			$field_mappings[ $key ] = $action_settings[ $key ];
		}

		$values         = array();
		$consent_fields = array( OmnisendAddOnAction::EMAIL_CONSENT, OmnisendAddOnAction::PHONE_CONSENT );

		foreach ( $field_mappings as $key => $field ) {
			foreach ( $form_fields['fields'] as $form_field ) {
				if ( $form_field['key'] !== $field ) {
					continue;
				}

				if ( in_array( $key, $consent_fields, true ) ) {
					if ( '1' == $form_field['value'] ) {
						$values[ $key ] = 'subscribed';
					} else {
						$values[ $key ] = 'nonSubscribed';
					}

					continue;
				}

				if ( OmnisendAddOnAction::BIRTHDAY === $key && ! empty( $form_field['value'] ) && strtotime( $form_field['value'] ) ) {
					$values[ $key ] = gmdate( 'Y-m-d', strtotime( $form_field['value'] ) );
					continue;
				}

				$values[ $key ] = $form_field['value'];
			}
		}

		if (
			isset( $action_settings[ OmnisendActionSettingsProvider::SEND_WELCOME_EMAIL ] ) &&
			'1' === $action_settings[ OmnisendActionSettingsProvider::SEND_WELCOME_EMAIL ]
		) {
			$values['sendWelcomeEmail'] = true;
		}

		$values['customFields'] = $this->map_custom_properties( $form_fields['fields'], $field_mappings );

		return $values;
	}

	/**
	 * Map custom properties.
	 *
	 * @param array $form_fields    Form fields.
	 * @param array $field_mappings Field mappings.
	 * @return array Custom properties.
	 */
	private function map_custom_properties( array $form_fields, array $field_mappings ): array {
		$custom_properties = array();
		$prefix            = 'ninja_forms_';
		foreach ( $form_fields as $field ) {
			$field_label = $field['label'];

			if ( in_array( $field['key'], $field_mappings, true ) || 'submit' === $field['type'] ) {
				continue;
			}

			$safe_label = str_replace( ' ', '_', $field_label );
			$safe_label = preg_replace( self::NAME_REGEXP, '', $safe_label );
			$safe_label = strtolower( $safe_label );

			if ( 'listcheckbox' !== $field['type'] ) {
				$custom_properties[ $prefix . $safe_label ] = $field['value'];

				continue;
			}

			$selected_values = $field['value'];
			$selected_labels = array();

			if ( empty( $selected_values ) ) {
				continue;
			}

			foreach ( $field['options'] as $option ) {
				if ( in_array( $option['value'], $selected_values, true ) ) {
					$selected_labels[] = $option['label'];
				}
			}

			$custom_properties[ $prefix . $safe_label ] = $selected_labels;
		}

		return $custom_properties;
	}
}
