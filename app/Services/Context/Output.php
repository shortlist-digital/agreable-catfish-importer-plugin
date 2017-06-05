<?php
/**
 * User: maciej
 * Date: 01/06/2017
 * Time: 10:51
 */

namespace AgreableCatfishImporterPlugin\Services\Context;

/**
 * Class Output
 * @package AgreableCatfishImporterPlugin\Driver\Context
 * @method static int cliStatic( $message )
 * @method static int cliErrorStatic( $message )
 * @method static int cliSuccessStatic( $message )
 * @method static int httpStatic( $message )
 * @method static int cliOutputStatic( $message )
 * @method static int httpOutputStatic( $message )
 */
class Output {

	/**
	 * @var bool
	 */
	private $isCli = false;

	/**
	 * @var bool
	 */
	private $isHttp = false;

	/**
	 * Output constructor.
	 */
	public function __construct() {
		$this->isCli  = ( defined( 'WP_CLI' ) && WP_CLI );
		$this->isHttp = ! $this->isCli;
	}

	/**
	 * echo's no matter what context we are in
	 *
	 * @param $message string
	 */
	public function __invoke( $message ) {
		$this->cli( $message );
		$this->http( $message );
	}


	/**
	 * Triggered only when in cli context
	 *
	 * @param $message string
	 */
	public function cli( $message ) {
		if ( $this->isCli ) {
			$this->cliOutput( $message );
		}
	}

	public function cliSuccess($message) {
		if ( $this->isCli ) {
			$this->cliOutputSuccess( $message );
		}
	}

	public function cliError($message) {
		if ( $this->isCli ) {
			$this->cliOutputError( $message );
		}
	}

	/**
	 * Triggered only when in http context
	 *
	 * @param $message string
	 */
	public function http( $message ) {
		if ( $this->isHttp ) {
			$this->httpOutput( $message );
		}
	}

	/**
	 * Outputs in cli context
	 *
	 * @param $message
	 */
	public function cliOutput( $message ) {
		\WP_CLI::line( $message );
	}

	public function cliOutputSuccess($message) {
		\WP_CLI::success( $message );
	}
	public function cliOutputError($message) {
		\WP_CLI::success( $message );
	}

	/**
	 * Outputs in http context
	 *
	 * @param $message
	 */
	public function httpOutput( $message ) {
		echo $message;
	}

	/**
	 * That function makes a bit of magic(&copy;)
	 * It allows us to call any of the above classes in cli context
	 *
	 * @param $name
	 * @param $arguments
	 */
	public static function __callStatic( $name, $arguments ) {
		$funName = preg_replace( '/(Static)$/', '', $name );
		call_user_func_array( [ ( new Output() ), $funName ], $arguments );
	}

	/**
	 * End to use invoke in static context
	 *
	 * @param $message
	 */
	public static function __( $message ) {
		$out = new self();
		$out($message);
	}
}