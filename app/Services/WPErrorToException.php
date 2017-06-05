<?php
/**
 * User: maciej
 * Date: 05/06/2017
 * Time: 11:32
 */

namespace AgreableCatfishImporterPlugin\Services;


class WPErrorToException {
	public static function loud( $er ) {
		if ( is_wp_error( $er ) ) {
			/**
			 * @var $er \WP_Error
			 */
			throw new \Exception( implode( PHP_EOL, $er->get_error_messages() ), $er->get_error_code() );
		} else {
			return $er;
		}
	}
}