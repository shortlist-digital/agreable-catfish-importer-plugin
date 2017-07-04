<?php

namespace AgreableCatfishImporterPlugin\Controllers;

use AgreableCatfishImporterPlugin\Services\Sync;

class SyncController {

	public function retry() {
		// Have to use a query string, as wordpress tries to
		// parse the URL if it's in a /retry/{id} param
		$catfish_url = $_GET['url'];

		Sync::queueUrl( $catfish_url );
		header( 'Location: ' . $_SERVER['HTTP_REFERER'] );
	}

}
