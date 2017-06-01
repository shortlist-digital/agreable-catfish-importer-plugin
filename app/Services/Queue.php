<?php

namespace AgreableCatfishImporterPlugin\Services;

use AgreableCatfishImporterPlugin\Services\Context\Output;
use Aws\Sqs\SqsClient;

//TODO::Move that to singleton|factory or DI
class Queue {

	/**
	 * @var SqsClient|bool
	 */
	private static $queue = false;
	/**
	 * @var string|bool
	 */
	private static $queueUrl = false;

	/**
	 * Setup queue connection variables on class instantiation
	 */
	public static function init() {
		if ( self::$queue ) {
			return;
		}
		// Warn devs to setup environment vars for AWS
		if ( ! getenv( 'AWS_SQS_KEY' ) || ! getenv( 'AWS_SQS_SECRET' ) || ! getenv( 'AWS_SQS_CATFISH_IMPORTER_QUEUE' ) || ! getenv( 'AWS_SQS_CATFISH_IMPORTER_REGION' ) ) {
			throw new \Exception( "You need to set your AWS environment variables in .env." );
		}

		self::$queueUrl = getenv( 'AWS_SQS_CATFISH_IMPORTER_QUEUE' );

		self::$queue = SqsClient::factory( array(
			'key'    => getenv( 'AWS_SQS_KEY' ),
			'secret' => getenv( 'AWS_SQS_SECRET' ),
			'region' => getenv( 'AWS_SQS_CATFISH_IMPORTER_REGION' )
		) );

	}


	/**
	 * Push to Queue
	 */
	public static function push( $data, $delay = 0 ) {
		self::init();
		$response = self::$queue->sendMessage( array(
			'QueueUrl'     => self::$queueUrl,
			'MessageBody'  => json_encode( $data ),
			'DelaySeconds' => $delay,
		) );

		return $response;
	}

	/**
	 * Pop from Queue
	 */
	public static function pop() {

		self::init();
		// Custom Job calling code for Catfish
		$response = self::$queue->receiveMessage( array(
			'QueueUrl' => self::$queueUrl
		) );

		// If the queue is empty exit
		if ( ! isset( $response['Messages'][0] ) ) {

			Output::cliStatic( 'The queue is empty, sleeping.' );
			//TODO:: Is that required?
			sleep( 10 );

			return;
		}

		$message = $response['Messages'][0];
		$data    = json_decode( $message['Body'], true );

		$function = $data['job'];
		$payload  = $data['data'];


		Output::cliStatic( 'Processing job ' . $function );


		// Call the queued function in the Sync Class
		// Parameters:
		// $data  the full json object from the queue including the function name and payload
		// $payload  the spefic payload data for this action
		// $cli  should WP_CLI console output be shown
		$response = Sync::$function( $data, $payload );

		// Pass on the slug to show in log file
		$log_identifier = ( isset( $response->log_identifier ) ) ? $response->log_identifier : '';


		Output::cliStatic( $log_identifier . 'Success: Worker action complete' );


		// Delete the job from the queue once complete without exception
		$delete = self::$queue->deleteMessage( array(
			// QueueUrl is required
			'QueueUrl'      => self::$queueUrl,
			// ReceiptHandle is required
			'ReceiptHandle' => $message['ReceiptHandle']
		) );


		Output::cliStatic( $log_identifier . 'Success: Task deleted from queue' );

		//TODO::what is job in here
		return [ 'job' => $job, 'failed' => false ];


	}

	/**
	 * Purge Queue
	 */
	public static function purge() {
		self::init();

		// Purge Queue
		return self::$queue->purgeQueue( array(
			// QueueUrl is required
			'QueueUrl' => self::$queueUrl,
		) );
	}
}
