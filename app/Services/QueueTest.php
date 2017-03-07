<?php

namespace AgreableCatfishImporterPlugin\Services;

use \Illuminate\Container;

class QueueTest extends Container {
    public function fire($job, $data) {
        echo "importing: {$data['url']}" . PHP_EOL;
        die('made it...');
    }
}
