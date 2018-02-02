<?php

define('PROFILER_APP', true);
include( __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

$old = PROFILE_DIR . DS . @$_GET['old'];
$new = PROFILE_DIR . DS . @$_GET['new'];

try {

	if( !file_exists($old) || !is_file($old) ) {
		Throw new Exception('Old file is not a valid file to rename');
	}

	if( file_exists($new) ) {
		Throw new Exception('New file already exists');
	}

	rename( $old, $new );

	if( isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) ) {
		header('location:' . $_SERVER['HTTP_REFERER'] );
	} else {
		$message = "This was successful!";
	}

} catch( Exception $e ) {
	$message = "ERROR! " . $e->getMessage();

}?><!doctype html>
<html>
<head>

</head>
<body>
<p><?=$message;?></p>
<a href="index.php">Back to main page</a>.
</body>
</html>


