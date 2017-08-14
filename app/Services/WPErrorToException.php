<?php


namespace AgreableCatfishImporterPlugin\Services;


/**
 * Class WPErrorToException
 *
 * @package AgreableCatfishImporterPlugin\Services
 */
class WPErrorToException {
	/**
	 * @param $er
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function loud( $er ) {
		if ( is_wp_error( $er ) ) {
			/**
			 * @var $er \WP_Error
			 */
			throw new \Exception(  $er->get_error_message() );
		} else {
			return $er;
		}
	}
}
