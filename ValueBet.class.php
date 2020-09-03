<?
	
	class ValueBet extends Model{
		
		
		### Note that there are multiple 
		protected	$belongsTo = array(
					'BetfairMarket'=>array(
						'required'=>false,
						'homeKey'=>'betfairMarketId',
						'foreignKey'=>'betfairId'
					),
					'BetfairEvent'=>array(
						'required'=>false,
						'homeKey'=>'betfairEventId',
						'foreignKey'=>'betfairId'
					),
					'BetfairRunner'=>array(
						'required'=>false
					),
					'BetType'=>array(
						'required'=>false
					)
		);
		
		//Function returns unique value bets on a selction
		function getValueBets(DateTime $lookBack, $minimumGrowth = 0, $bookie = "", $minimumEdge = 0, $minimumLiquid = 0, $minimumPrice=0, $maxPrice=999){
			
			$conditions = array(
					'time>'=>$lookBack->format('Y-m-d H:i:s'),
					'growth>'=>$minimumGrowth,
					'edge>'=>$minimumEdge,
					'liquid>'=>$minimumLiquid,
					'bestBookmakerPrice>'=>$minimumPrice,
					'bestBookmakerPrice<'=>$maxPrice,
				);
				
			if($bookie != ""){
				
				$conditions[] = array('LIKE'=>array('bestBookmakerList'=>'%'.$bookie.'%'));
			}
			
			$ValueBets = $this->find(array(
				'conditions'=>$conditions,
				'orderBy'=>'growth Desc'
			));
			
			$BetfairRunnerIds = array();
			$UniqueValueBets = array();
			
			foreach($ValueBets as $index => $ValueBet){
				
				$valueBetLookUp = array_search($ValueBet->BetfairRunnerId, $BetfairRunnerIds);
				if($valueBetLookUp === false){
					
					$BetfairRunnerIds[] = $ValueBet->BetfairRunnerId;
					$UniqueValueBets[] = $ValueBet;
				}else{
					
					if( $ValueBet->time > $UniqueValueBets[$valueBetLookUp]->time ){
						
						$UniqueValueBets[$valueBetLookUp] = $ValueBet;
					}
				}
			}
			
			return $UniqueValueBets;
			
		}
		
		//This function deletes all values before a certain time...
		function clear($lookBack){
			
			$sqlQuery = "DELETE FROM ValueBets WHERE time < ?";
			$mysql = $this->mysqli();
			//Prepare the query..
			if( !$mysqlProcedure = $mysql->prepare( $sqlQuery ) )
				throw new Exception( "(" . $this->mysqli->errno . ") " . $this->mysqli->error . $sqlQuery);
			
			$lookBackString = $lookBack->format('Y-m-d H:i:s');
			$mysqlProcedure->bind_param('s',$lookBackString);
						    
		    //Exectue the query..
		    if( !$mysqlProcedure->execute() )
				throw new Exception( "(" . $mysqlProcedure->errno . ") " . $mysqlProcedure->error);
		}
		
		//Uses the price of the bet to determine max stake, includes random number selector
		function getInstoreMaxStake(){
			
			//Hard limits
			if($this->bestBookmakerPrice < 2 ){
				
				$maxStake = 700;
				
			}else if($this->bestBookmakerPrice < 2.5 ){
				
				$maxStake = 550;
				
			}else if($this->bestBookmakerPrice < 3 ){
				
				$maxStake = 440;
				
			}else if($this->bestBookmakerPrice < 4 ){
				
				$maxStake = 360;
				
			}else if($this->bestBookmakerPrice < 5 ){
				
				$maxStake = 310;
				
			}else if($this->bestBookmakerPrice < 6 ){
				
				$maxStake = 260;
				
			}else if($this->bestBookmakerPrice < 7 ){
				
				$maxStake = 210;
				
			}else if($this->bestBookmakerPrice < 8 ){
				
				$maxStake = 160;
				
			}else if($this->bestBookmakerPrice < 9 ){
				
				$maxStake = 130;
				
			}else if($this->bestBookmakerPrice < 10 ){
				
				$maxStake = 110;
				
			}else if($this->bestBookmakerPrice < 14 ){
				
				$maxStake = 90;
				
			}else if($this->bestBookmakerPrice < 15 ){
				
				$maxStake = 70;
				
			}else if($this->bestBookmakerPrice < 22 ){
				
				$maxStake = 60;
				
			}else if($this->bestBookmakerPrice < 28 ){
				
				$maxStake = 40;
				
			}else if($this->bestBookmakerPrice < 52 ){
				
				$maxStake = 30;
				
			}else{
				
				$maxStake = 20;
			}
			
			//If each way, multiply by 1.5x
			if( $this->BetTypeId == 2){
				
				$maxStake = $maxStake*1.6;
			}
			
			return $maxStake;
			
			//Removed random number generator because stake would change every refresh
			//Randomise top 10% of bet and round results to nearest 10
			//$tenPercent = $maxStake*0.1;
			//Return the final value with 10% randomised top value
			//return round(($maxStake+(rand(0,(int)(2*$tenPercent))-$tenPercent))/10)*10;
		}
		
	}