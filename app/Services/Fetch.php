<?php


namespace AgreableCatfishImporterPlugin\Services;


use Croissant\App;
use Croissant\DI\Interfaces\CatfishLogger;
use Sunra\PhpSimple\HtmlDomParser;

/**
 * This fixes the issues with htmldomparser not parsing large files
 */
if ( ! defined( 'MAX_FILE_SIZE' ) ) {
	define( 'MAX_FILE_SIZE', 600000000 );
}

/**
 * Class Fetch
 *
 * @package AgreableCatfishImporterPlugin\Services
 */
class Fetch {

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var
	 */
	private $cache;

	/**
	 * @var CatfishLogger
	 */
	private $_logger;

	/**
	 * @var array
	 */
	private static $memoryCache = [];


	/**
	 * Fetch constructor.
	 *
	 * @param $url
	 * @param $cache
	 */
	public function __construct( $url, $cache ) {
		$this->cache   = $cache;
		$this->url     = $url;
		$this->_logger = App::get( CatfishLogger::class );
	}

	/**
	 * @return mixed
	 * @throws \Exception
	 */
	private function get() {


		$curl = curl_init();
		$url  = $this->getPreparedUrl();
		$host = strpos( $this->url, 'stylist.co.uk' ) === false ? 'www.shortlist.com' : 'www.stylist.co.uk';
		curl_setopt_array( $curl, array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "GET",
			CURLOPT_HTTPHEADER     => array(
				"Host: " . $host
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

		$path = parse_url( $this->url )['path'];

		$escapedPath = implode( '/', array_map( function ( $segment ) {
			return rawurlencode( $segment );
		}, explode( '/', $path ) ) );

		return str_replace(
			[
				'www.shortlist.com',
				'http://shortlist.com',
				'www.stylist.co.uk',
				'http://stylist.co.uk',
				$path
			],
			[
				'origin.shortlist.com',
				'http://origin.shortlist.com',
				'origin.shortlist.com',
				'http://origin.shortlist.com',
				$escapedPath
			],
			$this->url );
	}

	/**
	 * This function seems ridiculous but because of caching requests should actually save us a lot of time.
	 */
	public function getCache() {

		if ( ! $this->cache ) {
			return false;
		}

		if ( isset( self::$memoryCache[ $this->url ] ) ) {
			return self::$memoryCache[ $this->url ];
		}

		return $this->getTransient();
	}


	/**
	 * @return mixed
	 */
	public function getTransient() {
		return get_transient( $this->getCacheKey() );
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	public function setCache( $data ) {
		$this->debug( 'Saved cache for ' . $this->url );

		return set_transient( $this->getCacheKey(), $data, 60 * 60 * 12 );
	}

	/**
	 * @return string
	 */
	public function getCacheKey() {
		return 'catfish_import_url_' . md5( $this->url );
	}

	/**
	 * @param $url
	 * @param bool $cache
	 *
	 * @return \stdClass|[]|null|bool|int
	 * @throws \Exception
	 */
	public static function json( $url, $cache = true ) {

		$self = new self( $url, $cache );

		$cache = $self->getCache();

		if ( $cache !== false ) {
			$self->debug( 'Fetching from cache ' . $self->url );

			return $cache;
		}
		$dataString = $self->get();
		$data       = json_decode( $dataString, false );
		if ( $data === null && json_last_error() != JSON_ERROR_NONE ) {
			throw new \Exception( 'It seems like ' . $url . ' is not a valid json' );
		}
		if ( $cache ) {
			self::$memoryCache[ $self->url ] = $data;
			$self->setCache( $data );
		}

		return $data;
	}

	/**
	 * @param $url
	 *
	 * @return \simplehtmldom_1_5\simple_html_dom
	 * @throws \Exception
	 * @internal param bool $cache
	 *
	 */
	public static function xml( $url ) {

		$self = new self( $url, false );

		$dataString = $self->get();
		$data       = HtmlDomParser::str_get_html( $dataString );

		if ( $data === false ) {
			throw new \Exception( 'It seems like ' . $url . ' is not a valid xml. CHeck if MAX_FILE_SIZE is not too small' );
		}

		return $data;
	}

	/**
	 * @param $message string
	 */
	public function error( $message ) {
		$this->_logger->error( $message );
	}

	/**
	 * @param $message string
	 */
	public function debug( $message ) {
		$this->_logger->debug( $message );
	}
}