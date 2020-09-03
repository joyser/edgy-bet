<?
	
	//Class is sued to help setup run betfair tasks 
	class betfairHelper{
		
		private $betfairAPI;
		
		
		function __construct(){
			
			$this->betfairAPI = new betfairAPI;
		} 
		
		
		//Function will update the list of venues held in the DB
		function updateVenues(){
			
			//DB obejct
			$BetfairVenue = new BetfairVenue;
			
			//Get all venues
			$venues = $this->betfairAPI->getVenues();
			
			foreach( $venues as $venue){
				
				//Check if venue is already saved...
				$BetfairVenues = $BetfairVenue->find(array('conditions'=>array('name'=>$venue->venue) ) );
				
				if( count($BetfairVenues) < 1){
					
					$NewBetfairVenue = clone $BetfairVenue;
					$NewBetfairVenue->name = $venue->venue;
					$NewBetfairVenue->save();
				}
				
			}
			
		}
		
		//Function saves the events for particular 
		function updateEvents( $eventTypeId ){
			
			//update venues just in case
			$this->updateVenues();
			
			//DB obejct
			$BetfairEvent = new BetfairEvent;
			
			//Get all venues
			$events = $this->betfairAPI->getEvents($eventTypeId);
			
			foreach( $events as $event ){
				

				//Check if event is already saved...
				$BetfairEvents = $BetfairEvent->find(array('conditions'=>array('betfairId'=>$event->event->id) ) );
				
				if( count($BetfairEvents) < 1 ){
					
					//Received time in gmt
					$eventTime = new DateTime($event->event->openDate);
					
					//Need to adjust the time to fit current server timezone..
					$eventTime->modify(date_offset_get(new DateTime).'seconds');
					
					$NewBetfairEvent = clone $BetfairEvent;
					$NewBetfairEvent->name = $event->event->name;
					$NewBetfairEvent->betfairEventTypeId = $eventTypeId;
					$NewBetfairEvent->betfairId = $event->event->id;
					$NewBetfairEvent->country = $event->event->countryCode;
					$NewBetfairEvent->startTime = $eventTime->format('Y-m-d H:i:s');
					$NewBetfairEvent->save();
				}
				
				
			}
			
			//Update the event venues for all events happening after today..
			if( $eventTypeId == 7){
				$this->updateEventHorseVenues();
				
			}
			
			$this->updateHasWinMarket($eventTypeId);
		}
		
		
		//Searches for venues for horse racing events event and updates the DB where it can..
		function updateEventHorseVenues(){
			
			//Get all horse events which start after midnight
			$BetfairEvent = new BetfairEvent;
			$BetfairEvents = $BetfairEvent->find(array('conditions'=>array('startTime>'=>date('Y-m-d 00:00:00'),'betfairEventTypeId'=>7 ) ) );
			
			foreach($BetfairEvents as $BetfairEvent){ 
							
				//need to find the venue of this event..
				$pieces = preg_split('/(?=[A-Z])/',$BetfairEvent->name);
				$pieces = explode(' ', $pieces[1]);
				$eventAbbr = $pieces[0];
				
				$BetfairVenueId = $BetfairEvent->BetfairVenueId;
				
				//First check if there is an exact abbreviation match...
				$BetfairVenue = new BetfairVenue;
				$BetfairVenues = $BetfairVenue->find( array('conditions'=>array('customAbbreviation'=>$eventAbbr)) );
				
				if( count($BetfairVenues)==1 ){
					
					$BetfairVenueId = $BetfairVenues[0]->id;
				}else{
					
					//Add % symbol for like condition
					$eventAbbr.='%';
					
					//Search for venue associated with this event.. (only when there is at least three letters
					
					$BetfairVenues = $BetfairVenue->find( array('conditions'=>array('LIKE'=>array('name'=>$eventAbbr) )) );
					
					if( count($BetfairVenues)==1 ){
						
						$BetfairVenueId = $BetfairVenues[0]->id;
					}
					
				}
				
				if($BetfairVenueId){
				
					$BetfairEvent->BetfairVenueId = $BetfairVenueId;
					$BetfairEvent->save();
				}
			}
		}
		
		//Update hasWinMarkets field on BetfairEvents
		function updateHasWinMarket($eventTypeId){
			
			//Get all venues
			$events = $this->betfairAPI->getEvents($eventTypeId, "WIN");
			//logness($events);
			foreach( $events as $event ){
				
				//Check if event is already saved...
				$BetfairEvent = new BetfairEvent;
				$BetfairEvents = $BetfairEvent->find(array('conditions'=>array('betfairId'=>$event->event->id) ) );
				
				if( count($BetfairEvents) == 1 ){
					
					$BetfairEvent = $BetfairEvents[0];
					
					$BetfairEvent->hasWinMarket = 1;
					$BetfairEvent->save();
				}				
			}
		}
		
		//Updates markets for a particular event..
		function updateEventMarkets( $betfairEventId ){
			
			$BetfairAPI = new BetfairAPI;
			$markets = $BetfairAPI->getMarkets( $betfairEventId );
			$BetfairMarket = new BetfairMarket;
			$BetfairRunner = new BetfairRunner;
			
			foreach( $markets as $market ){
				
				//Check if this market is already saved..
				$BetfairMarkets = $BetfairMarket->find( array('conditions'=>array('betfairId'=>$market->marketId) ) );
				
				if(count($BetfairMarkets) < 1 ){
					
					//Get the start time of market, in GMT
					$startTime = new DateTime($market->description->marketTime);
					
					//Need to adjust the time to fit current server timezone..
					$startTime->modify(date_offset_get(new DateTime).'seconds');
					
					//Find out if the race is a handcicap or not..
					$isHandicap = 0;
					
					if(strpos($market->marketName, 'Hcap') !== false ){
						
						$isHandicap=1;						
					}

					$NewBetfairMarket = clone $BetfairMarket;
					$NewBetfairMarket->betfairId = $market->marketId;
					$NewBetfairMarket->name = $market->marketName;
					$NewBetfairMarket->type = $market->description->marketType;
					$NewBetfairMarket->startTime = $startTime->format('Y-m-d H:i:s');
					$NewBetfairMarket->betfairEventId = $betfairEventId;
					$NewBetfairMarket->isHandicap = $isHandicap;
					$NewBetfairMarket->save();
										
					$activeRunners = 0;
				
					//Need to add the runners for this market..
					foreach( $market->runners as $runner ){
						
						//Add the runner
						$NewBetfairRunner = clone $BetfairRunner;
						$NewBetfairRunner->name = $runner->runnerName;
						$NewBetfairRunner->number = $runner->metadata->CLOTH_NUMBER;
						$NewBetfairRunner->betfairId = $runner->selectionId;
						$NewBetfairRunner->betfairMarketId = $NewBetfairMarket->betfairId;
						$NewBetfairRunner->colorsFilename = $runner->metadata->COLOURS_FILENAME;
						$NewBetfairRunner->save();
						
						$activeRunners++;
						
					}
					
					$NewBetfairMarket->activeRunners = $activeRunners;
					$NewBetfairMarket->save();
				}
			}
			//logness($markets);
			
		}
		
		//Returns the eachway terms which will be given..
		static function eachWayTerms($runners, $isHandicap){
			
			if($isHandicap){
				
				if($runners > 15 )
					return array('places'=>4,'multiplier'=>0.25);
					
				else if ($runners > 11)
					return array('places'=>3,'multiplier'=>0.25);
					
				else if ($runners > 7)
					return array('places'=>3,'multiplier'=>0.2);
				
				else if ($runners > 4)
					return array('places'=>2,'multiplier'=>0.25);
					
				else 
					return array('places'=>0,'multiplier'=>0);
				
			}else{
				
				if($runners > 7 )
					return array('places'=>3,'multiplier'=>0.2);
					
				else if ($runners > 4)
					return array('places'=>2,'multiplier'=>0.25);
					
				else 
					return array('places'=>0,'multiplier'=>0);
				
			}
			
		}
		
		
		//Returns the betfairMarket of the eachWay Place only market
		function getEachWayPlaceMarket($BetfairMarket, $activeRunners, $eachWayTerms = array()){
			
			//First find out how many places 
			if( count($eachWayTerms) < 1 ){
				$eachWayTerms = $this->eachWayTerms($activeRunners, $BetfairMarket->isHandicap);
			}
			
			//Find markets for the same event, at the same time
			$BetfairPlaceMarket = new BetfairMarket;
			
			//First search for a "x TBP" market
			$tbpString = $eachWayTerms['places'].' TBP';
			$BetfairPlaceMarkets = $BetfairPlaceMarket->find( array('conditions'=>array(
				'startTime'=>$BetfairMarket->startTime,
				'betfairEventId'=>$BetfairMarket->betfairEventId,
				'name'=>$tbpString
			) ) );
			
			if(count($BetfairPlaceMarkets) > 0 ){
				
				return $BetfairPlaceMarkets[0];
			}else{
				
				//If not found, then just take the "To Be Placed" market
				$BetfairPlaceMarkets = $BetfairPlaceMarket->find( array('conditions'=>array(
					'startTime'=>$BetfairMarket->startTime,
					'betfairEventId'=>$BetfairMarket->betfairEventId,
					'name'=>'To Be Placed'
				) ) );
				
				if(count($BetfairPlaceMarkets) > 0 ){
				
					return $BetfairPlaceMarkets[0];
					
				}else{
					
					return 0;
				}
			}
		}
		
		
		static function updateRunnerSilks($BetfairRunners){
			
			foreach($BetfairRunners as $BetfairRunner){
				
				//Check if sil exists
				if( !file_exists("images/silks/".$BetfairRunner->colorsFilename) ){
					
					//Download silks
					$url = 'http://example.com/image.php'.$BetfairRunner->colorsFilename;
					$img = "images/silks/".$BetfairRunner->colorsFilename;
					file_put_contents($img, file_get_contents($url));
				}
				
			}
			
		}
	}