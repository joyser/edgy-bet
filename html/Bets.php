<h1 class="ui header"><?=$title;?></h1>



<table class="ui blue very compact selectable table" style="width: 95%">
  <thead>
    <tr>
	<th>Horse</th>
    <th>Race</th>
    <th>Price</th>
    <th class="right aligned">Stake (€)</th>
    <th class="right aligned">Return (€)</th>
    <th class="right aligned">Profit (€)</th>
    <th>Type</th>
    <th>PT</th>
    <th>Time saved</th>
    <th>User</th>
    <th>Cancel</th>
  </tr></thead>
  <tbody>
	  <?
		
		$totalStake = 0;
		$totalValue = 0;
		$totalProfit = 0;
		foreach( $Bets as $Bet ){
			
			
			$betReturn = $Bet->getReturn();
			$Bet->profit = $betReturn - $Bet->stake;
			
			//Only do profit if the market has results
			if( $Bet->BetfairMarket->hasResults ){
				
				if( $Bet->profit < 0){
				
					$profitString = "<font color=\"red\"><b>".money_format("%i",$Bet->profit)."</b></font>";
				}else{
					
					$profitString = "<font color=\"green\"><b>".money_format("%i",$Bet->profit)."</b></font>";
				}
				
				$totalProfit+=$Bet->profit;
				
				if( $Bet->BetfairMarket->runners > 5 && $Bet->BetfairMarket->runners < 8){
					
					$placeTerms = 0.25;
				}else if( $Bet->BetfairMarket->runners > 7 && $Bet->BetfairMarket->runners < 12){
					
					$placeTerms = 0.2;
					
				}else if($Bet->BetfairMarket->runners > 11 && $Bet->BetfairMarket->isHandicap ){
					$placeTerms = 0.25;
					
				}else{
					
					$placeTerms = 0.2;
				}
				
			}else{
				
				$profitString = "<font color=\"orange\"><b>-</b></font>";
				$placeTerms="";
			}
			

			//Add values to totals
			$totalStake+=$Bet->stake;
			$totalReturn+=$betReturn;
			
			
								
			echo "<td><img src=\"https://content-cache.cdnbf.net/feeds_images/Horses/SilkColours/".$Bet->BetfairRunner->colorsFilename."\"/> ".$Bet->BetfairRunner->name."</td>".
				 "<td><a class=\"item\" href=\"index.php?controller=Race&action=viewRace&winMarketId=".$Bet->BetfairMarket->betfairId."\">".substr($Bet->BetfairMarket->startTime, 11, 5)." ".$Bet->BetfairEvent->name."</a></td>".
				 "<td>".$Bet->price."</td>".
				 "<td class=\"right aligned\">".money_format("%i",$Bet->stake)."</td>".
				 "<td class=\"right aligned\">".money_format("%i",$betReturn)."</td>".
				 "<td class=\"right aligned\">".$profitString."</td>".
				 "<td>".$Bet->BetType->abbreviation."</td>".
				 "<td>".$placeTerms."</td>".
				 "<td>".$Bet->logTime."</td>".
				 "<td>".$Bet->User->name."</td>".
				 "<td><a href=\"index.php?controller=Bet&action=updateBetStatus&BetId=".$Bet->id."&BetStatusId=2\"><button class=\"ui compact orange button\">Cancel</button></a></td>".
			 "</tr>";
				
			
				
		}
		
		if( $totalProfit < 0){
				
			$profitString = "<font color=\"red\"><b>".money_format("%i",$totalProfit)."</b></font>";
		}else{
			
			$profitString = "<font color=\"green\"><b>".money_format("%i",$totalProfit)."</b></font>";
		}
		  
		?>
	    <tfoot>
	    <th></th>
	    <th></th>
	    <th></th>
	    <th class="right aligned"><?=money_format("%i",$totalStake);?></th>
	    <th class="right aligned"><?=money_format("%i",$totalReturn);?></th>
	    <th class="right aligned"><?=$profitString;?></th>
	    <th></th>
	    <th></th>
	    <th></th>
	    <th></th>
	    <th></th>
	    	
	  </tr></tfoot>
	</tbody>
</table>


