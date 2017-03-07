<?php
namespace AgreableCatfishImporterPlugin\Services;

// use AgreableCatfishImporterPlugin\Services\Job; // Extend and replace the resolveAndFire function within the Illuminate Queue Job class
// use \Illuminate\Contracts\Queue\Job;
// use Illuminate\Contracts\Queue\Job

class QueueJob extends Job {

  // public function job

  /**
   * A process function supporting the Agreable structure (Not Laravels)
   */
  public function resolveAndFire(array $payload) {

    die(var_dump('resolveAndFire??'));

  }

}
