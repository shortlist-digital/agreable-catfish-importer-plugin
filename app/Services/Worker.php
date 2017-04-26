<?php
namespace AgreableCatfishImporterPlugin\Services;

use \WP_CLI;
use AgreableCatfishImporterPlugin\Services\Sync;

use Illuminate\Queue\Jobs\SqsJob as Job;
use \Illuminate\Queue\Worker as QueueWorker;

use Exception;

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
      // Parameters:
      // $data  the full json object from the queue including the function name and payload
      // $payload  the spefic payload data for this action
      // $cli  should WP_CLI console output be shown
      $response = Sync::$function($data, $payload, $this->cli);

      // Pass on the slug to show in log file
      $log_identifier = (isset($response->log_identifier)) ? $response->log_identifier : '' ;

      if($this->cli) {
        WP_CLI::line($log_identifier.'Success: Worker action complete');
      }

      // Delete the job from the queue once
      $job->delete();

      if($this->cli) {
        WP_CLI::line($log_identifier.'Success: Task deleted from queue');
      }

      return ['job' => $job, 'failed' => false];

    } catch (Exception $e) {

      // Try to release the job back into the queue to try again later
      $this->handleJobException($connection, $job, $delay, $e);

      // Show the error to the cli
      if($this->cli) {
        WP_CLI::line($log_identifier.'Error: Task released back to queue');
        WP_CLI::error($log_identifier.$e->getMessage());
      }
      // Send handled error to BugSnag as well..
      $bugsnag = Bugsnag\Client::make(getenv('BUGSNAG_API_KEY'));
      $bugsnag->notifyException($e);

    // Repeated from above for Illuminate's error handline namespace
    } catch (Throwable $e) {
      // Try to release the job back into the queue to try again later
      $this->handleJobException($connection, $job, $delay, $e);

      // Show the error to the cli
      if($this->cli) {
        WP_CLI::line($log_identifier.'Error: Task released back to queue');
        WP_CLI::error($log_identifier.$e->getMessage());
      }
      trigger_error($log_identifier.$e->getMessage(), E_USER_ERROR);
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
  public function purge($connection, $queue, $cli) {

    if($cli) {
      $startTime = microtime(true);
    }

    $count = 0;
    $connection = $this->manager->connection($connection);

    while ($job = $connection->pop($queue)) {
      $job->delete();
      $count++;
      if($cli) {
        WP_CLI::line("Purged " . $count . " queue items.");
      }
    }

    // Show how long it took to process the category to queue
    if($cli) {
      $endTime = microtime(true);
      $time = $endTime - $startTime;
      WP_CLI::line("Purge took " . $time . " seconds.");
    }

    return $count;
  }

}
