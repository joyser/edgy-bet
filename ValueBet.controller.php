<?
	
	class ValueBetController {
		
		function index(){
			
			//Get system variables
			$SystemVariable = new SystemVariable;
			$runnerStartHour = $SystemVariable->get('runnerStartHour');
			$runnerEndHour = $SystemVariable->get('runnerEndHour');
			$minimumEachWayOdds = $SystemVariable->get('minimumEachWayOdds');
			
			//User session..
			$UserSession = new UserSession;
			
			global $globalView;
			
			//Get all value which was spotted within the last 30 seconds...
			$lookBack= new DateTime('-15 seconds');
			
			//filters received
			$bookie = $_GET['bookie'];
			
			$minimumGrowth = 4;
			$minimumEdge = 0;
			$minimumLiquid = 0;
			$minimumPrice = 0;
			$maxPrice = 999;
			
			if( $UserSession->User->level == 1){
				
				$minimumGrowth = isset($_GET['minimumGrowth'])? (float)$_GET['minimumGrowth'] : 4;
				$minimumEdge = isset($_GET['minimumEdge'])? (float)$_GET['minimumEdge'] : 0;
				$minimumLiquid = isset($_GET['minimumLiquid'])? (float)$_GET['minimumLiquid'] : 0;
				$minimumPrice = isset($_GET['minimumPrice'])? (float)$_GET['minimumPrice'] : 0;
				$maxPrice = isset($_GET['maxPrice'])? (float)$_GET['maxPrice'] : 999;
			}
			
			//Get value bets with minimum growth rate and speicif bookies
			$ValueBet = new ValueBet;
			$ValueBets = $ValueBet->getValueBets($lookBack,($minimumGrowth/10000),$bookie, ($minimumEdge/100), $minimumLiquid, $minimumPrice, $maxPrice);
			
			//Get current users bets for the day
			$startToday = new DateTime('today midnight');
			$endToday = new DateTime('tomorrow midnight');
			
			$Bet = new Bet;
			$Bets = $Bet->find(array('conditions'=>array(
				'UserId'=>$UserSession->User->id,
				'deleted='=>0,
				'BetfairMarket.startTime>'=>$startToday->format('Y-m-d H:i:s'),
				'BetfairMarket.startTime<'=>$endToday->format('Y-m-d H:i:s')
			), 'orderBy'=>'id DESC'));
			
			
			
			//Calculate bet metrics
			$totalStake=0;
			$betCount = 0;
			$runnersBackedIds = array();
						
			foreach($Bets as $Bet){
				
				$totalStake+= $Bet->stake;
				$betCount++;
				
				$runnersBackedIds[] = $Bet->BetfairRunner->id;
			}
			
			
			//Get bankroll
			$bankroll = $UserSession->User->bankroll;
			
			//For getting fractional odds
			$Odd = new Odd;
			
			//Loop through each value bet
			foreach($ValueBets as &$ValueBet){
				
				//Get the amount matched on horse in last 2 minutes
				$matchLookBack = new DateTime('-3 minutes');
				
				$tradeVolumes = $ValueBet->BetfairRunner->getTradedVolume($matchLookBack);
				
				$ValueBet->tradedVolume = $tradeVolumes[0];
				$ValueBet->BetfairRunner->totalMatched = $tradeVolumes[1];
				
				
				$ValueBetAge = time() - (new DateTime($ValueBet->lastUpdate))->getTimeStamp();
				
				$ValueBet->shortTime = substr($ValueBet->BetfairMarket->startTime,11,5);
				$ValueBet->age = $ValueBetAge;
				$ValueBet->fractionalOdds = $Odd->getFractional($ValueBet->bestBookmakerPrice);
				
				//Is user is level 3 or more we apply some filters..
				//Apply these filters regardless of level now on to accomodate for the lads
				//if( $UserSession->User->level != 1  ){
					
					//Hide bets that are tomorrow
					if( (new DateTime($ValueBet->BetfairMarket->startTime)) > (new DateTime('midnight tomorrow')) ){
						$ValueBet->hideOnMobile = true;
					}
					
					
					//Hide bets if minimum liquid and minimum matched
					//if( $ValueBet->BetfairMarket->totalMatched < 15000 || $ValueBet->liquid < 15){
						
					//	$ValueBet->hideOnMobile = true;
					//}
					
					//Hide ew bets that are too short 
					if( $ValueBet->BetTypeId ==2 && $ValueBet->bestBookmakerPrice < $minimumEachWayOdds->value){
						$ValueBet->hideOnMobile = true;
					}
					
						
					//Hide bets that have already been backed
					if( !(array_search($ValueBet->BetfairRunner->id, $runnersBackedIds)===False) ){
						
						$ValueBet->hideOnMobile = true;
					}
					
					//Hide bets for users outside working time range...
					if( ((date('H') < $runnerStartHour->value) || (date('H') >= $runnerEndHour->value)) ){
					    
					    $ValueBet->hideOnMobile = true;
					}
					
					//hard coded limits used instead
					//Set the max stakes
					if( $ValueBet->BetTypeId == 1 ){
						
						$maxStake = floor(($UserSession->User->maxMobilePayout/$ValueBet->bestBookmakerPrice)/10)*10;
						
					}else if( $ValueBet->BetTypeId == 2 ){//EW bets will have double max payout
					
						$maxStake = floor((2*$UserSession->User->maxMobilePayout/$ValueBet->bestBookmakerPrice)/10)*10;
					}
					
					
					//$maxStake = floor($ValueBet->getInstoreMaxStake()/10)*10;
				//}
				
				$ValueBet->stake = floor(($ValueBet->kellyPercent*$bankroll)/10)*10;
				$ValueBet->grow = floor($ValueBet->stake*$ValueBet->edge);
				
				if($maxStake>0)
					$ValueBet->mobileStake = min($ValueBet->stake, $maxStake);
				else
					$ValueBet->mobileStake = $ValueBet->stake;
				
			}
			
			//Show valuebet filters if level 1 user
			if( $UserSession->User->level == 1){
				
				$Bookmaker = new Bookmaker;
				$Bookmakers = $Bookmaker->find(array('conditions'=>array('visible'=>1)));
				
				$globalView->addView('ValueBetFilters', array('minimumGrowth'=>$minimumGrowth,'minimumEdge'=>$minimumEdge,'minimumLiquid'=>$minimumLiquid,'bookie'=>$bookie,'minimumPrice'=>$minimumPrice,'maxPrice'=>$maxPrice, 'Bookmakers'=>$Bookmakers));
			}
			
			$globalView->addView('ValueBet', array('ValueBets'=>$ValueBets,'valueRefreshTime'=>15,'bankroll'=>$bankroll,'bookie'=>$bookie,'totalStake'=>$totalStake,'betCount'=>$betCount));
			
			//$globalView->addView('Bets', array('Bets'=>$Bets,'title'=>'Todays bets'));

						
		}
		
		
	}