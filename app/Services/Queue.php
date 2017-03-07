<?php
namespace AgreableCatfishImporterPlugin\Services;

use \Illuminate\Queue\Capsule\Manager;
// use \Illuminate\Queue\Capsule\Manager;
// use \Illuminate\Queue\Worker;

class Queue extends Manager {
  /**
   * Setup queue connection variables on class instantiation
   */
  public function __construct() {

   parent::__construct();

   // Connect to the AWS SQS Queue
   // Bind required subclasses
   self::getContainer()->bind('encrypter', function() {
   	return new \Illuminate\Encryption\Encrypter(getenv('APP_KEY'), 'AES-256-CBC');
   });
   self::getContainer()->bind('request', function() {
   	return new Illuminate\Http\Request();
   });

   // Connect with credentials from .env
   self::addConnection([
       'driver' => 'sqs',
       'key'    => getenv('AWS_KEY'),
       'secret' => getenv('AWS_SECRET'),
       'queue'  => getenv('AWS_SQS_QUEUE'),
       'region' => getenv('AWS_SQS_REGION')
   ]);

   // Set class to be globally accessable via Queue::
   self::setAsGlobal();

  }

  public function pop() {
    die('catch pop');
  }
  /**
   * Directly access the next
   */
  // public static function popNextJob() {
  //   // die('hello');
  //   die(var_dump('pop next job', Worker::getNextJob()));
  //
  // }

}
