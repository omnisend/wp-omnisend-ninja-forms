<?php
/**
 * Omnisend Action setting provider
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Provider;

use Omnisend\NinjaFormsAddon\Actions\OmnisendAddOnAction;

/**
 * Class OmnisendActionSettingsProvider
 */
class OmnisendActionSettingsProvider {

	public const SEND_WELCOME_EMAIL = 'send_welcome_email_checkbox';
	private const TYPES             = array(
		'email'       => OmnisendAddOnAction::EMAIL,
		'address'     => OmnisendAddOnAction::ADDRESS,
		'city'        => OmnisendAddOnAction::CITY,
		'listcountry' => OmnisendAddOnAction::COUNTRY,
		'firstname'   => OmnisendAddOnAction::FIRST_NAME,
		'lastname'    => OmnisendAddOnAction::LAST_NAME,
		'phone'       => OmnisendAddOnAction::PHONE_NUMBER,
		'date'        => OmnisendAddOnAction::BIRTHDAY,
		'zip'         => OmnisendAddOnAction::POSTAL_CODE,
		'checkbox'    => 'checkbox',
	);

	/**
	 * Get the settings for the OmnisendActionSettingsProvider.
	 *
	 * @return array The settings array.
	 */
	public function get_settings(): array {
		if ( ! isset( $_GET['form_id'] ) ) {
			return array();
		}

		if ( ! isset( $_GET['page'] ) ) {
			return array();
		}

		if ( 'ninja-forms' !== $_GET['page'] ) {
			return array();
		}

		$form_id = sanitize_text_field( wp_unslash( $_GET['form_id'] ) );

		if ( 'new' === $form_id ) {
			return array();
		}

		[$fields_by_type, $fields] = $this->get_fields( $form_id );
		$settings                  = $this->get_heading_settings();

		foreach ( OmnisendAddOnAction::OMNISEND_FIELDS as $key => $label ) {
			$options = $fields;

			if ( array_key_exists( $key, $fields_by_type ) ) {
				$options = array_merge(
					array(
						array(
							'value' => '-',
							'label' => '-',
						),
					),
					$fields_by_type[ $key ]
				);
			}

			if (
				in_array( $key, array( OmnisendAddOnAction::EMAIL_CONSENT, OmnisendAddOnAction::PHONE_CONSENT ) ) &&
				array_key_exists( 'checkbox', $fields_by_type )
			) {
				$options = array_merge(
					array(
						array(
							'value' => '-',
							'label' => '-',
						),
					),
					$fields_by_type['checkbox']
				);
			}

			$settings[ $key ] = array(
				'name'    => $key,
				'type'    => 'select',
				'label'   => $label,
				'options' => $options,
				'width'   => 'one-half',
				'group'   => 'advanced',
				'value'   => 'default',
			);
		}

		return $settings;
	}

	/**
	 * Get the fields for the OmnisendActionSettingsProvider.
	 *
	 * @param string $form_id The form ID.
	 * @return array The fields array.
	 */
	private function get_fields( string $form_id ): array {
		$form_fields = Ninja_Forms()->form( $form_id )->get_fields();
		$all_fields  = array(
			array(
				'value' => '-',
				'label' => '-',
			),
		);
		$fields      = array();

		foreach ( $form_fields as $form_field ) {
			$key   = $form_field->get_setting( 'key' );
			$label = $form_field->get_setting( 'label' );
			$type  = $form_field->get_setting( 'type' );

			$field_type = 'general';
			if ( array_key_exists( $type, self::TYPES ) ) {
				$field_type = self::TYPES[ $type ];
			}

			$field_data = array(
				'value' => $key,
				'label' => $label,
			);

			$fields[ $field_type ][] = $field_data;
			$all_fields[]            = $field_data;
		}

		return array( $fields, $all_fields );
	}

