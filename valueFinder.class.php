<?
	
	class valueFinder{
		
		
		//Returns the betfair markets with all relevant eachway arb data..
		static function getArbitrageRaces( $BetfairMarkets, $extraPlace=0, $quarterOdds=0 ){
			
			
			
			//Check if single race or not..
			if( !is_array($BetfairMarkets) ){
				
				$BetfairMarkets = array($BetfairMarkets);
			}
			
			//required helper objects
			$oddsChecker = new oddsChecker;
			$betfairAPI = new betfairAPI;
			$betfairHelper = new betfairHelper;
			
			//Holds the ids of win markets...
			$winMarketIds = array();
			$placeMarketIds = array();
			
			// remove market updating as automator.php should be doing it
			//$BetfairMarkets = $oddsChecker->updateBookmakerPrices($BetfairMarkets);
			
			foreach( $BetfairMarkets as &$BetfairMarket ){
				
				//update theodschecker prices...
				$winMarketIds[] = $BetfairMarket->betfairId;
			}
			
			//get the win market books in one go
			$winMarketBooks = $betfairAPI->getMarketBook( $winMarketIds );
			
			//Winmarketbooks will be a single if only one market given..
			if(!is_array($winMarketBooks))
				$winMarketBooks = array($winMarketIds[0]=>$winMarketBooks);
			
			foreach( $BetfairMarkets as &$BetfairMarket ){
				
				//assign marketbook
				$BetfairMarket->marketBook = $winMarketBooks[$BetfairMarket->betfairId];
					
					
					//Check for non runners
					if( $BetfairMarket->activeRunners != $BetfairMarket->marketBook->numberOfActiveRunners ){

						
						//Update the active runners..
						$BetfairMarket->activeRunners = $BetfairMarket->marketBook->numberOfActiveRunners;
						$BetfairMarket->save();
						
					}
					
					//Check if the volume has changed by 50%, and update if so
					//Cannot update every time because lag issues are caused
					if($BetfairMarket->marketBook->totalMatched >  ($BetfairMarket->totalMatched*1.5)){
						
						$BetfairMarket->totalMatched =  $BetfairMarket->marketBook->totalMatched;
						$BetfairMarket->save();
					}
					
					
					
					//Eachway terms
					$BetfairMarket->eachWayTerms = $betfairHelper->eachWayTerms($BetfairMarket->activeRunners, $BetfairMarket->isHandicap);
					
					//Find the place market for this race
					$BetfairMarket->BetfairPlaceMarket = $betfairHelper->getEachWayPlaceMarket($BetfairMarket, $BetfairMarket->activeRunners, $BetfairMarket->eachWayTerms);
					
					if($BetfairMarket->BetfairPlaceMarket){
						$placeMarketIds[] = $BetfairMarket->BetfairPlaceMarket->betfairId;
					}
			}
			
			//Get place markets..
			$placeMarketBooks = $betfairAPI->getMarketBook( $placeMarketIds );
			
			//placemarketbooks will be a single if only one market given..
			if(!is_array($placeMarketBooks))
				$placeMarketBooks = array($placeMarketIds[0]=>$placeMarketBooks);
						
			foreach( $BetfairMarkets as &$BetfairMarket ){
				
				//Only perform if there is an eacyway
					
					$placeMarketBook = 	$placeMarketBooks[$BetfairMarket->BetfairPlaceMarket->betfairId];				
					
					//Check that loaded place market is relevant to place terms
					if($placeMarketBook->numberOfWinners>0 && isset($placeMarketBook->numberOfWinners) && $placeMarketBook->numberOfWinners == $BetfairMarket->eachWayTerms['places'] ){
						
						$BetfairMarket->placeMarketBook = $placeMarketBook;
						
					}else{
						
						$BetfairMarket->placeMarketBook = 0;
						$BetfairMarket->hasEachWayMarket=0;
						$BetfairMarket->save();
					}
					
					$BetfairMarket->BetfairRunner = valueFinder::getArbitrageValues($BetfairMarket->BetfairRunner, $BetfairMarket->marketBook, $BetfairMarket->placeMarketBook, $BetfairMarket->eachWayTerms, $extraPlace, $quarterOdds);
			}
			
			return $BetfairMarkets;			
		}
		
		
		//finds arbitrage values on an eachway market
		static function getArbitrageValues($BetfairRunners, $winMarketOrderBook, $placeMarketOrderBook, $eachWayTerms, $extraPlace, $quarterOdds){
			
			//user for getting fractional odds
			$Odd = new Odd;
			
			//if $extraPlace is true,we scale the placeMarket to an extra place.. (paddypower special sometimes)
			
			//$if quarter odds is true we adjust the eachway terms (bet365 special sometimes)..
			if($quarterOdds)
				$eachWayTerms['multiplier']=0.25;
			
			//Only perform function if place market is there
				
			//Set the key value to the selection id
			$winMarketRunners = array();
			foreach($winMarketOrderBook->runners as $key => $runner){
				
				$winMarketRunners[$runner->selectionId] = $runner;
			}
			$winMarketOrderBook->runners = $winMarketRunners;
			
			$placeMarketRunners = array();
			if($placeMarketOrderBook){
				foreach($placeMarketOrderBook->runners as $key => $runner){
					
					$placeMarketRunners[$runner->selectionId] = $runner;
				}
				$placeMarketOrderBook->runners = $placeMarketRunners;
			}
			
			
			
			
			//Loop through each runner in the win market book
			foreach($winMarketOrderBook->runners as $selectionId=>&$runner){
			

				foreach($BetfairRunners as &$BetfairRunner){
				
					//find corresponding horse
					if( $BetfairRunner->betfairId == $runner->selectionId ){
						
						//Update the total matched for this horse
						//$BetfairRunner->totalMatched = $winMarketOrderBook->runners[$selectionId]->totalMatched;
						//$BetfairRunner->save();
						
						//Check if the horse is still running..
						if($winMarketOrderBook->runners[$selectionId]->status != "ACTIVE" && $winMarketOrderBook->status=="OPEN"){
							
							$BetfairRunner->isNonRunner = 1;
						}else{
						
							//Find the fair win price of the horse..
							//$fairWinPrice = ($runner->ex->availableToBack[0]->price + $runner->ex->availableToLay[0]->price)/2;
							//Now just take the lay price instead..
							$fairWinPrice = $runner->ex->availableToLay[0]->price;
							
							//Check if there was no lay price..
							if($fairWinPrice < $runner->ex->availableToBack[0]->price){
								
								$fairWinPrice=999;
							}
							
							//Convert to fair win prob of horse
							$fairWinProb = 1/$fairWinPrice;
							
							//find the fair place price of horse
							//$fairPlacePrice = ($placeMarketOrderBook->runners[$selectionId]->ex->availableToBack[0]->price + $placeMarketOrderBook->runners[$selectionId]->ex->availableToLay[0]->price)/2;
							$fairPlacePrice = $placeMarketOrderBook->runners[$selectionId]->ex->availableToLay[0]->price;
							
							//Check if there was no lay price..
							if($fairPlacePrice < $placeMarketOrderBook->runners[$selectionId]->ex->availableToBack[0]->price){
								
								$fairPlacePrice=999;
							}
							
							//convert to fair place prob of horse
							$fairPlaceProb = 1/$fairPlacePrice;
							
							//Check if adding an extra place..
							if($extraPlace){
								
								$fairPlaceProb = ((($fairPlaceProb-$fairWinProb)/$placeMarketOrderBook->numberOfWinners)*($placeMarketOrderBook->numberOfWinners+1)) +$fairWinProb;
							}
							
							//use formula to find break even price of horse ew odds
							$eachWayMultiplier = $eachWayTerms['multiplier'];
							$breakEventPrice = (2 - $fairPlaceProb + $eachWayMultiplier*$fairPlaceProb)/($fairWinProb + $eachWayMultiplier*$fairPlaceProb);
							
							//Use forumale to calucalte best return on the horse
							$bestBookmakerPlace = (($BetfairRunner->bestBookmakerPrice-1)*$eachWayMultiplier)+1;
							$bestExpectedReturn = $BetfairRunner->bestBookmakerPrice*$fairWinProb + $bestBookmakerPlace*$fairPlaceProb;
							$bestReturn = ($bestExpectedReturn - 2)/2;
							
							//the arb value to the winmarket runners, with rounding	
							$BetfairRunner->fairWinPrice=round($fairWinPrice,2);
							$BetfairRunner->fairWinProb=round($fairWinProb,2);
							$BetfairRunner->fairPlacePrice=round($fairPlacePrice,2);
							$BetfairRunner->fairPlaceProb=round($fairPlaceProb,2);
							$BetfairRunner->breakEvenPrice=round($breakEventPrice,2);
							$BetfairRunner->bestReturn=round($bestReturn,4);
							$BetfairRunner->edgeFairPrice = $BetfairRunner->breakEvenPrice;
							
							$BetfairRunner->winAvailableToBack = $winMarketOrderBook->runners[$selectionId]->ex->availableToBack;
							$BetfairRunner->winAvailableToLay = $winMarketOrderBook->runners[$selectionId]->ex->availableToLay;
							$BetfairRunner->placeAvailableToBack = $placeMarketOrderBook->runners[$selectionId]->ex->availableToBack;
							$BetfairRunner->placeAvailableToLay = $placeMarketOrderBook->runners[$selectionId]->ex->availableToLay;			
							$BetfairRunner->liquid=$BetfairRunner->winAvailableToLay[0]->size+$BetfairRunner->placeAvailableToLay[0]->size;			
							
							//Set the horse number
							//$BetfairRunner->horseNumber = $winMarketOrderBook->runners[$selectionId]->ex->availableToLay;			
							
							###Kelly criterion time###
							//Only calculate the kelly if there is an Edge (kelly function will do this but save time here)
							if($bestReturn>0){
								
								//Build outcomes array for EW Bet
								$winOutcome = new stdClass;
								$winOutcome->probability = $fairWinProb;
								$winOutcome->profit = ($BetfairRunner->bestBookmakerPrice+$bestBookmakerPlace-2);
								
								$placeOutcome = new stdClass;
								$placeOutcome->probability = $fairPlaceProb-$fairWinProb;
								$placeOutcome->profit = $bestBookmakerPlace-2;
								
								$loseOutcome = new stdClass;
								$loseOutcome->probability = 1-$fairPlaceProb;
								$loseOutcome->profit = -2;
								
								$outcomes = array($winOutcome,$placeOutcome,$loseOutcome);
								
								$kellyPercent = valueFinder::kellyCriterion($outcomes);
								$BetfairRunner->edgeType='EW';
								$BetfairRunner->edgeBetTypeId=2;
								
							}else{
								
								$kellyPercent = 0;
							}
							
							//Check for a straight win edge
							if( $BetfairRunner->bestBookmakerPrice > $fairWinPrice ){

								$straightEdge = ($BetfairRunner->bestBookmakerPrice - $fairWinPrice)/$fairWinPrice;
								$straightKelly = $straightEdge/($BetfairRunner->bestBookmakerPrice-1);
								$straightGrowth = $straightEdge*$straightKelly;
								
								//Check if the straight win provides a higher growth...
								if( $straightGrowth > $kellyPercent*$bestReturn ){
									
									$kellyPercent = $straightKelly;
									$BetfairRunner->bestReturn=round($straightEdge,4);
									$BetfairRunner->edgeType='WIN';
									$BetfairRunner->edgeBetTypeId=1;
									$BetfairRunner->liquid=$BetfairRunner->winAvailableToLay[0]->size;
									$BetfairRunner->edgeFairPrice = $fairWinPrice;
								}
								
							}
							
							$BetfairRunner->fractionalOdds = $Odd->getFractional($BetfairRunner->bestBookmakerPrice);
							$BetfairRunner->kellyPercent = $kellyPercent;
							
							$BetfairRunner->totalMatched = $winMarketOrderBook->runners[$selectionId]->totalMatched;
							$BetfairRunner->lastPriceTraded = $winMarketOrderBook->runners[$selectionId]->lastPriceTraded;
						}
						
					}
					
				}
			}
			
			return $BetfairRunners;
		}
		
		
		
		//Function returns kelly percent for investment
		//Outcomes is an array of results, with stdObjects; with parameters: $probability & $profit
		static function kellyCriterion($outcomes = array()){
			
			//Check there is outcomes
			if(count($outcomes)>0){
				
				$expectedProfit = 0;
				$probabilites = array();
				$profits = array();
				
				//Check that there is a positive edge before calculating kelly
				foreach($outcomes as $outcome){
				
					$expectedProfit += $outcome->probability*$outcome->profit;
					$probabilites[] = $outcome->probability;
					$profits[] = $outcome->profit;
				}
				
								
				if($expectedProfit>0){
					
					$valInitialValue = 0.25;
					$valThreshold = 0.00001;
					
					$valPast = 0;
					$valNext = $valInitialValue;

					//b = matCurr(:,1); PROFIT COLUMN
					//p = matCurr(:,2); PROBABILITY COLUMN
					$maxLoop = 100;
					$loopCounter=0;
					
					
					$boolBump = 0;
					while (abs($valPast-$valNext) > $valThreshold && $loopCounter<$maxLoop){
					    $valPast = $valNext;
					
						$valNumerator=0;
						$valDenominator=0;
						
						/*
						foreach($outcomes as $outcome){
							
							$valNumerator+=($outcome->probability*$outcome->profit)/(1+$outcome->profit*$valPast);
							$valDenominator+=((-($outcome->profit^2))*$outcome->probability)/((1+($outcome->profit*$valPast))^2);
						}
						*/
						
						$valNum1 = valueFinder::vectorMaths($probabilites, $profits, "*");
						$valNum2 = valueFinder::vectorMaths($profits, $valPast, "*");
						$valNum3 = valueFinder::vectorMaths($valNum2, 1, "+");
						$valNum4 = valueFinder::vectorMaths($valNum1, $valNum3, "/");
						
						$valNumerator = array_sum($valNum4);
						
						
						$valDen1 = valueFinder::vectorMaths($profits, 2, "^");
						$valDen2 = valueFinder::vectorMaths($valDen1, -1, "*");
						$valDen3 = valueFinder::vectorMaths($valDen2, $probabilites, "*");
						
						$valDen4 = valueFinder::vectorMaths($profits, $valPast, "*");
						$valDen5 = valueFinder::vectorMaths($valDen4, 1, "+");
						$valDen6 = valueFinder::vectorMaths($valDen5, 2, "^");
						
						$valDen7 = valueFinder::vectorMaths($valDen3, $valDen6, "/");
						$valDenominator = array_sum($valDen7);
						
					    //$valNumerator = sum(p.*b./(1+b*$valPast));
					    //$valDenominator = sum(-b.^2.*p./(1+b*$valPast).^2);
					
					    $valNext = $valPast - $valNumerator/$valDenominator;
					    //logness("Numerator: ".$valNumerator);
					    //logness("Denominator: ".$valDenominator);
					    //logness("val past: ".$valPast);
					    //logness("valNext: ".$valNext);
					
					    if ($valNext < 0 && !$boolBump){
					        $valNext = 0;
					        $boolBump = 1;
					    }
					    
					    $loopCounter++;
					}
					//logness($outcomes);
					//customExit();
					$valPercent = $valNext;
					
					return $valPercent;
					
					
					//customExit();
				}
				
			}else{
				
				return 0;
			}
		}
		
		
		static function vectorMaths($array1, $array2, $operation){
			
			$results = array();
			
			if(count($array1) == count($array2) && is_array($array1) && is_array($array2)){
				
				foreach( $array1 as $index=>$element){
					
					if($operation=="+"){
						
						$results[]=$element+$array2[$index];
					}else if($operation=="-"){
						
						$results[]=$element-$array2[$index];
					}else if($operation=="/"){
						
						$results[]=$element/$array2[$index];
					}else if($operation=="*"){
						
						$results[]=$element*$array2[$index];
					}
				}
			}else if(is_array($array1) && is_numeric($array2)){
				
				foreach( $array1 as $index=>$element){
					
					if($operation=="+"){
						
						$results[]=$element+$array2;
					}else if($operation=="-"){
						
						$results[]=$element-$array2;
					}else if($operation=="/"){
						
						$results[]=$element/$array2;
					}else if($operation=="*"){
						
						$results[]=$element*$array2;
					}else if($operation=="^"){
						
						$results[]=pow($element, $array2);
					}
				}
			}
			
			return $results;
		}
	}