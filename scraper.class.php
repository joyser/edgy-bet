<?
	
	class scraper{
		
		
		function getHtml( $url ){
			
			// create curl resource
		    $ch = curl_init();
		
		    // set url
		    curl_setopt($ch, CURLOPT_URL, $url);
		
		    //return the transfer as a string
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		
		    // $output contains the output string
		    $html = curl_exec($ch);
			
		    // close curl resource to free up system resources
		    curl_close($ch);
		    
		    return $html;
		}
		
		
		
		function multiRequest($data, $options = array()) {
 
		  // array of curl handles
		  $curly = array();
		  // data to be returned
		  $result = array();
		 
		  // multi handle
		  $mh = curl_multi_init();
		 
		  // loop through $data and create curl handles
		  // then add them to the multi-handle
		  foreach ($data as $id => $d) {
		 
		    $curly[$id] = curl_init();
		 
		    $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
		    curl_setopt($curly[$id], CURLOPT_URL,            $url);
		    curl_setopt($curly[$id], CURLOPT_HEADER,         0);
		    curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
			//return the transfer as a string
		    curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($curly[$id], CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
			
		    // post?
		    if (is_array($d)) {
		      if (!empty($d['post'])) {
		        curl_setopt($curly[$id], CURLOPT_POST,       1);
		        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
		      }
		    }
		 
		    // extra options?
		    if (!empty($options)) {
		      curl_setopt_array($curly[$id], $options);
		    }
		 
		    curl_multi_add_handle($mh, $curly[$id]);
		  }
		 
		  // execute the handles
		  $running = null;
		  do {
		    curl_multi_exec($mh, $running);
		  } while($running > 0);
		 
		 
		  // get content and remove handles
		  foreach($curly as $id => $c) {
		    $result[$id] = curl_multi_getcontent($c);
		    curl_multi_remove_handle($mh, $c);
		  }
		 
		  // all done
		  curl_multi_close($mh);
		 
		  return $result;
		}
	}