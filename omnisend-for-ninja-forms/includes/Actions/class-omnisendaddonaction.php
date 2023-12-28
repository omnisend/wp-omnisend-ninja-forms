<?php
/**
 * Omnisend Addon Action
 *
 * @package OmnisendNinjaFormsPlugin
 */

declare(strict_types=1);

namespace Omnisend\NinjaFormsAddon\Actions;

use NF_Abstracts_Action;
use Omnisend\NinjaFormsAddon\Builder\RequestBodyBuilder;
use Omnisend\NinjaFormsAddon\Mapper\FormFieldsMapper;
use Omnisend\NinjaFormsAddon\OmnisendResponse;
use Omnisend\NinjaFormsAddon\Provider\OmnisendActionSettingsProvider;
use Omnisend\NinjaFormsAddon\Service\OmnisendApiService;
use Omnisend\NinjaFormsAddon\Service\TrackerService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Omnisend Addon
 */
class OmnisendAddOnAction extends NF_Abstracts_Action {

	const EMAIL           = 'email';
	const PHONE_NUMBER    = 'phone_number';
	const ADDRESS         = 'address';
	const CITY            = 'city';
	const STATE           = 'state';
	const COUNTRY         = 'country';
	const FIRST_NAME      = 'first_name';
	const LAST_NAME       = 'last_name';
	const BIRTHDAY        = 'birthday';
	const POSTAL_CODE     = 'postal_code';
	const EMAIL_CONSENT   = 'email_consent';
	const PHONE_CONSENT   = 'phone_consent';
	const OMNISEND_FIELDS = array(
		self::EMAIL         => 'Email',
		self::PHONE_NUMBER  => 'Phone Number',
		self::ADDRESS       => 'Address',
		self::CITY          => 'City',
		self::STATE         => 'State',
		self::COUNTRY       => 'Country',
		self::FIRST_NAME    => 'First Name',
		self::LAST_NAME     => 'Last Name',
		self::BIRTHDAY      => 'Birthday',
		self::POSTAL_CODE   => 'Postal Code',
		self::EMAIL_CONSENT => 'Email Consent',
		self::PHONE_CONSENT => 'Phone Consent',
	);

	/**
	 * Name
	 *
	 * @var string
	 */
	protected $_name = 'omnisend';

	/**
	 * Tags
	 *
	 * @var array
	 */
	protected $_tags = array();

	/**
	 * Timing
	 *
	 * @var string
	 */
	protected $_timing = 'late';

	/**
	 * Priority
	 *
	 * @var int
	 */
	protected $_priority = 20;

	/**
	 * Omnisend service
	 *
	 * @var OmnisendApiService
	 */
	private $omnisend_service;

	/**
	 * Tracker service
	 *
	 * @var TrackerService
	 */
	private $tracker_service;

	/**
	 * Snippet path
	 *
	 * @var string
	 */
	private $snippet_path;

	/**
	 * Creating a Action
	 *
	 * @param string $snippet_path The path to the snippet.
	 */
	public function __construct( $snippet_path ) {
		parent::__construct();

		$this->_nicename        = esc_html__( 'Omnisend', 'ninja-forms' );
		$settings_provider      = new OmnisendActionSettingsProvider();
		$this->_settings        = array_merge( $this->_settings, $settings_provider->get_settings() );
		$this->omnisend_service = new OmnisendApiService();
		$this->tracker_service  = new TrackerService();
		$this->snippet_path     = $snippet_path;
	}

	/**
	 * Process sending contact to omnisend
	 *
	 * @param array  $action_settings The settings for the action.
	 * @param string $form_id The ID of the form.
	 * @param array  $data The data array.
	 *
	 * @return void
	 */
	public function process( $action_settings, $form_id, $data ) {
		$form_name = $data['settings']['title'];

		if ( ! is_array( $action_settings ) || ! is_array( $data ) ) {
			return;
		}

		/**
		 * Response object for Omnisend.
		 *
		 * @var OmnisendResponse $response
		 */
		$response = $this->omnisend_service->create_omnisend_contact( $form_name, $action_settings, $data );

		if ( ! $response->get_success() ) {
			return;
		}

		$this->tracker_service->enable_web_tracking(
			$response->get_email(),
			$response->get_phone() ?? '',
			$this->snippet_path
		);
	}
}
