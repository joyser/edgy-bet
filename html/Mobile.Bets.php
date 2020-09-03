<h1 class="ui header"><?=$title;?></h1>



<table class="ui blue very compact selectable table" style="width: 95%">
  <thead>
    <tr>
	<th>Horse</th>
    <th>Race</th>
    <th>Price</th>
    <th class="right aligned">Stake (€)</th>
    <!--
    <th class="right aligned">E. Return (€)</th>
    <th class="right aligned">E. Profit (€)</th>
    -->
    <th>Type</th>
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
			
			
			
			
			
			if($ValueBet->edgeType=='EW'){
				
				$type='<b><font color="#75FF76">EW</font></b>';

			}else if($ValueBet->edgeType=='WIN'){
				
				$type='<b><font color="#33ccff">WIN</font></b>';
			}
			
			/*
			$Bet->profit = $Bet->value - $Bet->stake;
			
			if( $Bet->profit < 0){
				
				$profitString = "<font color=\"red\"><b>".money_format("%i",$Bet->profit)."</b></font>";
			}else{
				
				$profitString = "<font color=\"green\"><b>".money_format("%i",$Bet->profit)."</b></font>";
			}
			*/
			//Add values to totals
			$totalStake+=$Bet->stake;
			$totalValue+=$Bet->value;
			$totalProfit+=$Bet->profit;
			
								
			echo "<td><img src=\"https://content-cache.cdnbf.net/feeds_images/Horses/SilkColours/".$Bet->BetfairRunner->colorsFilename."\"/> ".$Bet->BetfairRunner->name."</td>".
				 "<td><a class=\"item\" href=\"index.php?controller=Race&action=viewRace&winMarketId=".$Bet->BetfairMarket->betfairId."\">".substr($Bet->BetfairMarket->startTime, 11, 5)." ".$Bet->BetfairEvent->name."</a></td>".
				 "<td>".$Bet->price."</td>".
				 "<td class=\"right aligned\">".money_format("%i",$Bet->stake)."</td>".
				 //"<td class=\"right aligned\">".money_format("%i",$Bet->value)."</td>".
				 //"<td class=\"right aligned\">".$profitString."</td>".
				 "<td>".$Bet->BetType->abbreviation."</td>".
				 "<td>".$Bet->logTime."</td>".
				 "<td>".$Bet->User->name."</td>".
				 "<td><a href=\"index.php?controller=Bet&action=updateBetStatus&BetId=".$Bet->id."&BetStatusId=3\"><button class=\"ui compact orange button\">Cancel</button></a></td>".
			 "</tr>";
				
			
				
		}
		
		/*
		if( $totalProfit < 0){
				
			$profitString = "<font color=\"red\"><b>".money_format("%i",$totalProfit)."</b></font>";
		}else{
			
			$profitString = "<font color=\"green\"><b>".money_format("%i",$totalProfit)."</b></font>";
		}
		*/	
		  
		?>
	    <tfoot>
	    <th></th>
	    <th></th>
	    <th></th>
	    <th class="right aligned"><?=money_format("%i",$totalStake);?></th>
	    <!--
	    <th class="right aligned"><?=money_format("%i",$totalValue);?></th>
	    <th class="right aligned"><?=$profitString;?></th>
	    -->
	    <th></th>
	    <th></th>
	    <th></th>
	    <th></th>
	    	
	  </tr></tfoot>
	</tbody>
</table>



<!-- Page, data-page contains page name-->
<div data-page="Bets" class="page">
			
	<div class="page-content">
    <div class="content-block-title"><?=$title;?></div>
    <div class="list-block">
      <ul>
        
        <?
		
		$totalStake = 0;
		$totalValue = 0;
		$totalProfit = 0;
		foreach( $Bets as $Bet ){
			
			
			//Add values to totals
			$totalStake+=$Bet->stake;
			$totalValue+=$Bet->value;
			$totalProfit+=$Bet->profit;
			
			?>
			<li class="item-content">
	          <div class="item-media"><img src="https://content-cache.cdnbf.net/feeds_images/Horses/SilkColours/<?=$Bet->BetfairRunner->colorsFilename;?>"/></div>
	          <div class="item-inner">
	            <div class="item-title"><?=$Bet->BetfairRunner->name;?></div>
	            <div class="item-after"><?=substr($Bet->BetfairMarket->startTime, 11, 5);?> - <?=$Bet->BetType->abbreviation;?> - €<?=money_format("%i",$Bet->stake);?></div>
	          </div>
	        </li>
			<?
		}
		?>
		
		
      </ul>  
    </div>
</div>

