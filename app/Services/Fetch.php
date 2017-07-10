<?php


namespace AgreableCatfishImporterPlugin\Services;


use Croissant\App;
use Croissant\DI\Interfaces\CatfishLogger;
use Sunra\PhpSimple\HtmlDomParser;

class Fetch {

	/**
	 * @var string
	 */
	private $url;
	/**
	 * @var CatfishLogger
	 */
	private $_logger;

	public function __construct( $url ) {
		$this->url     = $url;
		$this->_logger = App::get( CatfishLogger::class );
	}

	private function get() {

		$curl = curl_init();
		$url  = $this->getPreparedUrl();
		curl_setopt_array( $curl, array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "GET",
			CURLOPT_HTTPHEADER     => array(
				"Host: www.shortlist.com"
			),
		) );

		$response = curl_exec( $curl );
		$err      = curl_error( $curl );

		curl_close( $curl );

		if ( $err ) {
			$this->error( "cURL Error #:" . $err . ' while processing ' . $url );
			Throw new \Exception( "cURL Error #:" . $err . ' while processing ' . $url );
		}
		$this->debug( 'Successfully performed request to ' . $url );

		return $response;
	}

	/**
	 * @return string
	 */
	private function getPreparedUrl() {
		return str_replace(
			[ 'www.shortlist.com', 'www.stylist.com' ],
			[ 'origin.shortlist.com', 'origin.stylist.com' ],
			$this->url );
	}

	/**
	 * @param $url
	 * @param bool $asArray
	 *
	 * @return \stdClass|[]|null|bool|int
	 * @throws \Exception
	 */
	public static function json( $url, $asArray = false ) {
		$self       = new self( $url );
		$dataString = $self->get();
		$data       = json_decode( $dataString, $asArray );
		if ( $data === null && json_last_error() != JSON_ERROR_NONE ) {
			throw new \Exception( 'It seems like ' . $url . ' is not a valid json' );
		}

		return $data;
	}

	public static function xml( $url ) {
		$self       = new self( $url );
		$dataString = $self->get();
		$data       = HtmlDomParser::str_get_html( $dataString );

		if ( $data === false ) {
			throw new \Exception( 'It seems like ' . $url . ' is not a valid xml. CHeck if MAX_FILE_SIZE is not too small' );
		}

		return $data;
	}

	public function error( $message ) {
		$this->_logger->error( $message );
	}

	public function debug( $message ) {
		$this->_logger->debug( $message );
	}
}