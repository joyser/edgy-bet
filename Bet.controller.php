<?
	
	class BetController {
		
		function index($arguments = array()){
			
			global $globalView;
			
			//Display all bets to the user..
			$UserSession = new UserSession;			
			
			//Get start and end date filters, or default to today
			$startDate = new DateTime($arguments['startDate']);
			if ($startDate == false){
				$startDate = new DateTime();
			}
			
			$endDate = new DateTime($arguments['endDate']);
			if ($endDate == false){
				$endDate = new DateTime();
			}
			
			$UserId = $arguments['UserId'];
			
			$User = new User;
			$Users = $User->find();
			
			$BetSeachVariables = array(
				'startDate'=>$startDate->format('Y-m-d'),
				'endDate'=>$endDate->format('Y-m-d'),
				'Users'=>$Users,
				'UserId'=>$UserId
			);
			
			$conditions = array(
				'isSettled='=>0,
				'BetfairMarket.startTime>'=>$startDate->format('Y-m-d 00:00:00'),
				'BetfairMarket.startTime<'=>$endDate->format('Y-m-d 23:59:59')
			);
			
			//Check level of current user...
			if( $UserSession->User->level != 1){
				
				$conditions['UserId']=$UserSession->User->id;
			}else if(is_numeric($UserId)){
				
				$conditions['UserId']=$UserId;
			} 
			
			$Bet = new Bet;
			$Bets = $Bet->find(array('conditions'=>$conditions,'orderBy'=>'id DESC'));
			
			//Only show filters if level 1
			if( $UserSession->User->level == 1){
				
				$globalView->addView('BetSearch', $BetSeachVariables);
			}
			$globalView->addView('Bets', array('Bets'=>$Bets,'title'=>'Todays open bets'));
			
			
		}
		
		function newBet($arguments = array()){
			
			//View for display page sections
			global $globalView;
			
			//Get the betfair runner and market
			$BetfairRunner = new BetfairRunner;
			$BetfairRunner = $BetfairRunner->find(array('conditions'=>array(
				'id'=>$arguments['BetfairRunnerId'],
				'betfairMarketId'=>$arguments['betfairMarketId']
			)));
			
			$BetfairMarket = new BetfairMarket;
			$BetfairMarket=$BetfairMarket->find(array('conditions'=>array(
				'betfairId'=>$arguments['betfairMarketId']
			)));
			
			
			//Get list of boomakers..
			$Bookmakers = new Bookmaker;
			$VisibleBookmakers = $Bookmakers->find();
			
			//Get bet types..
			$BetTypes = new BetType;
			$BetTypes = $BetTypes->find();
			
			if( count($BetfairRunner)==1 && count($BetfairMarket)==1 && $BetfairRunner[0]->betfairMarketId == $BetfairMarket[0]->betfairId ){
				
				$BetfairRunner = $BetfairRunner[0];
				$BetfairMarket = $BetfairMarket[0];
				
				$dataError=0;
				
			}else{
				
				$dataError=1;
			}
			
			//Get short time
			$shortTime = substr($BetfairMarket->startTime, 11, 5);
			
			//Get the fractional version of odds
			$Odd = new Odd;
			$defaultPriceFractional = $Odd->getFractional($arguments['defaultPrice']);
						
			$displayVariables = array(
				'BetfairRunner'=>$BetfairRunner,
				'Bookmakers'=>$VisibleBookmakers,
				'BetTypes'=>$BetTypes,
				'BetfairMarket'=>$BetfairMarket,
				'dataError'=>$dataError,
				'defaultPrice'=>$arguments['defaultPrice'],
				'defaultPriceFractional'=>$defaultPriceFractional,
				'defaultStake'=>$arguments['defaultStake'],
				'defaultBetTypeId'=>$arguments['defaultBetTypeId'],
				'bookieFilter'=>$arguments['bookieFilter'],
				'raceTitle'=>$BetfairMarket->BetfairEvent->name." ".$shortTime
			);
			
			
			$globalView->addView('NewBet', $displayVariables);
		}
		
		
		function saveBet($arguments = array()){
			
			//View for display page sections
			global $globalView;
			
			if($arguments['stake']!="" && $arguments['price']!="" && is_numeric($arguments['stake']) && is_numeric($arguments['price'])){
				
				//Get user Id..
				$UserSession = new UserSession;
				
				$Bet = new Bet;
				$Bet->UserId = $UserSession->User->id;
				$Bet->price = $arguments['price'];
				$Bet->stake = $arguments['stake'];
				//$Bet->value = $arguments['stake'];
				$Bet->BetfairRunnerId = $arguments['BetfairRunnerId'];
				$Bet->betfairMarketId = $arguments['betfairMarketId'];
				$Bet->BetTypeId = $arguments['BetTypeId'];
				//$Bet->BookmakerId = $arguments['BookmakerId'];
				$Bet->save();
				
				//Have to reload the bet to get the child data (i know, i know)
				$Bet = $Bet->find(array('conditions'=>array('id'=>$Bet->id)))[0];
				
				$displayVariables = array('Bet'=>$Bet,'bookieFilter'=>$arguments['bookie']);
			
				$globalView->addView('NewBetReceipt', $displayVariables);
			}
			
			
		}
		
		
		function deleteBet($arguments = array()){
			
			$BetId = $arguments['BetId'];
			$UserSession = new UserSession;
			$conditions = array('id'=>$BetId);
			
			//Check level of current user...
			if( $UserSession->User->level != 1){
				
				$conditions['UserId']=$UserSession->User->id;
			}
			
			$Bet = new Bet;
			$Bets = $Bet->find(array('conditions'=>$conditions));
			
			
			if( count($Bets == 1 )){
				
				$Bets[0]->deleted=1;
				$Bets[0]->save();
			}
			
			$this->index();
		}
		
		function updateBetStatus($arguments = array()){
			
			$BetId = $arguments['BetId'];
			$BetStatusId = $arguments['BetStatusId'];
			$UserSession = new UserSession;
			$conditions = array('id'=>$BetId);
			
			//Check level of current user...
			if( $UserSession->User->level != 1){
				
				$conditions['UserId']=$UserSession->User->id;
			}
			
			$Bet = new Bet;
			$Bets = $Bet->find(array('conditions'=>$conditions));
			
			//Only update to cancelled status for the moment
			if( $BetStatusId == 2 ){
				
				$Bets[0]->BetStatusId = $BetStatusId;
				$Bets[0]->save();
			}
			
		}
		
		
	}