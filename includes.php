<?
	//Set timezone 
	date_default_timezone_set('Europe/Dublin');
	
	
	//Check that the config file is present..
	if( !file_exists('config.php')){
		
		echo "No config file found";
		exit();
	}
	
	if($_GET['debug']=="true"){
		
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
	}
	
	//start session
	session_start();
	
	date_default_timezone_set('Europe/Dublin');
	require_once 'config.php';
	require_once 'Model.class.php';
	require_once 'isMobileDevice.php';
	require_once 'betfairAPI.class.php';
	require_once 'User.class.php';
	require_once 'Login.class.php';
	require_once 'UserSession.class.php';
	require_once 'betfairHelper.class.php';
	require_once 'objectHelper.class.php';
	require_once 'BetfairVenue.class.php';
	require_once 'BetfairEvent.class.php';
	require_once 'BetfairMarket.class.php';
	require_once 'Bet.class.php';
	require_once 'PageLoad.class.php';
	require_once 'BetType.class.php';
	require_once 'Bookmaker.class.php';
	require_once 'Bet.controller.php';
	require_once 'BetfairRunner.class.php';
	require_once 'scraper.class.php';
	require_once 'oddsChecker.class.php';
	require_once 'racingPost.class.php';
	require_once 'globalView.class.php';
	require_once 'Race.controller.php';
	require_once 'valueFinder.class.php';
	require_once 'AutomatedTask.class.php';
	require_once 'ValueBet.class.php';
	require_once 'RunnerSnapShot.class.php';
	require_once 'SystemVariable.class.php';
	require_once 'ValueBet.controller.php';
	require_once 'Tipster.class.php';
	require_once 'Tip.class.php';
	require_once 'Odd.class.php';
	require_once 'beeper.class.php';
	require_once 'automator.class.php';
	require_once 'User.controller.php';
	require_once 'debug.php';