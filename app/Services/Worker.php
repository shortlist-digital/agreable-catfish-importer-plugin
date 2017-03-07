<?php
namespace AgreableCatfishImporterPlugin\Services;

use \Illuminate\Queue\Worker as QueueWorker;
// use \Illuminate\Queue\QueueManager;

// use AgreableCatfishImporterPlugin\Services\QueueManager; // Extend and replace the resolveAndFire function within the Illuminate Queue Job class
// use AgreableCatfishImporterPlugin\Services\Job;
// use AgreableCatfishImporterPlugin\Services\Job;

// use AgreableCatfishImporterPlugin\Services\SqsJob as Job; // Extend SqsJob Contract -> Abstract class chain
use Illuminate\Contracts\Queue\Job;

use AgreableCatfishImporterPlugin\Services\QueueTest; // Make Queue namespace available to the Worker class

class Worker extends QueueWorker {

  /**
   * Setup queue connection variables on class instantiation
   */

   function pop($connectionName, $queue = NULL, $delay = 0, $sleep = 3, $maxTries = 0) {
    //  die('catch pop');
     // die('pop');
       // try {
            // $this->setManager(new QueueManager); // Set QueueManager to extention in current namespace so that we can define the SqsObject in the current namespace
           $connection = $this->manager->connection($connectionName);

           // die(var_dump($connection));

           $job = $this->getNextJob($connection, $queue);

           var_dump($job);

           // If we're able to pull a job off of the stack, we will process it and
           // then immediately return back out. If there is no job on the queue
           // we will "sleep" the worker for the specified number of seconds.
           if (! is_null($job)) {
               return $this->process(
                   $this->manager->getName($connectionName), $job, $maxTries, $delay
               );
           }
       // } catch (Exception $e) {
       //     if ($this->exceptions) {
       //         $this->exceptions->report($e);
       //     }
       // }

       $this->sleep($sleep);

       return ['job' => null, 'failed' => false];
   }

   /**
    * Get the next job from the queue connection.
    *
    * @param  \Illuminate\Contracts\Queue\Queue  $connection
    * @param  string  $queue
    * @return \Illuminate\Contracts\Queue\Job|null
    */
   protected function getNextJob($connection, $queue)
   {
       if (is_null($queue)) {
           return $connection->pop(); // XXX poping from SqsQueue
       }

       foreach (explode(',', $queue) as $queue) {
           if (! is_null($job = $connection->pop($queue))) {
               return $job;
           }
       }
   }

  /**
   * Create a new queue worker from Illuminate library
   *
   * @param  \Illuminate\Queue\QueueManager  $manager
   * @param  \Illuminate\Queue\Failed\FailedJobProviderInterface  $failer
   * @param  \Illuminate\Contracts\Events\Dispatcher  $events
   * @return void
   */
  public function __construct($manager, $failer = null, $events = null)
  {
    parent::__construct($manager, $failer = null, $events = null);
  }

  /**
   * Process a given job from the queue.
   *
   * @param  string  $connection
   * @param  \Illuminate\Contracts\Queue\Job  $job
   * @param  int  $maxTries
   * @param  int  $delay
   * @return array|null
   *
   * @throws \Throwable
   */
  public function process($connection, Job $job, $maxTries = 0, $delay = 0)
  {

    $data = json_decode($job->getRawBody(), true);
    die(var_dump('Raw Body', $data));

  var_dump('start process', $job);
      if ($maxTries > 0 && $job->attempts() > $maxTries) {
          return $this->logFailedJob($connection, $job);
      }
      // die('process');

      // die(var_dump($job->fire()));
      // try {
          $this->raiseBeforeJobEvent($connection, $job);

          var_dump('after raise', $job);
          // First we will fire off the job. Once it is done we will see if it will be
          // automatically deleted after processing and if so we'll fire the delete
          // method on the job. Otherwise, we will just keep on running our jobs.
          $job->fire();

          die(var_dump('final', $job));

          $this->raiseAfterJobEvent($connection, $job);

          return ['job' => $job, 'failed' => false];
      // } catch (Exception $e) {
      //     $this->handleJobException($connection, $job, $delay, $e);
      // } catch (Throwable $e) {
      //     $this->handleJobException($connection, $job, $delay, $e);
      // }
  }

  /**
   * Raise the before queue job event.
   *
   * @param  string  $connection
   * @param  \Illuminate\Contracts\Queue\Job  $job
   * @return void
   */
  protected function raiseBeforeJobEvent($connection, Job $job)
  {
      if ($this->events) {
          $data = json_decode($job->getRawBody(), true);

          $this->events->fire(new Events\JobProcessing($connection, $job, $data));
      }
  }

  /**
   * Raise the after queue job event.
   *
   * @param  string  $connection
   * @param  \Illuminate\Contracts\Queue\Job  $job
   * @return void
   */
  protected function raiseAfterJobEvent($connection, Job $job)
  {
      if ($this->events) {
          $data = json_decode($job->getRawBody(), true);

          $this->events->fire(new Events\JobProcessed($connection, $job, $data));
      }
  }



  /**
   * Pass process job through but use the Catfish Job class extension
   */
  // function process($connection, $job, $maxTries = 0, $delay = 0) {
  //
  //   // $job = new SqsJob($job);
  //   // var_dump($job->fire());
  //
  //   parent::process($connection, $job, $maxTries, $delay);
  // }
  /**
   * Directly access the next
   */
  // public function popNextJob($connectionName, $queue) {
  //
  //   $connection = $this->manager->connection(null);
  //
  //   // die(var_dump($connection));
  //
  //   $job = $this->getNextJob($connection, $queue);
  //
  //   die(var_dump($job));
  //
  //   $test = new Job($job);
  //
  //   die(var_dump($test));
  //
  //   // $manager = new QueueManager(true);
  //   //
  //   // $this->setManager($manager);
  //   //
  //   // die(var_dump($this->manager->connection($connectionName)));
  //   // // Adapted from Queue function
  //   // $connection = $this->manager->connection($connectionName);
  //   //
  //   // $job = $this->getNextJob($connection, $queue);
  //   //
  //   // die(var_dump($job));
  //
  //   // die(var_dump('pop next job', self::pop('default', 'default')));
  //
  //   // $connection = $this->manager->connection();
  //   // die(var_dump($connection));
  //   // die(var_dump('pop next job', self::getNextJob($connection, 'default')));
  //
  // }

}
