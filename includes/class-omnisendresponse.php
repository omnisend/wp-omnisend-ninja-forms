<?php
/**
 * Omnisend response
 *
 * @package OmnisendNinjaFormsPlugin
 */

namespace Omnisend\NinjaFormsAddon;

/**
 * Class OmnisendResponse
 *
 * Represents a response from Omnisend.
 */
class OmnisendResponse {

	/**
	 * Is the response successful.
	 *
	 * @var bool
	 */
	private $success;

	/**
	 * Email
	 *
	 * @var null | string
	 */
	private $email = null;

	/**
	 * Phone number
	 *
	 * @var null | string
	 */
	private $phone = null;

	/**
	 * Returns if response was successfully sent to Omnisend.
	 *
	 * @return bool
	 */
	public function get_success() {
		return $this->success;
	}

	/**
	 * Sets if response was successfully sent to Omnisend.
	 *
	 * @param bool $success The success status of the response.
	 */
	public function set_success( $success ) {
		$this->success = $success;
	}

	/**
	 * Gets the email address.
	 *
	 * @return string|null
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Sets the email address.
	 *
	 * @param string $email The email address.
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Gets the phone number.
	 *
	 * @return string|null
	 */
	public function get_phone() {
		return $this->phone;
	}

	/**
	 * Sets the phone number.
	 *
	 * @param string $phone The phone number.
	 */
	public function set_phone( $phone ) {
		$this->phone = $phone;
	}
}
