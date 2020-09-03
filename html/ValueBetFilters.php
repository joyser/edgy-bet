
<form action="index.php?controller=Bet" method="GET">

<input type="hidden" value="ValueBet" name="controller" >
<div class="ui form">
	<div class="fields">
		<div class="two wide field">
	    	<label>Min. Edge (%)</label>
			<input type="text" value="<?=$minimumEdge;?>" name="minimumEdge">
		</div>
		<div class="two wide field">
	    	<label>Min. Liquid (€)</label>
			<input type="text" value="<?=$minimumLiquid;?>" name="minimumLiquid">
		</div>
		<div class="two wide field">
	    	<label>Min. Growth (‱)</label>
			<input type="text" value="<?=$minimumGrowth;?>" name="minimumGrowth">
		</div>
		<div class="two wide field">
	    	<label>Min. price</label>
			<input type="text" value="<?=$minimumPrice;?>" name="minimumPrice">
		</div>
		<div class="two wide field">
	    	<label>Max Price</label>
			<input type="text" value="<?=$maxPrice;?>" name="maxPrice">
		</div>
		<div class="two wide field">
	    	<label>Bookies</label>
			<select name="bookie" class="ui selection dropdown">
			 	<option value="">All</option>
			  <?
				  foreach( $Bookmakers as $Bookmaker){
					  ?>
					  
					  <option <?=($bookie==$Bookmaker->abbreviation)?"selected=\"true\"":""?> value="<?=$Bookmaker->abbreviation;?>"><?=$Bookmaker->abbreviation;?></option>
					  <?
				  }
				?>
			  
		  </select>
		  
			
		</div>
		
	</div>
	<input type="submit" class="ui blue button" value="Filter" name="submit" >
</div>
</form>