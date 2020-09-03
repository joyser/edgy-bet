<?

	require_once 'betfairAPI.class.php';
	
	
	$betfairAPI = new betfairAPI;
	print_r( $betfairAPI->getAllEventTypes() );