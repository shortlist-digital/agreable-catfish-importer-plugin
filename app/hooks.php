<?php

//Adds reimport button on listing
//Requires admin.js

add_filter( 'post_row_actions', function ( $actions, $post ) {

	$actions['re_import'] = "<a title='Re-import from Catfish' class='reicport js-catfish-reimport' href='#' data-id='{$post->ID}'>Reimport</a>";
	return $actions;
}, 10, 2 );
