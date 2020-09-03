<?
	
	require_once 'includes.php';
	
	$seconds = date_offset_get(new DateTime);
	print $seconds / 3600;