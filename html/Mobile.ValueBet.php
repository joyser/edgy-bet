<!-- Page, data-page contains page name-->
          <div data-page="valueBets" class="page">
			
			<div class="page-content pull-to-refresh-content" data-ptr-distance="55">
				<!-- Default pull to refresh layer-->
			    <div class="pull-to-refresh-layer">
			      <div class="preloader"></div>
			      <div class="pull-to-refresh-arrow"></div>
			    </div>
			  <style>
				  .facebook-card .card-header {
				    display: block;
				    padding: 10px;
				  }
				  .facebook-card .facebook-avatar {
				    float: left;
				  }
				  .facebook-card .facebook-name {
				    margin-left: 44px;
				    font-size: 14px;
				    font-weight: 500;
				  }
				  .facebook-card .facebook-date {
				    margin-left: 44px;
				    font-size: 13px;
				    color: #8e8e93;
				  }
				  .facebook-card .card-footer {
				    background: #fafafa;
				  }
				  .facebook-card a,  {
				    color: #ff0000;
				    font-weight: 500;
				  }
				  .facebook-card .card-content img {
				    display: block;
				  }
				  .facebook-card .card-content-inner {
				    padding: 15px 10px;
				  }  
				</style>
				<div class="content-block">
					<div class="row">
					  <div class="col-25">
					    <a data-ignore-cache="true" href="index.php?controller=ValueBet&bookie=PP" class="button button-big button-red color-green">PP</a>
					  </div>
					  <div class="col-25">
					    <a data-ignore-cache="true" href="index.php?controller=ValueBet&bookie=LD" class="button button-big button-red color-red">LD</a>
					  </div>
					  <div class="col-25">
					    <a data-ignore-cache="true" href="index.php?controller=ValueBet&bookie=BY" class="button button-big button-red color-blue">BY</a>
					  </div>
					  <div class="col-25">
					    <a data-ignore-cache="true" href="index.php?controller=ValueBet" class="button button-big">All</a>
					  </div>
					</div>
				</div>
				
				<div class="content-block-title"><?=$betCount;?> bets placed - €<?=$totalStake;?> staked - Value bets:</div>
			 
				
				
				<?
		
		foreach( $ValueBets as $ValueBet ){
			
			if( !$ValueBet->hideOnMobile ){
				if($ValueBet->edgeType=='EW'){
					
					$type='EW';
					$color='green';
	
				}else if($ValueBet->edgeType=='WIN'){
					
					$type='WIN';
					$color='blue';
				}
									
					
				//Print age timers...
				?>
				<div class="card facebook-card">
				  <div class="card-header">
				    <div class="facebook-avatar"><img src="https://content-cache.cdnbf.net/feeds_images/Horses/SilkColours/<?=$ValueBet->BetfairRunner->colorsFilename;?>" width="34" height="28"></div>
				    <div class="facebook-name">
					    <div class="row">
						    <div class="col-50"><?=$ValueBet->BetfairRunner->name;?></div>
						    <div class="col-15 color-orange"><?=$ValueBet->fractionalOdds?></div>
						    <div class="col-35 color-<?=$color;?>"><?=$type;?> <b>€<?=$ValueBet->mobileStake;?></b></div>
					    </div>
					    
					    
				    </div>
				    <div class="facebook-date"><?=$ValueBet->shortTime." ".$ValueBet->BetfairEvent->name;?></div>
				  </div>
				  <div class="card-content">
				    <div class="card-content-inner">
				      <div class="row">
						  <div class="col-60"><?=$ValueBet->bestBookmakerList;?></div>
						  <div class="col-40">
							  <a data-ignore-cache="true" href="index.php?controller=Bet&action=NewBet&BetfairRunnerId=<?=$ValueBet->BetfairRunner->id;?>&betfairMarketId=<?=$ValueBet->BetfairMarket->betfairId;?>&defaultPrice=<?=$ValueBet->bestBookmakerPrice;?>&defaultStake=<?=$ValueBet->mobileStake;?>&defaultBetTypeId=<?=$ValueBet->BetType->id;?>&bookieFilter=<?=$bookie;?>" class="button button-big button-red color-yellow">Add Bet</a>
							  
							  </div>
					    </div>
					    
				    </div>
				  </div>
				</div>
				
				<?
			}
		}
		
		  
		?>
	</div>
