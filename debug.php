<?

	$log = array();
	$lognessStartTime= microtime(true);

	function logness($text, $color='black'){

		//Retrieve global log
		global $log;

		$log[] = array('text'=>$text, 'color'=>$color);

	}

	//This function is a custom exit function
	function customExit($email=0){

		//Retrieve the global log..
		global $log;
		global $lognessStartTime;

		$string = '';

		foreach( $log as $item ){

			if( is_string($item['text']) ){
				$string.= "<font color=\"".$item['color']."\">".$item['text']."</font><br>";
			}else{

				$string.= "<font color=\"".$item['color']."\"><pre>";

				$string.= print_r($item['text'],true);
				$string.= "</pre></font><br>";
			}
		}

		$string.= "<font color=\"orange\">Exiting, execution time: ".round(microtime(true)-$lognessStartTime,3)."</font><br>";

		echo $string;

		if($email){

			$to  = ''; // note the comma

			// subject
			$subject = 'Auto Trade ouput';

			// message
			$message = '
			<html>
			<head>
			  <title>Auto Trade ouput</title>
			</head>
			<body>
			  '.$string.'
			</body>
			</html>
			';

			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			// Additional headers
			$headers .= 'To: admin <>' . "\r\n";
			$headers .= 'From: Autotrader <autotrade@laptop>' . "\r\n";

			// Mail it
			//mail($to, $subject, $message, $headers);

		}

		exit;
	}
