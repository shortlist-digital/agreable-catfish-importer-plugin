<?php
namespace AgreableCatfishImporterPlugin\Services;

use \Illuminate\Queue\Capsule\Manager;

use Exception;

class Queue extends Manager {
  /**
   * Setup queue connection variables on class instantiation
   */
  public function __construct() {

   parent::__construct();

   // Warn devs to setup encryption key and environment vars for AWS
   if( !getenv('ILLUMINATE_ENCRYPTOR_KEY') ) {
     throw new Exception("You need to set a AES-256-CBC compatible encryption key for you ILLUMINATE_ENCRYPTOR_KEY environment variable.");
   }
   if( !getenv('AWS_SQS_KEY') || !getenv('AWS_SQS_SECRET') || !getenv('AWS_SQS_CATFISH_IMPORTER_QUEUE') || !getenv('AWS_SQS_CATFISH_IMPORTER_REGION') ) {
     throw new Exception("You need to set your AWS environment variables in .env.");
   }

   // Connect to the AWS SQS Queue
   // Bind required subclasses
   self::getContainer()->bind('encrypter', function() {
   	return new \Illuminate\Encryption\Encrypter(getenv('ILLUMINATE_ENCRYPTOR_KEY'), 'AES-256-CBC');
   });
   self::getContainer()->bind('request', function() {
   	return new Illuminate\Http\Request();
   });

   // Connect with credentials from .env
   self::addConnection([
       'driver' => 'sqs',
       'key'    => getenv('AWS_SQS_KEY'),
       'secret' => getenv('AWS_SQS_SECRET'),
       'queue'  => getenv('AWS_SQS_CATFISH_IMPORTER_QUEUE'),
       'region' => getenv('AWS_SQS_CATFISH_IMPORTER_REGION')
   ]);

   // Set class to be globally accessable via Queue::
   self::setAsGlobal();

  }

}
