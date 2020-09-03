<?
	

	//Require necessary files..
	require_once('includes.php');
	
	//Global view
	$globalView = new globalView;
	
	//Check if the user is logged in...
	$UserSession = new UserSession;
	
	$UserSession->logPageLoad();
	
	//Check if the user is trying to login..
	if( $_GET['action']=='login'){
		
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		$UserSession->login($username, $password);
	}
	
	//Check if user is trying to logout..
	if($_GET['action']=='logout'){
				
		$UserSession->logout();
	}
	
	if( !$UserSession->isLoggedIn() ){
		
		$globalView->addView('login');
		$globalView->printPage();
		exit();
	}
	
	//Check if user is active
	if( $UserSession->User->blocked ){
		sleep(10);
		exit();
	}
	
	//Check if the user is trying to switch from or to mobile
	if( $_GET['action']=="toMobile") {
		
		
		$UserSession->toMobile();
		
	}else if( $_GET['action']=="toDesktop"){
		
		$UserSession->toDesktop();
	}
	
	
	$betfairHelper = new betfairHelper;
	
	
	$globalView->addView('header');
	
	//Get all horse events with win markets that start today..
	$BetfairEvent = new BetfairEvent;
	$lookBack = new DateTime('-12hours');
	$midnightTwoDays = new DateTime('midnight');
	$midnightTwoDays = $midnightTwoDays->modify('+2days');
	$fiveMinutesAgo = new DateTime('-5minutes');
	
	
	//Check if the events and amrkets are being manually updated...
	if($_GET['updateEvents']=="true"){
		
		//Update horse racing events
		$betfairHelper->updateVenues();
		$betfairHelper->updateEvents(7);
		
		//Get approriate events and update their markets..
		$BetfairEvents = $BetfairEvent->find(array('conditions'=>array(
			'startTime>'=>$lookBack->format('Y-m-d H:i:s'),
			'startTime<'=>$midnightTwoDays->format('Y-m-d H:i:s'),
			'hasWinMarket'=>1,
			'betfairEventTypeId'=>7,
			'OR'=>array(array('country'=>'IE'),array('country'=>'GB'),array('country'=>'AE'),array('country'=>'FR'))
		) ) );
		
		foreach( $BetfairEvents as $index=>$BetfairEvent ){
		
			$betfairHelper->updateEventMarkets($BetfairEvent->betfairId);
		}
	}
	
	
	//Process for getting events for the sidebar..
	$BetfairEvents = $BetfairEvent->find(array('conditions'=>array(
		'startTime>'=>$lookBack->format('Y-m-d H:i:s'),
		'startTime<'=>$midnightTwoDays->format('Y-m-d H:i:s'),
		'hasWinMarket'=>1,
		'betfairEventTypeId'=>7,
		'BetfairMarket.type'=>'WIN',
		'BetfairMarket.startTime>'=>$fiveMinutesAgo->format('Y-m-d H:i:s'),
		'OR'=>array(array('country'=>'IE'),array('country'=>'GB'),array('country'=>'AE'))
	) , 'orderBy'=>'startTime ASC') );
	
	//BetfairMarkets
	$BetfairMarket = new BetfairMarket;
	
	
	
	foreach( $BetfairEvents as $index=>$BetfairEvent ){
		
	
		//Sort the markets in the events..
		$BetfairEvent->sortHasMany('BetfairMarket','startTime', 'time');
		
		//Get all win markets for this event..
		$BetfairMarkets = $BetfairEvent->BetfairMarket;
		
		//Check that there is still active markets..
		if( count($BetfairMarkets) < 1 ){
			
			unset($BetfairEvents[$index]);
		}else{
			
			foreach( $BetfairMarkets as $marketIndex=>$BetfairMarket ){
				
				$shortTime = explode(" ", $BetfairMarket->startTime);
				$BetfairMarkets[$marketIndex]->shortTime = substr($shortTime[1], 0, 5);
			}
			
			$BetfairEvent->Markets = $BetfairMarkets;
		}
		
	}
	
	
	$sideBarVariables = array('BetfairEvents'=>$BetfairEvents, 'userName'=>$UserSession->User->name, 'userIpAddress'=>$UserSession->ipAddress);
	$globalView->addView('sidebar', $sideBarVariables );
	
	
	//Get the controller and action..
	$controller = $_GET['controller'];
	
	//Get all the sent variables
	$arguments = array();
	foreach ($_GET as $param_name => $param_val) {
    	
    	$arguments[$param_name] = $param_val;
	}
	
	foreach ($_POST as $param_name => $param_val) {
    	
    	$arguments[$param_name] = $param_val;
	}
	
	if( $controller != ""){
		
		$controller.='Controller';
		$controller = new $controller;
		$action = $_GET['action'];
		if( $action == ""){
			
			$action="index";
		}
		
		$controller->$action($arguments);
	}
	
	
	
	
	$globalView->addView('footer');
	
	
	$globalView->printPage();