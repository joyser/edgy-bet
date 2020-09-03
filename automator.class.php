<?
	
	class automator{
		
		
		//When true, the automator will exit if there is no active users on the system
		public $goIdle=true;
		
		
		//This is how often the cron task runs the script
		public $refreshTime=60;
		public $tasks =  array(
			array(
				'name'=>'updateOddsChecker',
				'minimumInterval'=>0
			),
			array(
				'name'=>'updateEvents',
				'minimumInterval'=>600
			),
			array(
				'name'=>'updateTodaysResults',
				'minimumInterval'=>600
			)
		);
		
		
		function __construct(){
			
			//Check if the idle is on
			if( $this->goIdle ){
				
				//Check if any user has been active in the last hour..
				$Login = new Login;
				$Logins = $Login->find(array('conditions'=>array(
					'lastActive>'=>(new DateTime('-1hour'))->format('Y-m-d H:i:s'),
					'wasSuccess'=>1
				)));
				
				if(count($Logins) == 0){
					
					logness('No active users found, exiting automator');
					customExit();
				}
			}
		}
		
		//updates oddschecker prices for all races in next 24 hours...
		public function updateOddsChecker(){
			
			//Get all win horse markets for UK and IRE
			//five minutes ago
			$fiveMinutesAgo = new DateTime('-5minutes');
			
			$twentyFourHours = new Datetime('+24hours');
			
			//Get all markets for this event
			$BetfairMarket = new BetfairMarket;
			$BetfairMarkets = $BetfairMarket->find(array('conditions'=>array(
				'type'=>'WIN',
				'startTime>'=>$fiveMinutesAgo->format('Y-m-d H:i:s'),
				'startTime<'=>$twentyFourHours->format('Y-m-d H:i:s')
			)));
			
			$oddsChecker = new oddsChecker;
			
			//Bulk update oddschecker prices
			$BetfairMarkets = $oddsChecker->updateBookmakerPrices($BetfairMarkets);
			
		}
		
		public function automate(){
			
			$AutomatedTask = new AutomatedTask;
			
			$runTime = new DateTime();
			
			$repeatExecution=0;
			//loop through each task
			foreach($this->tasks as $task){
				
				
				$lookBack = new DateTime('-'.$task['minimumInterval'].'seconds');
				
				//Check if task has been executed with lookback..
				$LastExecution = $AutomatedTask->find( array('conditions'=>array(
					'time>'=>$lookBack->format('Y-m-d H:i:s'),
					'name'=>$task['name']
				)) );
				
				if(count($LastExecution)<1){
					
					$startTime = microtime(true);
					$this->$task['name']();
					$executionTime = microtime(true)-$startTime;
					
					$completedAutomatedTask = clone $AutomatedTask;
					$completedAutomatedTask->time = $runTime->format('Y-m-d H:i:s');
					$completedAutomatedTask->name = $task['name'];
					$completedAutomatedTask->executionTime = $executionTime;
					$completedAutomatedTask->save();
				}
			}
		}
		
		
		//Finds value in one race only (requires frequent update)
		public function findValue(){
			
			$startFindValue = microtime(true);
			
			//get all (UK, and IRE) horse races for today that havent already started... 
			$next24Hours = new DateTime('+24 hours');
			
			//Limit the call to the first 50 races 
			$BetfairMarket = new BetfairMarket;
			$BetfairMarkets = $BetfairMarket->find(
			array(
				'conditions'=>array(
					'BetfairEvent.betfairEventTypeId'=>7,
					'type'=>'WIN',
					'startTime>'=>date('Y-m-d H:i:s'),
					'startTime<'=>$next24Hours->format('Y-m-d H:i:s'),
					'OR'=>array(array('BetfairEvent.country'=>'IE'),array('BetfairEvent.country'=>'GB'),array('BetfairEvent.country'=>'AE'))
				),
				'orderBy'=>'startTime ASC'
				)
			);
			
			logness((microtime(true)-$startFindValue));
				
			//Send all markets to find value...
			$valueFinder = new valueFinder;
			$BetfairMarkets = $valueFinder->getArbitrageRaces($BetfairMarkets);
			logness((microtime(true)-$startFindValue));
			
			//For logging the value
			$ValueBet = new ValueBet;
			
			//Clear all value bets that are more than 5 minutes old
			$lookBack = new DateTime('-5minutes');
			$ValueBet->clear($lookBack);
			logness((microtime(true)-$startFindValue));
			
			//This is for issuing tips to the users..
			$Tip = new Tip;
			
			//This is for updating live bet values..
			$Bet = new Bet;
			
			//This is for recording snapshots
			$RunnerSnapShot = new RunnerSnapShot;
			$snapShotTime = new DateTime;
			
			//clear old snapshots for efficiency 
			$RunnerSnapShot->clear($lookBack);
			
			logness((microtime(true)-$startFindValue));
			//Loop through each market..
			foreach($BetfairMarkets as $BetfairMarket){
				
				//Loop through each runner to check for value...
				foreach( $BetfairMarket->BetfairRunner as $BetfairRunner ){
					
					
					//Add a snap shot for each runner of the total matched
					$newRunnerSnapShot = clone $RunnerSnapShot;
					
					$newRunnerSnapShot->totalMatched = $BetfairRunner->totalMatched;
					$newRunnerSnapShot->lastTradedPrice = $BetfairRunner->lastTradedPrice;
					$newRunnerSnapShot->time = $snapShotTime->format('Y-m-d H:i:s');
					$newRunnerSnapShot->BetfairRunnerId = $BetfairRunner->id;
					$newRunnerSnapShot->save();
					
					/* Code will revalue bets based on market values, curently disabled *//*
					//Check if there are any bets saved for this runner...
					$Bets = $Bet->find(array('conditions'=>array(
						'betfairRunnerId'=>$BetfairRunner->betfairId,
						'betfairMarketId'=>$BetfairMarket->betfairId
					)));
					
					foreach($Bets as $Bet){
						
						if( $Bet->BetTypeId == 1){ //win bet
							
							$Bet->value = (1/$BetfairRunner->fairWinPrice)*($Bet->stake*$Bet->price);
							$Bet->save();
							
						}else if( $Bet->BetTypeId == 2){ //ew bet
							
							//If there is no longer an ew market, full stake goes on win..
							if(!$BetfairMarket->hasEachWayMarket){
								
								$Bet->value = (1/$BetfairRunner->fairWinPrice)*($Bet->stake*$Bet->price);
								
							}else{
								
								$Bet->value = ((1/$BetfairRunner->fairWinPrice)*(($Bet->stake/2)*$Bet->price))+((1/$BetfairRunner->fairPlacePrice)*(($Bet->stake/2)*((($Bet->price-1)*$BetfairMarket->eachWayTerms['multiplier'])+1)));
							}
							
							
							$Bet->save();
						}
						
					}
					*/
					
					//Only check the runner if there is a kelly against it..
					if( $BetfairRunner->kellyPercent ){
						
						$BetfairRunner->growth = $BetfairRunner->kellyPercent*$BetfairRunner->bestReturn;
						$newValueBet = clone $ValueBet;
						$newValueBet->BetfairRunnerId = $BetfairRunner->id;
						$newValueBet->betfairMarketId = $BetfairMarket->betfairId;
						$newValueBet->betfairEventId = $BetfairMarket->betfairEventId;
						$newValueBet->bestBookmakerPrice = $BetfairRunner->bestBookmakerPrice;
						$newValueBet->bestBookmakerList = $BetfairRunner->bestBookmakerList;
						$newValueBet->edge = $BetfairRunner->bestReturn;
						$newValueBet->kellyPercent = $BetfairRunner->kellyPercent;
						$newValueBet->growth = $BetfairRunner->bestReturn*$BetfairRunner->kellyPercent;
						$newValueBet->lastUpdate = $BetfairMarket->lastOddsCheckerUpdateTime;
						$newValueBet->edgeType = $BetfairRunner->edgeType;
						$newValueBet->BetTypeId = $BetfairRunner->edgeBetTypeId;
						$newValueBet->liquid = $BetfairRunner->liquid;
						$newValueBet->fairPrice = $BetfairRunner->edgeFairPrice;
						$newValueBet->time = date('Y-m-d H:i:s');
						
						$newValueBet->save();
						
						//Issue new tips
						//$Tip->ProcessValue($newValueBet);
					}
				}
			}
			
			/*
			logness((microtime(true)-$startFindValue));
			$beeper = new beeper;
			
			
			//Get all unsent tips..
			
			$Tip = new Tip;
			$UnsentTips = $Tip->find(array('conditions'=>array('sent'=>0)));
			
			foreach( $UnsentTips as &$Tip ){
				
				//Get the user that owns the tip..
				$User = new User;
				$User = $User->find(array('conditions'=>array('id'=>$Tip->Tipster->UserId)))[0];
				
				//Check that the user has a number saved, and bleeper activiated...
				if($User->mobile!="" && $User->beeperActive){
					
					$eventString = explode(" ", $Tip->BetfairEvent->name);
					$eventString = substr($Tip->BetfairMarket->startTime,11,5)." ".$eventString[0];
					$stake = floor(($Tip->kellyPercent*$User->bankroll)/10)*10;

					//get fractional odds
					$Odd = new Odd;
					$fractional = $Odd->getFractional($Tip->bestBookmakerPrice);
					
					$tipString = $Tip->BetfairRunner->name." @ "
								.$fractional." - "
								.$eventString." "
								.round($Tip->kellyPercent*100,1)."% (€".$stake.")"
								.$Tip->edgeType." ("
								.$Tip->bestBookmakerList.")";
								//@ 5.5 - 15:50 Kempton 1.5% EW (WH,PP,BY)"
										
					$beeper->beep($User->mobile, $tipString);
				}
				
				
				
				$Tip->sent=1;
				$Tip->save();
				
			}
			*/
			logness((microtime(true)-$startFindValue));
			
		}
		
		private function updateEvents(){
			
			//Get all horse events with win markets that start today..
			$BetfairEvent = new BetfairEvent;
			$lookBack = new DateTime('-12hours');
			$midnightTwoDays = new DateTime('midnight');
			$midnightTwoDays = $midnightTwoDays->modify('+2days');
			$fiveMinutesAgo = new DateTime('-5minutes');
			
			$betfairHelper = new betfairHelper;
			//Update horse racing events
			$betfairHelper->updateVenues();
			$betfairHelper->updateEvents(7);
			
			//Get approriate events and update their markets..
			$BetfairEvents = $BetfairEvent->find(array('conditions'=>array(
				'startTime>'=>$lookBack->format('Y-m-d H:i:s'),
				'startTime<'=>$midnightTwoDays->format('Y-m-d H:i:s'),
				'hasWinMarket'=>1,
				'betfairEventTypeId'=>7,
				'OR'=>array(array('country'=>'IE'),array('country'=>'GB'),array('country'=>'AE'))
			) ) );
			
			foreach( $BetfairEvents as $index=>$BetfairEvent ){
			
				$betfairHelper->updateEventMarkets($BetfairEvent->betfairId);
			}
			
		}
		
		public function updateTodaysResults(){
			
			$BetfairMarket = new BetfairMarket;
			$BetfairMarket->updateResults(new DateTime('now'));
		}
		
		
	}