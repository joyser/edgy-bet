<?
	require_once('../config.php');
	require_once('debug.php');
	
	
	$BetfairMarket = new BetfairMarket;
	$BetfairMarkets = $BetfairMarket->find(array('conditions'=>array(
		'betfairId'=>'1.122995870'
	)));
	
	
	valueFinder::getEachWayArbitrageRaces( $BetfairMarkets );
	
	
