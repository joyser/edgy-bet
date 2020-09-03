<?
	require_once 'config.php';

	$betfairAPI = new betfairAPI;
	$betfairHelper = new betfairHelper;
	
	
	
	
	//$horseRacing = $betfairAPI->extractSportEvent('Horse Racing') ;
	//$events = $betfairAPI->getEvents( 7 );
	//$markets = $betfairAPI->getMarkets( 27664853, "WIN" );
	//$book = $betfairAPI->getMarketBook(1.122714265);
	//$venues = $betfairAPI->getVenues();
	
	
	//This function should be executed at the start of every day...
	//$betfairHelper->updateVenues();
	
	//Function should be called daily (or when called manually)
	//$betfairHelper->updateEvents(7);
	
	$betfairHelper->updateEventMarkets(27672124);
	
	//logness($venues, orange );
	customExit();