	/**
	 * Get the heading settings.
	 *
	 * @return array The heading settings.
	 */
	private function get_heading_settings(): array {
		return array(
			'weclome_email_block'                   => array(
				'name'           => 'weclome_email_block',
				'type'           => 'html',
				'group'          => 'advanced',
				'label'          => esc_html__( 'This is a message', 'ninja-forms' ),
				'value'          => '<h2 class="omnisend-ninja-forms-block-heading omnisend-ninja-forms-form-top">' . esc_html__( 'Welcome email', 'ninja-forms' ) . '</h2>',
				'width'          => 'full',
				'use_merge_tags' => true,
			),
			'message'                               => array(
				'name'           => 'message',
				'type'           => 'html',
				'group'          => 'advanced',
				'label'          => esc_html__( 'This is a message', 'ninja-forms' ),
				'value'          => '<p class="omnisend-ninja-forms-text">' . esc_html__( 'Use this to automatically send your custom welcome email, created in Omnisend, to subscribers joining through Ninja forms.', 'ninja-forms' ) . '</p>',
				'width'          => 'full',
				'use_merge_tags' => true,
			),
			self::SEND_WELCOME_EMAIL                => array(
				'name'  => self::SEND_WELCOME_EMAIL,
				'type'  => 'toggle',
				'group' => 'advanced',
				'label' => esc_html__( 'Send a welcome email to new subscribers ', 'ninja-forms' ),
				'width' => 'full',
			),
			'welcoming_learn_more'                  => array(
				'name'           => 'welcoming_learn_more',
				'type'           => 'html',
				'group'          => 'advanced',
				'label'          => esc_html__( 'This is a message', 'ninja-forms' ),
				'value'          => '<div class="omnisend-ninja-forms-block-end"><a href="https://support.omnisend.com/en/articles/1061818-welcome-email-automation" target="_blank" class="omnisend-ninja-forms-link">' . esc_html__( 'Learn more about Welcome automation', 'ninja-forms' ) . '</a></div>',
				'width'          => 'full',
				'use_merge_tags' => true,
			),
			'field_mapping_block'                   => array(
				'name'           => 'field_mapping_block',
				'type'           => 'html',
				'group'          => 'advanced',
				'label'          => esc_html__( 'This is a message', 'ninja-forms' ),
				'value'          => '<h2 class="omnisend-ninja-forms-block-heading">' . esc_html__( 'Field mapping', 'ninja-forms' ) . '</h2>',
				'width'          => 'full',
				'use_merge_tags' => true,
			),
			'field_mapping_message'                 => array(
				'name'           => 'field_mapping_message',
				'type'           => 'html',
				'group'          => 'advanced',
				'label'          => esc_html__( 'This is a message', 'ninja-forms' ),
				'value'          => "<p class='omnisend-ninja-forms-text'>" . esc_html__( "Field mapping lets you align your form fields with Omnisend. It's important to match them correctly, so the information collected through Ninja Forms goes into the right place in Omnisend", 'ninja-forms' ) . '</h4>',
				'width'          => 'full',
				'use_merge_tags' => true,
			),
			'field_mapping_dont_see_fields_trouble' => array(
				'name'           => 'field_mapping_dont_see_fields_trouble',
				'type'           => 'html',
				'group'          => 'advanced',
				'label'          => esc_html__( 'This is a message', 'ninja-forms' ),
				'value'          => "<div class='omnisend-ninja-forms-updated-fields-heading'>" . esc_html__( 'Don\'t see updated fields?', 'ninja-forms' ) . "<a class='omnisend-ninja-refresh-page-link-link' href='#' onclick='window.location.reload();'>" . esc_html__( 'Refresh page', 'ninja-forms' ) . '</a></div>',
				'width'          => 'full',
				'use_merge_tags' => true,
			),
			'field_mapping_having_trouble'          => array(
				'name'           => 'field_mapping_having_trouble',
				'type'           => 'html',
				'group'          => 'advanced',
				'label'          => esc_html__( 'This is a message', 'ninja-forms' ),
				'value'          => "<div class='omnisend-ninja-forms-having-trouble'><div class='omnisend-ninja-forms-having-trouble-heading'>" . esc_html__( 'Having trouble? Explore our help article', 'ninja-forms' ) . "</div><div><a class='omnisend-ninja-forms-link' href='#'>" . esc_html__( 'Learn more', 'ninja-forms' ) . '</a></div> </div>',
				'width'          => 'full',
				'use_merge_tags' => true,
			),
		);
	}
}
