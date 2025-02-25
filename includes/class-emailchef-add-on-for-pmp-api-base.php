<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Emailchef_Add_On_For_Pmp_Api_Base {

	protected $api_url = "https://app.emailchef.com";
	public $lastError;
	private $isLogged = false;

	/**
	 * @var string | null
	 */
	private $consumer_key = null;
	/**
	 * @var string | null
	 */
	private $consumer_secret = null;

	public function set(
		$consumer_key,
		$consumer_secret
	): Emailchef_Add_On_For_Pmp_Api_Base {
		$this->consumer_key = $consumer_key;
		$this->consumer_secret = $consumer_secret;
		return $this;
	}


	public function __construct( $consumer_key, $consumer_secret ) {
		$this->set($consumer_key, $consumer_secret);
	}

	public function getApiUrl(){
		return defined("EMAILCHEF_API_URL") ? EMAILCHEF_API_URL : $this->api_url;
	}

	protected function call( $route, $args = array(), $method = "POST" ) {

		$url = $this->getApiUrl() . "/apps/api/v1" . $route;

		$args = array(
			'body'   => $args,
			'method' => strtoupper( $method ),
			'headers' => [
				'consumerKey' => $this->consumer_key,
				'consumerSecret' => $this->consumer_secret
			]
		);

		$args = apply_filters( "emailchef-addon-for-pmp_get_args", $args );

		return wp_remote_request( $url, $args );

	}

}
