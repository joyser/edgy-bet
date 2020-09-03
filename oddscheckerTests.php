<?
	
	require_once 'includes.php';
	
	
	$DateTime = new DateTime('2016-04-15 16:55');
	$DateTime2 = new DateTime('2016-04-15 18:55');
	$oddsChecker = new oddsChecker;
	
	
	$races = array(
		array(
			'venueName'=>'ballinrobe',
			'time'=>$DateTime,
			'country'=>'GB'
		),
		array(
			'venueName'=>'ballinrobe',
			'time'=>$DateTime,
			'country'=>'GB'
		)
	);
	$odds = $oddsChecker->getRacePrices($races);
	
	
	logness($odds, 'orange');
	customExit();