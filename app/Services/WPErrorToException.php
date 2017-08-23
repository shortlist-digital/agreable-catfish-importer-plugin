<?php


namespace AgreableCatfishImporterPlugin\Services;

use AgreableCatfishImporterPlugin\Exception\CatfishException;


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
			throw new CatfishException( $er->get_error_message() );

		} else {
			return $er;
		}
	}
}
