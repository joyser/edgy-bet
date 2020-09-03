<?


	class betfairAPI{

		private $appKey = "";
		private $sessionKey;
		private $username = "";
		private $password = "";


		function __construct(){


			//Get the betfair sessionkey from database..
			$SystemVariable = new SystemVariable;
			$betfairSessionKey = $SystemVariable->get('betfairSessionKey');
			$betfairLastLogin = $SystemVariable->get('betfairLastLogin');

			$this->sessionKey = $betfairSessionKey->value;

			//If there has not been a login in an hour, get a new session key..
			if( ($betfairLastLogin->value+(60*60)) < time()){

				$this->login();
			}

		}

		//Function used to login to betfair
		function login(){


			$loginEndpoint= "https://identitysso.betfair.com/api/login";
			$cookie = "";

			$login = "true";
			$redirectmethod = "POST";
			$product = "home.betfair.int";
			$url = "https://www.betfair.com/";

			$fields = array
				(
					'username' => urlencode($this->username),
					'password' => urlencode($this->password),
					'login' => urlencode($login),
					'redirectmethod' => urlencode($redirectmethod),
					'product' => urlencode($product),
					'url' => urlencode($url)
				);

			//open connection
			$ch = curl_init($loginEndpoint);
			//url-ify the data for the POST
			$counter = 0;
			$fields_string = "&";

			foreach($fields as $key=>$value)
				{
					if ($counter > 0)
						{
							$fields_string .= '&';
						}
					$fields_string .= $key.'='.$value;
					$counter++;
				}

			rtrim($fields_string,'&');

			curl_setopt($ch, CURLOPT_URL, $loginEndpoint);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$fields_string);
			curl_setopt($ch, CURLOPT_HEADER, true);  // DO  RETURN HTTP HEADERS
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // DO RETURN THE CONTENTS OF THE CALL

			//execute post
			$result = curl_exec($ch);

			//echo $result;

			if($result == false)
				{
		   	 		echo 'Curl error: ' . curl_error($ch);
		   	 		exit;
				}

			else
				{
					$temp = explode(";", $result);
					$result = $temp[0];

					$end = strlen($result);
					$start = strpos($result, 'ssoid=');
					$start = $start + 6;

					$sessionKey = substr($result, $start, $end);

				}
			curl_close($ch);

			//save the cookie in the DB
			$SystemVariable = new SystemVariable;
			$SystemVariable->set('betfairSessionKey',$sessionKey);
			$SystemVariable->set('betfairLastLogin',time());


			$this->sessionKey = $sessionKey;

		}


		//Checks if the object is logged in or not..
		function isLoggedIn(){


		}

		function sportsApingRequest( $operation, $params ){

			//Setup curl
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, "https://api.betfair.com/exchange/betting/json-rpc/v1");
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		        'X-Application: ' . $this->appKey,
		        'X-Authentication: ' . $this->sessionKey,
		        'Accept: application/json',
		        'Content-Type: application/json'
		    ));
		    $postData = '[{ "jsonrpc": "2.0", "method": "SportsAPING/v1.0/' . $operation . '", "params" :' . $params . ', "id": 1}]';
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		    $this->debug('Post Data: ' . $postData);
		    $response = json_decode(curl_exec($ch));
		    $this->debug('Response: ' . json_encode($response));
			$this->debug($this->appKey.' '.$this->sessionKey);
		    curl_close($ch);
		    if (isset($response[0]->error)) {
		        echo 'Call to api-ng failed: ' . "\n";
		        echo  'Response: ' . json_encode($response);
		        exit(-1);
		    } else {
		        return $response;
		    }
		}

		//Returns all events (Sports)
		function getEventTypes(){

			//Call the curl function
		    $jsonResponse = $this->sportsApingRequest('listEventTypes', '{"filter":{}}');

		    //Return results
		    return $jsonResponse[0]->result;
		}

		//Extracts a certain sport from  event types..
		function extractSportEvent($sport, $allEventTypes = array()){

			if(empty($allEventTypes[0])){

				$allEventTypes = $this->getEventTypes();
			}

		    foreach ($allEventTypes as $eventType) {
		        if ($eventType->eventType->name == 'Horse Racing') {
		            return $eventType;
		        }
		    }
		}

		//Get the events for a particular $eventTypeId
		function getEvents( $eventTypeId, $marketType =""  ){
		    $params = '{"filter":{"eventTypeIds":["' . $eventTypeId . '"],
		    			"marketTypeCodes":["' . $marketType . '"]}}';
		    $jsonResponse = $this->sportsApingRequest( 'listEvents', $params);
		    return $jsonResponse[0]->result;
		}

		//Get all markets for an event.. (sorted earliest first)
		function getMarkets( $eventId, $marketType ="" ){

			$params = '{"filter":{"eventIds":["' . $eventId . '"],
		              "marketStartTime":{"from":"' . date('c') . '"},
		              "marketTypeCodes":["' . $marketType . '"]},
		              "sort":"FIRST_TO_START",
		              "maxResults":"99",
		              "marketProjection":["RUNNER_DESCRIPTION","MARKET_DESCRIPTION","RUNNER_METADATA"]}';
		    //logness($params);
		    $jsonResponse = $this->sportsApingRequest('listMarketCatalogue', $params);
		    return $jsonResponse[0]->result;

		}

		//Gets the market book of a race
		function getMarketBook( $marketId ){

			//Check if just a marketId was given
			if(!is_array($marketId)){

				$marketId = array($marketId);
			}

			//Max number of markets is 40 from betfair API
			$overFlowMarketBooks = array();
			if(count($marketId)>29){

				//Use recursion to get the other market data
				$overFlowMarketBooks = $this->getMarketBook( array_slice($marketId, 30));

				if(!is_array($overFlowMarketBooks))
					$overFlowMarketBooks = array($overFlowMarketBooks);
			}

			//Implode the first 40 market Ids
			$marketIdString = implode('","', array_slice($marketId, 0, 30));

		    $params = '{"marketIds":["' . $marketIdString . '"], "priceProjection":{"priceData":["EX_BEST_OFFERS"]}}';

		    $jsonResponse = $this->sportsApingRequest('listMarketBook', $params);


		    if(count($marketId)>1){

				$marketBooks = array();

				foreach( $jsonResponse[0]->result as $marketBook){

					$marketBooks[$marketBook->marketId]=$marketBook;

				}

				//Add overflow market books if any
				$marketBooks = array_merge($marketBooks, $overFlowMarketBooks);


				return $marketBooks;
			}else{

				return $jsonResponse[0]->result[0];
			}


		}

		//Gets a list of all horse racing venues..
		function getVenues(){

			$params = '{"filter":{}}';
		    $jsonResponse = $this->sportsApingRequest('listVenues', $params);
		    return $jsonResponse[0]->result;
		}

		//Debugging function
		function debug($debugString)
		{
		    global $DEBUG;
		    if ($DEBUG)
		        echo $debugString . "\n\n";
		}

	}
