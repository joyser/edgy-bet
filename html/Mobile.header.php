<!DOCTYPE html>
<html>
  <head>
	<script src="jquery.min.js"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>Edgybet.com</title>
    <!-- Path to Framework7 Library CSS-->
    <link rel="stylesheet" href="css/framework7.ios.min.css">
    <link rel="stylesheet" href="css/framework7.ios.colors.min.css">
    <!-- Path to your custom app styles-->
    <link rel="stylesheet" href="css/my-app.css">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico" />

  </head>
  <body>
    <!-- Status bar overlay for fullscreen mode-->
    <div class="statusbar-overlay"></div>
    <!-- Panels overlay-->
    <div class="panel-overlay"></div>
    <!-- Left panel with reveal effect-->
    <div class="panel panel-left panel-reveal">
      <div class="content-block">
        <p>User</p>
        
        <!-- Click on link with "close-panel" class will close panel -->
		<p><a data-ignore-cache="true" href="index.php?controller=ValueBet" class="close-panel">Value Bets</a></p>
		<p><a data-ignore-cache="true" href="index.php?controller=Bet"  class="close-panel">Placed Bets</a></p>
		<p><a data-ignore-cache="true" href="index.php?action=toDesktop" class="external">Desktop version</a></p>
		<p><a data-ignore-cache="true" href="index.php?action=logout" class="external">Log out</a></p>
      </div>
    </div>
    
    <!-- Views-->
    <div class="views">
      <!-- Your main view, should have "view-main" class-->
      <div class="view view-main">
        <!-- Top Navbar-->
        <div class="navbar">
          <div class="navbar-inner">
            <!-- We have home navbar without left link-->
            
            <div class="left">
              <!-- Right link contains only icon - additional "icon-only" class--><a href="#" class="link icon-only open-panel"> <i class="icon icon-bars"></i></a>
            </div>
            
            <div class="center sliding">EdgyBet.com</div>
            <div class="right">
	            <div id="valueRefreshTimer" class="color-red"></div>
            </div>
          </div>
        </div>
        
        
        
        <!-- Pages, because we need fixed-through navbar and toolbar, it has additional appropriate classes-->
        <div class="pages navbar-through toolbar-through">
          
	      
	        