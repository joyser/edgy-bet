function refreshTimer(start, end, display) {
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
            location.reload();
            return;
        }
    }, 1000);
}