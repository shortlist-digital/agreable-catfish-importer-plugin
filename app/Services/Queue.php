<?php
namespace AgreableCatfishImporterPlugin\Services;

use \WP_CLI;

use Aws\Sqs\SqsClient;

use Exception;

class Queue {

  public static $cli = false;

  /**
   * Setup queue connection variables on class instantiation
   */
  public function __construct() {

    // Warn devs to setup environment vars for AWS
    if( !getenv('AWS_SQS_KEY') || !getenv('AWS_SQS_SECRET') || !getenv('AWS_SQS_CATFISH_IMPORTER_QUEUE') || !getenv('AWS_SQS_CATFISH_IMPORTER_REGION') ) {
     throw new Exception("You need to set your AWS environment variables in .env.");
    }

    self::$queueUrl = getenv('AWS_SQS_CATFISH_IMPORTER_QUEUE');

    self::$queue = SqsClient::factory(array(
      'key' => getenv('AWS_SQS_KEY'),
      'secret' => getenv('AWS_SQS_SECRET'),
      'region'  => getenv('AWS_SQS_CATFISH_IMPORTER_REGION')
    ));

  }

  /**
   * Queue constant
   *
   * Constants to interact with the queue
   */
  private static $queue = false;
  private static $queueUrl = false;

  /**
   * Push to Queue
   */
  public static function push($data, $delay = 0) {
    $response = self::$queue->sendMessage(array(
        'QueueUrl'     => self::$queueUrl,
        'MessageBody'  => json_encode($data),
        'DelaySeconds' => $delay,
    ));

    return $response;
  }

  /**
   * Pop from Queue
   */
  public static function pop() {

    try {
      // Custom Job calling code for Catfish
      $response = self::$queue->receiveMessage(array(
        'QueueUrl' => self::$queueUrl
      ));

      // If the queue is empty exist
      if(!isset($response['Messages'][0])) {
        if(self::$cli) {
          WP_CLI::line('The queue is empty, sleeping.');
        }
        sleep(10);
        return;
      }

      $message = $response['Messages'][0];
      $data = json_decode($message['Body'], true);

      $function = $data['job'];
      $payload = $data['data'];

      if(self::$cli) {
        WP_CLI::line('Processing job '.$function);
      }

      // Call the queued function in the Sync Class
      // Parameters:
      // $data  the full json object from the queue including the function name and payload
      // $payload  the spefic payload data for this action
      // $cli  should WP_CLI console output be shown
      $response = Sync::$function($data, $payload, self::$cli);

      // Pass on the slug to show in log file
      $log_identifier = (isset($response->log_identifier)) ? $response->log_identifier : '' ;

      if(self::$cli) {
        WP_CLI::line($log_identifier.'Success: Worker action complete');
      }

      // Delete the job from the queue once complete without exception
      $delete = self::$queue->deleteMessage(array(
        // QueueUrl is required
        'QueueUrl' => self::$queueUrl,
        // ReceiptHandle is required
        'ReceiptHandle' => $message['ReceiptHandle']
      ));

      if(self::$cli) {
        WP_CLI::line($log_identifier.'Success: Task deleted from queue');
      }

      return ['job' => $job, 'failed' => false];

    } catch (Exception $e) {

      // Show the error to the cli
      if(self::$cli) {
        WP_CLI::line($log_identifier.'Error: Task released back to queue');
        WP_CLI::error($log_identifier.$e->getMessage());
      }
      // Send handled error to BugSnag as well..
      $this->get('bugsnag')->setReleaseStage(WP_ENV);
      $bugsnag = \Bugsnag\Client::make(getenv('BUGSNAG_API_KEY'));
      $bugsnag->notifyException($e);
      if(WP_ENV == 'development') { die($e->getMessage); }

    // Repeated from above for Illuminate's error handline namespace
    }
  }

  /**
   * Purge Queue
   */
  public static function purge() {
    // Purge Queue
    return self::$queue->purgeQueue(array(
      // QueueUrl is required
      'QueueUrl' => self::$queueUrl,
    ));
  }
}
