<?
	// Beeper is a mobile app for sending notifcations to runners
	class beeper{


		private $applicationId = "";
		private $restAPIKey = "";
		private $senderId = "";

		function beep($mobile, $text){

			// create curl resource
	        $ch = curl_init();

	        $data = array(
	        	'sender_id'=>$this->senderId,
	        	'phone'=>$mobile,
	        	'text'=>$text
	        );

			$data_json = json_encode($data);


	        // set url
	        curl_setopt($ch, CURLOPT_URL, "https://api.beeper.io/api/messages");

			$headers = array(
				'Content-Type: application/json',
				'X-Beeper-Application-Id: '.$this->applicationId,
				'X-Beeper-REST-API-Key: '.$this->restAPIKey,
			);

	        //return the transfer as a string
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			    curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);

	        // $output contains the output string
	        $output = curl_exec($ch);

	        // close curl resource to free up system resources
	        curl_close($ch);
		}
	}
