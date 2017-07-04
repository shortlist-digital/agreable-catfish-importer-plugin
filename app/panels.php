<?php


add_action( 'admin_menu', function () {
	add_options_page( 'Catfish importer', 'Catfish Importer', 'manage_options', 'catfish_importer', function () {
		return include __DIR__ . '/views/sync.php';
	} );
} );
