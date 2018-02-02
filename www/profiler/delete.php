<?php

define('PROFILER_APP', true);
include( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php' );

$files = $_GET['files'];

if( !$files || empty($files) ) {
	$message = "No files specified";
} else {

	$success = array();
	$error = array();

	foreach ( $files as $file ) {
		$filePath = PROFILE_DIR . DS . $file;

		if ( file_exists( $filePath ) && unlink( $filePath ) ) {
			$success[] = $file;
		} else {
			$error[] = $file;
		}
	}

	$actions = array();
	if( count($success) )
		$actions[] = count($success) . ' files deleted';
	if( count($error) )
		$actions[] = count($error) . ' files could not be deleted';

	$message = implode(', but ', $actions);

	if( !count($errors) && isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ) {
		header('location:' . $_SERVER['HTTP_REFERER'] );
	}


}
?><!doctype html>
<html>
<head>

</head>
<body>
	<p><?=$message;?></p>
	<p><a href="index.php?detailed=1">Return</a>.</p>
</body>
</html>