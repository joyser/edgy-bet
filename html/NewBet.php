<h1 class="ui header">Record Bet</h1>

<?
	if($dataError){
	
	?>	
	<h5 class="ui red header">Error getting runner details</h5>

	
	<?
			
	}else{
		?>
		
<h5 class="ui blue header"><?=$raceTitle;?></h5>
<h5 class="ui orange header"><?=$BetfairRunner->name;?></h5>

<form action="index.php?controller=Bet&action=SaveBet" method="POST">
<div class="ui action input">
  <input type="text" value="<?=$defaultStake;?>" name="stake" placeholder="€ Stake..(total)">
  <input type="text" value="<?=$defaultPrice;?>" name="price" placeholder="Price...">
  <input type="hidden" name="BetfairRunnerId" value="<?=$BetfairRunner->id;?>">
  <input type="hidden" name="betfairMarketId" value="<?=$BetfairMarket->betfairId;?>">
  <select name="BetTypeId" class="ui compact selection dropdown">
	  
	  <?
		  foreach( $BetTypes as $BetType ){
			  
			  if($defaultBetTypeId == $BetType->id)
			  	$selectedString=" selected=\"\" ";
			  else
			  	$selectedString="";
			  
			  echo "<option ".$selectedString." value=\"".$BetType->id."\">".$BetType->abbreviation."</option>";
		  }
		  
		?>
  </select>
  <select name="BookmakerId" class="ui compact selection dropdown">
	  
	  <?
		  foreach( $Bookmakers as $Bookmaker ){
			  
			  echo "<option value=\"".$Bookmaker->id."\">".$Bookmaker->abbreviation."</option>";
		  }
		  
		?>
  </select>
  
  
</div>
  <input type="submit" value="Add bet" class="ui green button"/>

</form>


		
		<?
	}
	
	?>
