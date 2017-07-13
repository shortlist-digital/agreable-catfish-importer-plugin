<?php

namespace AgreableCatfishImporterPlugin\Services;

/**
 * Class User
 *
 * @package AgreableCatfishImporterPlugin\Services
 */
class User {

	/**
	 * @param $email
	 *
	 * @return bool|int
	 */
	public static function checkUserByEmail( $email ) {
		if ( ( $user = get_user_by( 'email', $email ) ) ) {
			return $user->ID;
		}

		return false;
	}

	/**
	 * @param $object
	 *
	 * @return int|\WP_Error
	 */
	public static function insertCatfishUser( $object ) {
		$user_data = array(
			'user_login'    => $object->slug,
			'user_nicename' => $object->slug,
			'user_email'    => $object->emailAddress,
			'display_name'  => $object->name,
			'description'   => $object->biography,
			'role'          => 'purgatory',
			'user_pass'     => null
		);
		$user_id   = wp_insert_user( $user_data );

		return $user_id;
	}

}
