<?
	
	class BetfairRunner extends Model{
		
		//Returns two values, the first is the total matched from now back as far as lookback
		//The second is the total matched
		function getTradedVolume( DateTime $lookBack ){
			
			$RunnerSnapShot = new RunnerSnapShot;
			
			$RunnerSnapShot = $RunnerSnapShot->find(array('conditions'=>array(
				'time>'=>$lookBack->format('Y-m-d H:i:s'),
				'BetfairRunnerId'=>$this->id
			)));
			
			$tradedSinceLookBack = abs(($RunnerSnapShot[0]->totalMatched - $RunnerSnapShot[sizeof($RunnerSnapShot)-1]->totalMatched))/2;
			
			$totalMatched = max($RunnerSnapShot[0]->totalMatched, $RunnerSnapShot[sizeof($RunnerSnapShot)-1]->totalMatched);
			
			//Return the difference in matched volume from first and last snap
			//have to divide by two because betfair includes backer and layers stake
			return array($tradedSinceLookBack,$totalMatched);
		} 
	}