<?php
namespace AgreableCatfishImporterPlugin\Services;

use \WP_CLI;
use AgreableCatfishImporterPlugin\Services\Sync;

use Illuminate\Queue\Jobs\SqsJob as Job;
use \Illuminate\Queue\Worker as QueueWorker;

class Worker extends QueueWorker {

  /**
   * Override the process queue function to call function within Sync, not fire() function within class as Laravel works.
   */

  public $cli = false;

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
    if ($maxTries > 0 && $job->attempts() > $maxTries) {
      return $this->logFailedJob($connection, $job);
    }

    try {
      // Custom Job calling code for Catfish
      $data = json_decode($job->getRawBody(), true);

      $function = $data['job'];
      $payload = $data['data'];

      if($this->cli) {
        WP_CLI::line('Processing job '.$function);
      }

      // Call the queued function in the Sync Class
      Sync::$function($data, $payload);

      if($this->cli) {
        WP_CLI::success('Action complete');
      }

      // Delete the job from the queue once
      $job->delete();

      return ['job' => $job, 'failed' => false];
    } catch (Exception $e) {
      $this->handleJobException($connection, $job, $delay, $e);
      if($this->cli) {
        var_dump($e);
        WP_CLI::error('Exception Completing Action');
      }
    } catch (Throwable $e) {
      $this->handleJobException($connection, $job, $delay, $e);
      if($this->cli) {
        var_dump($e);
        WP_CLI::error('Exception Completing Action');
      }
    }
  }

  // Illuminate version of this function:
  // public function process($connection, Job $job, $maxTries = 0, $delay = 0)
  // {
  //     if ($maxTries > 0 && $job->attempts() > $maxTries) {
  //         return $this->logFailedJob($connection, $job);
  //     }
  //
  //     try {
  //         $this->raiseBeforeJobEvent($connection, $job);
  //
  //         var_dump('after raise', $job);
  //         // First we will fire off the job. Once it is done we will see if it will be
  //         // automatically deleted after processing and if so we'll fire the delete
  //         // method on the job. Otherwise, we will just keep on running our jobs.
  //         $job->fire();
  //
  //         die(var_dump('final', $job));
  //
  //         $this->raiseAfterJobEvent($connection, $job);
  //
  //         return ['job' => $job, 'failed' => false];
  //     } catch (Exception $e) {
  //         $this->handleJobException($connection, $job, $delay, $e);
  //     } catch (Throwable $e) {
  //         $this->handleJobException($connection, $job, $delay, $e);
  //     }
  // }

  /**
   * Purge queue function for tests primarily
   */
  public function purge($connection, $queue)
  {
    $count = 0;
    $connection = $this->manager->connection($connection);
    while ($job = $connection->pop($queue)) {
      $job->delete();
      $count++;
    }
    return $count;
  }

}
