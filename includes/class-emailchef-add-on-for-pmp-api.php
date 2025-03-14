<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Emailchef_Add_On_For_Pmp_Api extends Emailchef_Add_On_For_Pmp_Api_Base {

	public function lists( $args = array() ) {


		if (empty($args)) {
			$args = array(
				'hidden'    => 0,
				'limit'     => 100,
				'offset'    => 0,
				'orderby'   => 'cd',
				'ordertype' => 'd',
				'pinned' => 1
			);
		}


		return $this->json( "/lists", $args, "GET" );
	}

	public function add_contact(
		$list_id,
		$email,
		$first_name,
		$last_name = ""
	){
		return $this->json( "/contacts", json_encode([
			"instance_in" => [
				"list_id"   => $list_id,
				"status"    => "ACTIVE",
				"email"     => $email,
				"firstname" => $first_name,
				"lastname"  => $last_name,
				"mode"      => "ADMIN"
			]
		]), "POST" );
	}

	private function json( $route, $args, $method = "POST" ) {

		$response = $this->call(
			$route,
			$args,
			$method
		);

		$status_code = wp_remote_retrieve_response_code( $response );

		do_action( "pmproecaddon_api_response", $response, [
			'route'  => $route,
			'args'   => $args,
			'method' => $method
		] );

		if ( $status_code !== 200 ) {
			return apply_filters( "pmproecaddon_response_body_error",
				[
					"status"      => "error",
					"sub_status"  => "api_error",
					"status_code" => $status_code
				],
				$response,
				$route,
				$args,
				$method
			);
		}

		$response_body = wp_remote_retrieve_body( $response );

		return apply_filters( "pmproecaddon_response_body_success",
			json_decode( $response_body, true ),
			$response,
			$route,
			$args,
			$method
		);
	}
	public function account() {
		return $this->json( "/accounts/current", array(), "GET" );
	}
}
