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
			$code = $er->get_error_code();
			if ( ! $code ) {
				$code = 0;
			}

			throw new CatfishException( implode( PHP_EOL, $er->get_error_messages() ) );
		} else {
			return $er;
		}
	}
}