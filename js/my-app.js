// Initialize your app
var myApp = new Framework7();

// Export selectors engine
var $$ = Dom7;

// Add view
var mainView = myApp.addView('.view-main', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true,
    cache: false
});

myApp.onPageInit('valueBets', function (page) {
	display = $('#valueRefreshTimer');
	refreshTimerMobile(0, 15, display);
	
	myApp.pullToRefreshDone();
	
	// Pull to refresh content
	var ptrContent = $$('.pull-to-refresh-content');
	 
	// Add 'refresh' listener on it
	ptrContent.on('refresh', function (e) {

	    mainView.router.refreshPage();
	    
	});

});




function refreshTimerMobile(start, end, display) {
    var timer = start, minutes, seconds;
    var interval = setInterval(function () {
        minutes = parseInt(timer / 60, 10)
        seconds = parseInt(timer % 60, 10);
		
		if( timer > 60*60 ){
			
			timer=0;
		}
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        display.text("-" + minutes + ":" + seconds);
		
        if (++timer == end && end) {
            mainView.router.refreshPage();
            return;
        }
    }, 1000);
}