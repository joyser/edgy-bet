<?
	
	require_once 'includes.php';
	
	$deicmal = 2.89;
	
	$Odd = new Odd;
	$fractional = $Odd->getFractional($deicmal);
	
	logness($fractional);
	customExit();