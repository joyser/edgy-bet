<!-- Page, data-page contains page name-->
<div data-page="newBet" class="page">
	<div class="page-content">
		<div class="content-block-title">Record Bet </div>
		<?
		if($dataError){
		
		?>	
		Error getting runner details
	
		<?
				
			}else{
				
				if($defaultBetTypeId==2){
					
					$type='EW';
					$color='green';
	
				}else if($defaultBetTypeId==1){
					
					$type='WIN';
					$color='blue';
				}
		?>
		<form action="index.php?controller=Bet&action=SaveBet&bookie=<?=$bookieFilter;?>" method="POST">
		<div class="list-block">
		  <ul>
			<li class="item-content">
	          <div class="item-inner">
	            <div class="item-title color-orange"><?=$BetfairRunner->name;?> @ <?=$raceTitle;?></div>
	          </div>
	        </li>
		    <!-- Text inputs -->
		    <li>
		      <div class="item-content">
		        <div class="item-inner">
		          <div class="item-title label color-gray">Total Stake €</div>
		          <div class="item-input">
		            <input type="number" name="stake" placeholder="Stake" value="<?=$defaultStake;?>">
		          </div>
		        </div>
		      </div>
		    </li>
		    <li>
		      <div class="item-content">
		        <div class="item-inner">
		          <div class="item-title label color-gray">Price</div>
		          <div class="item-input">
		            <input type="hidden" name="price" placeholder="Odds" value="<?=$defaultPrice;?>">
		            <b><?=$defaultPriceFractional;?></b>
		          </div>
		        </div>
		      </div>
		    </li>
		    <!-- Select -->
		    <li>
		      <div class="item-content">
		        <div class="item-inner">
		          <div class="item-title label color-gray">Type</div>
		          <div class="item-input">
			        <input type="hidden" name="BetTypeId" value="<?=$defaultBetTypeId;?>">
			            <div class="color-<?=$color;?>"><?=$type;?></div>
		          </div>
		        </div>
		      </div>
		    </li>
		  </ul>
		</div>
		<input type="hidden" name="BetfairRunnerId" value="<?=$BetfairRunner->id;?>">
		<input type="hidden" name="betfairMarketId" value="<?=$BetfairMarket->betfairId;?>">
		<div class="content-block">
		  <input type="submit" value="Confirm" class="button color-green form-to-json"/>
		</div>
		
		</form>
		
		<div class="content-block">
			<a  data-ignore-cache="true" href="index.php?controller=ValueBet&bookie=<?=$bookieFilter;?>" class="button form-to-json color-red">Cancel</a>
		</div>

	</div>
</div>

<?
}	
?>