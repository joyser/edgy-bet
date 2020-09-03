<h1 class="ui header"><?=$raceTitle;?></h1>
<h5 class="ui header"><a href="index.php?controller=Race&action=viewRace&winMarketId=<?=$winMarketId;?>&extraPlace=1">View Extra place</a></h5>
<h5 class="ui header"><a href="index.php?controller=Race&action=viewRace&winMarketId=<?=$winMarketId;?>&quarterOdds=1">Force 1/4 place</a></h5>
<h5 class="ui header"><a href="index.php?controller=Race&action=viewRace&winMarketId=<?=$winMarketId;?>&quarterOdds=1&extraPlace=1">1/4 place odds and extra place</a></h5>
<table class="ui blue very compact selectable table" style="width: 95%">
  <thead>
    <tr><th>Name (<?=$activeRunners;?> runners, €<?=round(($totalMatched/1000),1);?>k)</th>
    <th class="center aligned" colspan="2">Fair win odds</th>
    <th>P(win)</th>
    <th class="center aligned" colspan="2">Fair place odds</th>
    <th>P(place)</th>
    <th>EW FP</th>
    <th>Best</th>
    <th>Bookie</th>
    <th>Edge</th>
    <th>Kelly</th>
  </tr></thead>
  <tbody>
	  <?
		
		//logness($runners);
		//customExit(); 
		$winBackMargin=0;
		$winLayMargin=0;
		$placeBackMargin=0;
		$placeLayMargin=0;
		$bestPriceMargin=0;
		
		
		foreach( $runners as $runner ){
			
			if(!($runner->isNonRunner)){
				
				$winBackMargin+=1/($runner->winAvailableToBack[0]->price);
				$winLayMargin+=1/($runner->winAvailableToLay[0]->price);
				$placeBackMargin+=1/($runner->placeAvailableToBack[0]->price);
				$placeLayMargin+=1/($runner->placeAvailableToLay[0]->price);
				$bestPriceMargin+=1/($runner->bestBookmakerPrice);
				
				if( $runner->edgeType=='EW' ){
					
					echo "<tr style=\"background-color: #ccffcc;\">";
					
				}else if( $runner->edgeType=='WIN' ){
					
					echo "<tr style=\"background-color: #ccf2ff;\">";
				}else{
					
					echo "<tr>";
				}
				
				//Check if theres a kelly percent
				if($runner->kellyPercent){
					
					$stake = floor(($runner->kellyPercent*$bankroll)/10)*10;
					$kellyString = "".round($runner->kellyPercent*100,2)."% (€".$stake.")";
				
				}else{
					
					$stake="";
					$kellyString="";
				}
					
					echo "<td><img src=\"https://content-cache.cdnbf.net/feeds_images/Horses/SilkColours/".$runner->colorsFilename."\"/><a href=\"index.php?controller=Bet&action=NewBet&BetfairRunnerId=".$runner->id."&betfairMarketId=".$winMarketId."&defaultPrice=".$runner->bestBookmakerPrice."&defaultStake=".$stake."&defaultBetTypeId=".$runner->edgeBetTypeId."\">".$runner->number.". ".$runner->name."</a> (".round(($runner->totalMatched/1000))."k)</td>";
					
					if(!$placeMarketFound){
						
						echo "<td colspan=\"15\">No EW Market</td>".
						"</tr>";
						
					}else{
							
						
						 echo "<td class=\"center aligned\" style=\"background-color: #e5f3ff;\"><h5 class=\"ui header\">".$runner->winAvailableToBack[0]->price."<div class=\"sub header\">€".round($runner->winAvailableToBack[0]->size)."</div></h5></td>".
						 "<td class=\"center aligned\" style=\"background-color: #fde8ec;\"><h5 class=\"ui header\">".$runner->winAvailableToLay[0]->price."<div class=\"sub header\">€".round($runner->winAvailableToLay[0]->size)."</div></h5></td>".
						 "<td>".$runner->fairWinProb."</td>".
						 "<td class=\"center aligned\" style=\"background-color: #e5f3ff;\"><h5 class=\"ui header\">".$runner->placeAvailableToBack[0]->price."<div class=\"sub header\">€".round($runner->placeAvailableToBack[0]->size)."</div></h5></td>".
						 "<td class=\"center aligned\" style=\"background-color: #fde8ec;\"><h5 class=\"ui header\">".$runner->placeAvailableToLay[0]->price."<div class=\"sub header\">€".round($runner->placeAvailableToLay[0]->size)."</div></h5></td>".
						 "<td>".$runner->fairPlaceProb."</td>".
						 "<td class=\"right aligned\"><b>".$runner->breakEvenPrice."</b></td>".
						 "<td class=\"right aligned\">".$runner->bestBookmakerPrice."(".$runner->fractionalOdds.")</td>".
						 "<td >".$runner->bestBookmakerList."</td>".
						 "<td class=\"right aligned\">".($runner->bestReturn*100)."% </td>".
						 "<td style=\"color:purple;\" class=\"right aligned\">".$kellyString."</td>".
						 "</tr>";
					 }
			}else{
				
				echo "<tr class=\"disabled\">".
					 "<td><img src=\"https://content-cache.cdnbf.net/feeds_images/Horses/SilkColours/".$runner->colorsFilename."\"/> ".$runner->name."</td>".
					 "<td colspan=\"15\">Non-runner</td>".
					 "</tr>";
			}
		}
		
		  
		?>
    <tfoot>
    <th>
	    
	EW:
<?
	
	if($eachWayTerms['multiplier']){
		?>
		 1/<?=(1/$eachWayTerms['multiplier']);?>, <?=$eachWayTerms['places'];?> places
	<?}else{
		
		echo "NA";	
	}
	
	?>
	    
	    
    </th>
    <th style="color:#cccccc;"><?=round($winBackMargin*100,1);?>%</th>
    <th style="color:#cccccc;"><?=round($winLayMargin*100,1);?>%</th>
	<th style="color:#66ccff;"><div id="betfairCheckerRefresh<?=$marketId;?>">-00:00</div></th>
    <th style="color:#cccccc;"><?=round($placeBackMargin*100,1);?>%</th>
    <th style="color:#cccccc;"><?=round($placeLayMargin*100,1);?>%</th>
    <th></th>
    <th></th>
    <th style="color:#cccccc;"><?=round($bestPriceMargin*100,1);?>%</th>
    <th style="color:orange;"><div id="oddsCheckerRefresh<?=$marketId;?>">-00:00</div></th>
    <th></th>
    <th></th>

  </tr></tfoot>
  </tbody>
</table>
<script>
	jQuery(function ($) {

	    display = $('#oddsCheckerRefresh<?=$marketId;?>');
	    refreshTimer(<?=$secondsSinceOddsCheckerRefresh;?>, <?=$oddsCheckerRefreshTime;?>, display);
	    
	    display = $('#betfairCheckerRefresh<?=$marketId;?>');
	    refreshTimer(0, <?=$betfairRefreshTime;?>, display);
	    
	});
</script>

