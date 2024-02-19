<?php
/**
 * Omnisend Api service
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Service;

use Omnisend\Internal\V1\Client;
use Omnisend\NinjaFormsAddon\Actions\OmnisendAddOnAction;
use Omnisend\NinjaFormsAddon\Builder\RequestBodyBuilder;
use Omnisend\NinjaFormsAddon\Factory\OmnisendResponseFactory;
use Omnisend\NinjaFormsAddon\Mapper\FormFieldsMapper;
use Omnisend\NinjaFormsAddon\OmnisendResponse;
use Omnisend\NinjaFormsAddon\Validator\ResponseValidator;
use Omnisend\SDK\V1\Omnisend;

/**
 * Omnisend API Service.
 */
class OmnisendApiService {

	/**
	 * Omnisend API client.
	 *
	 * @var Client
	 */
	private $client;

	/**
	 * Form fields mapper.
	 *
	 * @var FormFieldsMapper
	 */
	private $fields_mapper;

	/**
	 * Request body builder.
	 *
	 * @var RequestBodyBuilder
	 */
	private $body_builder;

	/**
	 * Response factory
	 *
	 * @var OmnisendResponseFactory
	 */
	private $response_factory;

	/**
	 *  Response validator.
	 *
	 * @var ResponseValidator
	 */
	private $response_validator;

	/**
	 * OmnisendApiService class constructor.
	 */
	public function __construct() {
		$this->fields_mapper      = new FormFieldsMapper();
		$this->body_builder       = new RequestBodyBuilder();
		$this->response_factory   = new OmnisendResponseFactory();
		$this->response_validator = new ResponseValidator();
		$this->client             = Omnisend::get_client(
			OMNISEND_NINJA_ADDON_NAME,
			OMNISEND_NINJA_ADDON_VERSION
		);
	}

	/**
	 * Creates an Omnisend contact.
	 *
	 * @param string $form_name The form name.
	 * @param array  $action_settings The action settings.
	 * @param array  $form_data The form data.
	 * @return OmnisendResponse The Omnisend response.
	 */
	public function create_omnisend_contact( string $form_name, array $action_settings, array $form_data ): OmnisendResponse {
		$mapped_fields_values = $this->fields_mapper->get_field_mappings( $action_settings, $form_data );
		$contact              = $this->body_builder->get_body( $mapped_fields_values, $form_name );

		if ( null === $contact ) {
			return $this->response_factory->create( false );
		}
		$response = $this->client->create_contact( $contact );

		if ( ! $this->response_validator->validate_response( $response ) ) {
			return $this->response_factory->create( false );
		}

		return $this->response_factory->create(
			true,
			$mapped_fields_values[ OmnisendAddOnAction::EMAIL ],
			$mapped_fields_values[ OmnisendAddOnAction::PHONE_NUMBER ]
		);
	}
}
