<?
	
	class racingPost extends scraper{
		
		
		//Returns an array of meetings, containing array of races, containing: 
		//'results'(array of horses(array with 'name', 'startingPrice') in order of finish), 'nonRunners'(array of non runners), 'totalRunners'(int number of runners in race)
		function getResults(DateTime $date){
			
			
			$dateString = $date->format('Y-m-d');
			$url = 'http://betting.racingpost.com/horses/results/?r_date='.$dateString;
			
			$html = $this->getHtml($url);
			
			//Line needs to be accross two lines to recognise new line character
 			$events = explode("<div class=\"headlineBlock\">
 <h2>",$html);
 
 			//logness($events);
 			//customExit();
 			$resultsArray = array();
 			
 			$maxI = count($events);
			
			for( $i=1; $i< $maxI;$i++){
				
				$eventName =  explode('</h2>',$events[$i])[0];
				
				//Remove bracketed text if any
				$eventName =  rtrim(explode('(',$eventName)[0]," ");
				
				
				//Get each race...
				$races = explode('<strong id=', $events[$i]);
								
				$resultsArray[$eventName]=array();
				
				$maxQ = count($races);
				
				for( $q=1; $q<$maxQ;$q++){
					
					$raceTime = explode('<',explode('>', $races[$q])[1])[0];
					
					//Convert the hour part of the time to 24 hour
					$raceTime = explode(':',$raceTime);
					$raceHour = $raceTime[0];    
					$raceMinute = $raceTime[1];    
					
					if( $raceHour < 11){
						$raceHour+=12;
					}
					
					$raceTime = $raceHour.':'.$raceMinute;
					
					
					
					$resultsArray[$eventName][$raceTime]=array();
					
					//Find out number of runners..
					$totalRunners = explode('>',explode('ran Distances', $races[$q])[0]);
					$totalRunners = (int)$totalRunners[count($totalRunners)-1];
					
					$resultsArray[$eventName][$raceTime]['totalRunners']=$totalRunners;
					
					//Only proceed if number of runners found..
					if( $totalRunners> 0 ){
					
						//Get horses in order
						$horses = explode('HORSE">', $races[$q]);
						
						
						$maxH = count($horses);
						
						for( $h=1; $h<$maxH; $h++ ){
							
							//get the position os this horse, (should match $h)
							$position = explode(' <a href', $horses[$h-1]);
							$position = $position[count($position)-2];
							$position = explode('>',$position);
							$position = $position[count($position)-1];
							
							//make sure we havent gone down as far as non runner horses, or script
							if (strpos($position, 'NR') !== false || !is_numeric($position)) {
								
								break;	
							}
							
							//If it doesn't match, probably a deadheat
							if( $position != $h ){
								
								//unset the array and break from for loop
								//logness("position: $position not match: $h");
								
								unset($resultsArray[$eventName][$raceTime]['results']);
								break;
							}else{
								
								$horseName = explode('</a>',$horses[$h] )[0];
								$horsePrice = trim(explode('<',explode('</a>', $horses[$h])[1])[0],' F');
								
								if($horsePrice=="evens"){
									
									$horserice = "1/1";
								}
								
								//Convert price to decimal
								$horsePrice = explode('/', $horsePrice);
								$horsePrice = $horsePrice[0]/$horsePrice[1]+1;
								
								$resultsArray[$eventName][$raceTime]['results'][$h] = array('name'=>$horseName,'price'=>$horsePrice );
							}
						}
						
						
						//Get non runners
						$nonRunners = explode('NR:', $races[$q]);
						
						//Check if there is any
						if( count($nonRunners)>1){
							
							$resultsArray[$eventName][$raceTime]['nonRunners']=array();
							
							$nonRunners = explode('</p>', $nonRunners[1])[0];
							$nonRunners = explode('</a>', $nonRunners);
							
							$maxN = (sizeof($nonRunners)-1);
							
							for( $n=0; $n<$maxN;$n++){
								
								$nonRunnerName = explode('>', $nonRunners[$n])[1];
								$nonRunnerName = explode(' (', $nonRunnerName)[0];
								
								$resultsArray[$eventName][$raceTime]['nonRunners'][]=explode('>', $nonRunners[$n])[1];
							}
						}
					}
				}
				
			}
			
			return $resultsArray;
			//logness($resultsArray);
			//customExit();
		}
		
	}