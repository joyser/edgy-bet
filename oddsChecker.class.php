<?
	
	class oddsChecker extends scraper{
		
		public $approvedBookmakers = array(
			'PP'=>'Paddypower',
			'LD'=>'Ladbrokes',
			'WH'=>'Wiliamhill',
			//'CE'=>'Coral',
			'B3'=>'Bet365',
			'BY'=>'Boylesport'
		);
		
		public $ignoredExchanges = array(
			'BD'=>'Betdaq',
			'BF'=>'Betfair'
		);
		
		
		function __construct(){
			
			//get list of approved bookmakers	
			
		}
		
		function updateBookmakerPrices( $BetfairMarkets ){
			
			//If only a single market, place it in array
			if( !is_array($BetfairMarkets) ){
				
				$BetfairMarkets = array($BetfairMarkets);
			}
			
			//Array of races to be retrieved from oddschecker
			$races = array();
			
			foreach($BetfairMarkets as $BetfairMarket){
				
				//Check that it has been more than 1 minute since last update of odds, and the event hasnt started..
				if( ($BetfairMarket->lastOddsCheckerUpdateTime == "0000-00-00 00:00:00" || (new DateTime('-55 seconds')) > (new DateTime($BetfairMarket->lastOddsCheckerUpdateTime) )) && ((new DateTime('-10 minutes')) < (new DateTime($BetfairMarket->startTime) )) ){
											
										
					//Check that there is a venue
					if( $BetfairMarket->BetfairEvent->BetfairVenueId ){
							
						//Load the venue
						$BetfairVenue = new BetfairVenue;
						$BetfairVenue = $BetfairVenue->find(array('conditions'=>array('id'=>$BetfairMarket->BetfairEvent->BetfairVenueId) ) );
							
						if(count($BetfairVenue) > 0){
							
							//Extract short time from the market...
							$BetfairVenue = $BetfairVenue[0];
							$time = new DateTime($BetfairMarket->startTime);
							
							$races[$BetfairMarket->betfairId] = array('venueName'=>$BetfairVenue->name,'time'=>$time,'country'=>$BetfairMarket->BetfairEvent->country);
						}
					}
				}
			}
			
			
			//Get the prices of races...
			if(count($races)>0)
				$races = $this->getRacePrices($races);

			
			foreach( $BetfairMarkets as &$BetfairMarket ){
				
				
				//Check if their is prices for this market
				if(isset($races[$BetfairMarket->betfairId])){
					
					if(!isset($BetfairMarket->BetfairRunner)){
						
						//Get runners for this race..
						$BetfairRunner = new BetfairRunner;
						$BetfairMarket->BetfairRunner = $BetfairRunner->find( array('conditions'=>array('betfairMarketId'=>$BetfairMarket->betfairId) ) );
					}
					
					
					if( count($BetfairMarket->BetfairRunner) > 0 ){
						
						
						//Get prices from oddschecker..
						$prices = $races[$BetfairMarket->betfairId]['horses'];
						
						if( count( $prices ) > 0 ){
							
							//Loop through each horse which is saved..
							foreach($BetfairMarket->BetfairRunner as &$BetfairRunner){
								
								//logness($prices);
								
								//Search for corresponding horse in price array
								foreach($prices as $price){
									
									if(strtolower($price->name) == strtolower($BetfairRunner->name)){
										
										//Create list of bookmakers with best price
										$abbreviations = array();
										foreach($price->bestBookmakers as $abbreviation=>$value){
											
											$abbreviations[] = $abbreviation;
											
										}
										$BetfairRunner->bestBookmakerList = implode(", ", $abbreviations);
										$BetfairRunner->bestBookmakerPrice = $price->bestPrice;
										$BetfairRunner->save();
									}
								}
							}
						}
						
						$BetfairMarket->lastOddsCheckerUpdateTime = (new DateTime)->format('Y-m-d H:i:s');
						$BetfairMarket->save();
					}
					
				}
				
			}
			return $BetfairMarkets;
		}
		
		//Function returns objects of todays events, and sub races...
		function getTodaysRaces(){
			
			//Get html of todays racing page
			$html = $this->getHtml( 'http://www.oddschecker.com/horse-racing');
			
			//Strip first part from text..
			$html = explode("<h2 class=\"title\">Today's Racing</h2>", $html);//($html, "<h2 class=\"title\">Today's Racing</h2>");
			$html = explode("</tbody></table></div></div></div></section>", $html[1]);//</tbody></table></div></div></div></section>
			$venueHtml = explode("<a class=\"venue\" href=\"/horse-racing/", $html[0]);
			
			$venueCount = count( $venueHtml );
			$counter = 1;
			
			//array of the events for today..
			$events = array();
			
			while( $counter < $venueCount){
				
				$object = new stdClass();
				
				//Extract the venue name
				$venueName = $venueHtml[$counter];
				$venueName = explode("\"", $venueName);
				$venueName = $venueName[0];
				
				$object->venue = $venueName;
				
				//Split at each race time
				$venueTimes = explode("data-time=\"", $venueHtml[$counter]);
				
				$raceCount = count( $venueTimes );
				$raceCounter = 1;
				
				$object->races = array();
				
				while( $raceCounter < $raceCount ){
					
					$venueTime = explode("\"", $venueTimes[$raceCounter]);
					
					//Need to take out the date and trailing seconds..
					$venueTime = explode(" ", $venueTime[0]);
					$venueTime = substr($venueTime[1], 0, -3);
					
					$object->races[]=$venueTime;
					
					$raceCounter++;
				}
				
				
				
				
				
				$events[]= $object;
				
				$counter++;
			}
			
			return $events;
		}
		
		function getRacePrices($races){ //$races is an array of races, which are arrays with venueName(string) and time(DateTime)
			
			
			if(isset($races['venueName'])){
				
				$races = array($races);
			}
			
			$urls = array();
			foreach( $races as $raceIndex=>&$race ){
				
				//If the race is tomorrow, then the date needs to the prepended to the event name
				if($race['time'] > (new DateTime('midnight +24 hours'))){
					
					$race['venueName'] = (new DateTime('midnight +24 hours'))->format('Y-m-d')." ".$race['venueName'];
				}
				
				//Reaplce spaces with dashes
				$race['venueName'] = str_replace(" ", "-", $race['venueName']);
				
				$world = "";
				
				if($race['country']!="IE" && $race['country']!="GB" && $race['country']!="AE" ){
					
					$world="world/";
				}
					
				
				$urls[$raceIndex]='http://www.oddschecker.com/horse-racing/'.$world.$race['venueName'].'/'.$race['time']->format('H:i').'/winner';
				
			}
			
						
			//Retrieve html from race page(s)
			$raceHtml = $this->multiRequest($urls);
				
			foreach($races as $raceIndex=>&$race){
				
				$html = $raceHtml[$raceIndex];
				
				
				//Need to get bookmakers, after "view form" text.
				$bookmakerHtml = explode("View Form", $html);

				$bookmakerHtml = $bookmakerHtml[count($bookmakerHtml)-1];
				
				
				$bookmakerCodes = explode("<td data-bk=\"", $bookmakerHtml);
				
				$bookmakerCodesArray = array();
				
				
				foreach( $bookmakerCodes as $index=>$code){

					//Ignore first element
					if( $index != 0){
						
						$code = substr($code, 0,2);

						if( !in_array($code, $bookmakerCodesArray) ){
							
							$bookmakerCodesArray[] = $code;
						}else{
							
							break;
						}
						
						
					}
				}
				
				
				//Need to get each runner now..
				$runners = explode("data-bname=\"", $html);
				$horses = array();
				
				
				
				
				foreach ($runners as $index=>$runner){
					
					if( $index != 0){
						
						$horseHtml = explode("\"", $runner);
						
						
						//Need to get each runner now..
						$prices = explode("data-odig=\"", $runner);
						$priceArray = array();
						
						foreach ($prices as $index2=>$price){
							
							if( $index2 != 0){
								
								$price = explode("\"", $price);
								$priceArray[] = $price[0];
								
								
							}
						}

						$newHorse = new stdClass;
						$newHorse->name = $horseHtml[0];
						$newHorse->prices = $priceArray;
						
						$horses[] = $newHorse;
						
					}
				}
				
				//Now have all prices, horse names and bookmakers
				//Need to find the best prices and corresponding bookmakers
				foreach( $horses as $horseIndex => &$horse ){
					
					$bestPrice = 0;
					$bestBookmakers = array();
					
					foreach( $horse->prices as $index=> $price ){
						
						if( $price >= $bestPrice && isset($this->approvedBookmakers[$bookmakerCodesArray[$index]]) ){
							
							if($price > $bestPrice){
							
								//reset array
								$bestBookmakers = array();
							}
						
							$bestBookmakers[$bookmakerCodesArray[$index]] = $this->approvedBookmakers[$bookmakerCodesArray[$index]];
							$bestPrice = $price;
						} 
					}
					
					$horse->bestPrice = $bestPrice;
					$horse->bestBookmakers = $bestBookmakers;
					
				}
				
				$race['horses'] = $horses;
									
			}
			
			return $races;
		}
		
		
		
		
	}