<?
	
	class BetfairMarket extends Model{
		
		protected	$hasMany = array(
					'BetfairRunner'=>array(
						'autoLoad'=>true,
						'homeKey'=>'betfairId',
						'foreignKey'=>'betfairMarketId'
					)
		);
		protected	$belongsTo = array(
					'BetfairEvent'=>array(
						'required'=>false,
						'homeKey'=>'betfairEventId',
						'foreignKey'=>'betfairId'
					)
		);
		
		
		//function updates race results for a given day
		function updateResults(DateTime $date){
			
			
			$BetfairMarkets = $this->find(array('conditions'=>array(
				'startTime>'=>$date->format('Y-m-d 00:00:00'),
				'startTime<'=>$date->format('Y-m-d 23:59:59')
			)));
			
			//Get racing post results for this day..
			$racingPost  = new racingPost;
			$results = $racingPost->getResults($date);
			
			$BetfairVenue = new BetfairVenue;
			
			//Loop through each race
			foreach( $BetfairMarkets as $BetfairMarket){
								
				//Only process races which have already ran and are win types
				if( ((new DateTime($BetfairMarket->startTime))<(new DateTime('now'))) && $BetfairMarket->type=="WIN"){
					
					
					//Need to get the venue name
					if( $BetfairMarket->BetfairEvent->BetfairVenueId != 0){
						
						$BetfairVenue = $BetfairVenue->find(array('conditions'=>array('id'=>$BetfairMarket->BetfairEvent->BetfairVenueId)))[0];
						
						
						$venueName = explode(' ',$BetfairVenue->name)[0];
						$raceTime = substr($BetfairMarket->startTime,11,5);
						
						//Check if there is a result for this race and event name
						if( isset($results[$venueName][$raceTime]['results'])){
							
							
							logness("results found for $raceTime at $venueName");
							
							$resultCount=0;
							$nonRunnerCount=0;
							
							//Loop through each runner in race
							foreach( $BetfairMarket->BetfairRunner as $BetfairRunner){
								
								//Get formatted version of name
								$runnerName = strtoupper(str_replace("'","",$BetfairRunner->name));
								
								
								//Check if horse is non runner
								if( is_array($results[$venueName][$raceTime]['nonRunners'])){
									
									foreach($results[$venueName][$raceTime]['nonRunners'] as $nonRunnerName){
										
										if(strtoupper(str_replace("'","",$nonRunnerName))==$runnerName){
											
											$BetfairRunner->isNonRunner=1;
											$BetfairRunner->save();
											
											$nonRunnerCount++;
										}
									}
								}
								
								//Check if horse has result
								foreach($results[$venueName][$raceTime]['results'] as $position => $horse){
									
									if(strtoupper(str_replace("'","",$horse['name']))==$runnerName){
											
										$BetfairRunner->result=$position;
										$BetfairRunner->save();
										
										$resultCount++;
									}
								}
									
							}
							
							
							//once results populated need to check that all positions  are assigned...
							if(   $resultCount == count($results[$venueName][$raceTime]['results'])){
								
								$BetfairMarket->runners=$results[$venueName][$raceTime]['totalRunners'];
								$BetfairMarket->hasResults=1;
								$BetfairMarket->save();
							}else{
								
								logness("nonrunner count: $nonRunnerCount, result count: $resultCount");
							}
							
						}
					}
					
				}
			}
			//logness($results);
			//customExit();
				
		}
	}