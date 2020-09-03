<!-- Page, data-page contains page name-->
<div data-page="newBet" class="page">
	<div class="page-content">
		<?
				if($Bet->BetTypeId==2){
					
					$type='EW';
					$color='green';
	
				}else if($Bet->BetTypeId==1){
					
					$type='WIN';
					$color='blue';
				}
			?>	
		<div class="card facebook-card">
				  <div class="card-header">
				    <div class="facebook-avatar"><img src="https://content-cache.cdnbf.net/feeds_images/Horses/SilkColours/<?=$Bet->BetfairRunner->colorsFilename;?>" width="34" height="28"></div>
				    <div class="facebook-name">
					    <div class="row">
						    <div class="col-50"><?=$Bet->BetfairRunner->name;?></div>
						    <div class="col-50 color-<?=$color;?>"><?=$type;?> <b>€<?=$Bet->stake;?></b></div>
					    </div>
					</div>
				  </div>
				  <div class="card-content">
				    <div class="card-content-inner color-green">
						  <b>Bet added!</b>
					    
				    </div>
				  </div>
				</div>  
		
		<div class="content-block">
			<a  data-ignore-cache="true" href="index.php?controller=ValueBet&bookie=<?=$bookieFilter;?>" class="button form-to-json color-orange">Back to Value</a>
		</div>

	</div>
</div>
