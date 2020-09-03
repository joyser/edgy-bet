<div class="ui visible left sidebar inverted vertical menu">
    <div class="item">
	    <div class="header">
		    <img src="images/whiteHorse.png" > EdgyBet v1.2
	    </div>
    </div>
    <div class="menu">
	    <div class="item">
		    <?=$userName;?> (<?=$userIpAddress;?>) | <a style="color:#00b5ad" href="index.php?controller=login&action=logout">Logout</a>
	    </div>
	    
    </div>
    
    <div class="header"><a class="item" href="index.php?controller=ValueBet">
					Summary		    	 
	</a></div>
	<div class="header"><a class="item" href="index.php?controller=Bet">
					My Bets		    	 
	</a></div>
	<div class="header"><a class="item" href="index.php?controller=User">
					Settings		    	 
	</a></div>
	<div class="header"><a class="item" href="index.php?action=toMobile">
					Mobile site		    	 
	</a></div>
		    	 
    <?
	    
	    foreach( $BetfairEvents as $Event ){
		    
		    echo "".
		    	 "<div class=\"header\"><a class=\"item\" href=\"index.php?controller=Race&action=viewMeeting&BetfairEventId=".$Event->betfairId."\">".
		    	 "<i class=\"".strtolower($Event->country)." flag\"></i>".
		    	 $Event->name.
		    	 "</a></div>".
		    	 "<div class=\"menu\">";
		    
		    
		    foreach( $Event->Markets as $Market ){
			     
		    	echo "<a class=\"item\" href=\"index.php?controller=Race&action=viewRace&winMarketId=".$Market->betfairId."\">".
		    		 $Market->shortTime." ".$Market->name.
					 "</a>";
			    
		    }
		    
		    echo "</div>";
	    }
	    
	    
	?>
    
  </div>
  <div class="pusher">
	  <div class="article">
    <!-- Site content !-->
	<div class="main ui container" style="margin-left: 30px!important">
		
	
       