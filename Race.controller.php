<?
	
	class RaceController {
		
		function index(){
			
			global $globalView;
			
			//$globalView->addView('Race');
		}
		
		function viewRace($arguments = array()){
			
			//View for display page sections
			global $globalView;
			
			//betfairWin market..
			$winMarketId = $arguments['winMarketId'];
			
			//Get the main win market from DB
			$BetfairMarket = new BetfairMarket;
			$BetfairWinMarket = $BetfairMarket->find(array('conditions'=>array(
				'betfairId'=>$winMarketId
			)))[0];
			
			$BetfairWinMarket = valuefinder::getArbitrageRaces($BetfairWinMarket, $arguments['extraPlace'],$arguments['quarterOdds'])[0];
			
			//Get bankroll
			$UserSession = new UserSession;
			$bankroll = $UserSession->User->bankroll;
			
			if(isset($BetfairWinMarket->placeMarketBook)){
				
				$placeMarketFound=1;
			}
			
			$secondsSinceOddsCheckerRefresh = time() - (new DateTime($BetfairWinMarket->lastOddsCheckerUpdateTime))->getTimeStamp();
			$betfairRefreshTime =30;
			$oddsCheckerRefreshTime =60;
			$shortTime = substr($BetfairWinMarket->startTime, 11, 5);
			
			$displayVariables['secondsSinceOddsCheckerRefresh'] = $secondsSinceOddsCheckerRefresh;
			$displayVariables['betfairRefreshTime'] = $betfairRefreshTime;
			$displayVariables['oddsCheckerRefreshTime'] = $betfairRefreshTime;
			$displayVariables['eachWayTerms'] = $BetfairWinMarket->eachWayTerms;
			$displayVariables['winMarketId'] = $BetfairWinMarket->betfairId;
			$displayVariables['marketId'] = $BetfairWinMarket->id;
			$displayVariables['placeMarketFound'] = $placeMarketFound;
			$displayVariables['runners'] = $BetfairWinMarket->BetfairRunner;
			$displayVariables['raceTitle'] = $BetfairWinMarket->BetfairEvent->name." ".$shortTime;
			$displayVariables['activeRunners'] = $BetfairWinMarket->activeRunners;
			$displayVariables['totalMatched'] = $BetfairWinMarket->totalMatched;
			$displayVariables['bankroll'] = $bankroll;
			
			$globalView->addView('Race', $displayVariables);
			
			
			//Print bets currently on this race..
			$UserSession = new UserSession;
			
			$conditions = array(
				'deleted='=>0,
				'betfairMarketId'=>$winMarketId
			);
			
			//Check level of current user...
			if( $UserSession->User->level != 1){
				
				$conditions['UserId']=$UserSession->User->id;
			}
			
			$Bet = new Bet;
			$Bets = $Bet->find(array('conditions'=>$conditions, 'orderBy'=>'id DESC'));
			
			$globalView->addView('Bets', array('Bets'=>$Bets,'title'=>'Bets for this race'));
		}
		
		function viewMeeting($arguments = array()){
			
			//View for display page sections
			global $globalView;
			
			//Betfair event id
			$BetfairEventId = $arguments['BetfairEventId'];
			
			//five minutes ago
			$fiveMinutesAgo = new DateTime('-5minutes');
			
			//Get all markets for this event
			$BetfairMarket = new BetfairMarket;
			$BetfairWinMarkets = $BetfairMarket->find(array('conditions'=>array(
				'betfairEventId'=>$BetfairEventId,
				'type'=>'WIN',
				'startTime>'=>$fiveMinutesAgo->format('Y-m-d H:i:s')
			)));
			
			//Get arb values for all markets 
			$BetfairWinMarkets = valuefinder::getArbitrageRaces($BetfairWinMarkets);
			
			//Get bankroll
			$UserSession = new UserSession;
			$bankroll = $UserSession->User->bankroll;
			
			//This will avoid any refresh of odds
			$betfairRefreshTime = 0;
			$oddsCheckerRefreshTime = 0;
			
			foreach($BetfairWinMarkets as $BetfairWinMarket){
				
				$placeMarketFound=0;
				
				if(isset($BetfairWinMarket->placeMarketBook)){
					
					$placeMarketFound=1;
				}
				
				$shortTime = substr($BetfairWinMarket->startTime, 11, 5);
				
				$secondsSinceOddsCheckerRefresh = time() - (new DateTime($BetfairWinMarket->lastOddsCheckerUpdateTime))->getTimeStamp();
				
				$displayVariables['secondsSinceOddsCheckerRefresh'] = $secondsSinceOddsCheckerRefresh;
				$displayVariables['betfairRefreshTime'] = $betfairRefreshTime;
				$displayVariables['oddsCheckerRefreshTime'] = $oddsCheckerRefreshTime;
				$displayVariables['eachWayTerms'] = $BetfairWinMarket->eachWayTerms;
				$displayVariables['winMarketId'] = $BetfairWinMarket->betfairId;
				$displayVariables['marketId'] = $BetfairWinMarket->id;
				$displayVariables['placeMarketFound'] = $placeMarketFound;
				$displayVariables['runners'] = $BetfairWinMarket->BetfairRunner;
				$displayVariables['raceTitle'] = $BetfairWinMarket->BetfairEvent->name." ".$shortTime;
				$displayVariables['activeRunners'] = $BetfairWinMarket->activeRunners;
				$displayVariables['totalMatched'] = $BetfairWinMarket->totalMatched;
				$displayVariables['bankroll'] = $bankroll;
				
				$globalView->addView('Race', $displayVariables);
				
			}
			
			
		}
	}