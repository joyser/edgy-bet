<?
	
	//This script is run every 60 seconds
	//It should stay awake for 60 seconds and find value every 10 seconds using the automator functions
	
	//set this as max
	ini_set('max_execution_time', 90);
	
	//Start time as a float..
	$startTime = microtime(true);
	
	//How often to try and find value...
	$repeatTime = 5;
	
	//Require necessary files..
	require_once('includes.php');
		
	$automator = new automator;
	
	$count=0;

	//Repeat while under 60 seconds.. (or 5 executions)
	while((microtime(true)-$startTime)<60 && $count < 12){
		
		//Find value
		$automateStart = microtime(true);
		$automator->findValue();
		$execTime = microtime(true)-$automateStart;
		
		//Sleep for the repeat interval
		$microSecondsSleep = round(($repeatTime-$execTime)*1000000);
		
		logness($execTime);
		
		$count++;
		
		usleep($microSecondsSleep);
		
	}
	
	customExit();
	