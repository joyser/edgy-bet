<h1 class="ui header">Value Bets</h1>



<table class="ui blue very compact selectable table" style="width: 95%">
  <thead>
    <tr>
	<th>Horse</th>
    <th>Race</th>
    <th>FP</th>
	<th>Price</th>
    <th>Bookie</th>
    <th>Type</th>
    <th>L</th>
    <th>~M</th>
    <th>~T</th>
    <th class="right aligned">Edge</th>
    <th class="right aligned">Kelly</th>
    <th class="right aligned">Profit</th>
    <th>Age</th>
  </tr></thead>
  <tbody>
	  <?
		//logness($ValueBets);
		//customExit();
		$totalGrowth=0;
		
		foreach( $ValueBets as $ValueBet ){
			
			
			$totalGrowth+=$ValueBet->growth;
			
			if($ValueBet->edgeType=='EW'){
				
				$type='<b><font color="#75FF76">EW</font></b>';

			}else if($ValueBet->edgeType=='WIN'){
				
				$type='<b><font color="#33ccff">WIN</font></b>';
			}
			
			//colors for liquid
			if( $ValueBet->liquid < 16){
				
				$liquidColor = "#B9B9B9";
				
			}else if( $ValueBet->liquid < 30){
				
				$liquidColor = "#6D6D6D";
			}else{
				
				$liquidColor = "#000000";
			}
			
			//colors for matched
			if( $ValueBet->BetfairMarket->totalMatched < 15000){
				
				$matchedColor = "#B9B9B9";
				
			}else if( $ValueBet->BetfairMarket->totalMatched < 85000){
				
				$matchedColor = "#6D6D6D";
			}else{
				
				$matchedColor = "#000000";
			}
			
			//colors for traded
			$tradePercent = ($ValueBet->tradedVolume / ($ValueBet->BetfairRunner->totalMatched/2));
			
			if( $tradePercent < 0.1){
				
				$tradeColor = "#B9B9B9";
				
			}else if( $tradePercent < 0.4){
				
				$tradeColor = "#6D6D6D";
			}else{
				
				$tradeColor = "#000000";
			}
			
			if( $ValueBet->tradedVolume > 1000 ){
				
				$tradeVolumeString = round($ValueBet->tradedVolume/1000)."k";
			}else{
				
				$tradeVolumeString = round($ValueBet->tradedVolume);
			}
			
			
			echo "<td><img src=\"https://content-cache.cdnbf.net/feeds_images/Horses/SilkColours/".$ValueBet->BetfairRunner->colorsFilename."\"/> <a href=\"index.php?controller=Bet&action=NewBet&BetfairRunnerId=".$ValueBet->BetfairRunner->id."&betfairMarketId=".$ValueBet->BetfairMarket->betfairId."&defaultPrice=".$ValueBet->bestBookmakerPrice."&defaultStake=".$ValueBet->stake."&defaultBetTypeId=".$ValueBet->BetType->id."\">".$ValueBet->BetfairRunner->name."</a></td>".
				 "<td><a class=\"item\" href=\"index.php?controller=Race&action=viewRace&winMarketId=".$ValueBet->BetfairMarket->betfairId."\">".$ValueBet->shortTime." ".$ValueBet->BetfairEvent->name."</a></td>".
				 "<td>".$ValueBet->fairPrice."</td>".
				 "<td>".$ValueBet->bestBookmakerPrice."(".$ValueBet->fractionalOdds.")</td>".
				 "<td>".$ValueBet->bestBookmakerList."</td>".
				 "<td>".$type."</td>".
				 "<td><font color=\"".$liquidColor."\">".$ValueBet->liquid."</font></td>".
				 "<td><font color=\"".$matchedColor."\">".round(($ValueBet->BetfairMarket->totalMatched/1000))."k</font></td>".
				 "<td><font color=\"".$tradeColor."\">".$tradeVolumeString." - ".round($tradePercent*100)."%</font></td>".
				 "<td class=\"right aligned\">".round(100*$ValueBet->edge,1)."%</td>".
				 "<td class=\"right aligned\">".round(100*$ValueBet->kellyPercent,1)."% (€".$ValueBet->stake.")</td>".
				 "<td class=\"right aligned\">€".$ValueBet->grow."</td>".
				 "<td><div id=\"ValueBetAge".$ValueBet->id."\">-00:00</div></td>".
			 "</tr>";
				
			//Print age timers...
			?>
			<script>
			jQuery(function ($) {
					    
			    display = $('#ValueBetAge<?=$ValueBet->id;?>');
			    refreshTimer(<?=$ValueBet->age;?>, 0, display);
			    
			});
		</script>
			
			<?
				
		}
		
		  
		?>
    <tfoot>
		<th colspan="11"><div id="valueRefreshTimer">-00:00</div></th>
		<th class="right aligned"><?=round(100*$totalGrowth,2);?>%</th>
		<th></th>
	</tr></tfoot>
	</tbody>
</table>
<script>
	jQuery(function ($) {

	    display = $('#valueRefreshTimer');
	    refreshTimer(0, <?=$valueRefreshTime;?>, display);
	    
	    
	});
</script>

<a href="index.php?controller=ValueBet&bookie=PP">
<button class="ui green button">
  PP
</button>
</a>

<a href="index.php?controller=ValueBet&bookie=B3">
<button class="ui yellow button">
  B3
</button>
</a>

<a href="index.php?controller=ValueBet&bookie=WH">
<button class="ui blue button">
  WH
</button>
</a>

<a href="index.php?controller=ValueBet&bookie=LD">
<button class="ui red button">
  LD
</button>
</a>

<a href="index.php?controller=ValueBet&bookie=BY">
<button class="ui blue button">
  BY
</button>
</a>

<a href="index.php?controller=ValueBet&bookie=CE">
<button class="ui yellow button">
  CE
</button>
</a>

<a href="index.php?controller=ValueBet&bookie=VC">
<button class="ui grey button">
  VC
</button>
</a>


