<?
	
	class arbFinder{
		
		//finds arbitrage values on an eachway market
		function getEachWayArbitrageValues($BetfairRunners, $winMarketOrderBook, $placeMarketOrderBook, $eachWayTerms){
			
			
			//Set the key value to the selection id
			foreach($winMarketOrderBook->runners as &$key => $runner){
				
				$key = $runner->selectionId;
			}
			
			foreach($placeMarketOrderBook->runners as &$key => $runner){
				
				$key = $runner->selectionId;
			}
			
			
			
			//Loop through each runner in the win market book
			foreach($winMarketOrderBook->runners as $selectionId=>&$runner){
			
				//Find the corresponding horse in the place market using runner id
				foreach($BetfairRunners as &$BetfairRunner){
				
					
					if( $BetfairRunner->betfairId == $runner->selectionId ){
					
						//Find the fair win price of the horse..
						$fairWinPrice = ($runner->ex->availableToBack[0]->price + $runner->ex->availableToLay[0]->price)/2;
						
						//Convert to fair win prob of horse
						$fairWinProb = 1/$fairWinPrice;
						
						//find the fair place price of horse
						$fairPlacePrice = ($placeMarketOrderBook->runners[$selectionId]->ex->availableToBack[0]->price + $placeMarketOrderBook->runners[$selectionId]->ex->availableToLay[0]->price)/2;
						
						//convert to fair place prob of horse
						$fairPlaceProb = 1/$fairPlacePrice;
						
						//use formula to find break even price of horse ew odds
						
						
						//the arb value to the winmarket runners	
						$BetfairRunner->fairWinPrice=$fairWinPrice;
						$BetfairRunner->fairWinProb=$fairWinProb;
						$BetfairRunner->fairPlacePrice=$fairPlacePrice;
						$BetfairRunner->fairPlaceProb=$fairPlaceProb;
						$BetfairRunner->breakEvenPrice=0;
					}
					
				}
			}
			
			return $BetfairRunners;
		}
		
	}