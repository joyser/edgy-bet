<?
	
	class Bet extends Model{
		
		
		protected	$belongsTo = array(
					'User'=>array(
						'required'=>true
					),
					//'Bookmaker'=>array(
					//	'required'=>true
					//),
					'BetfairRunner'=>array(
						'required'=>true,
						'homeKey'=>'BetfairRunnerId',
						'foreignKey'=>'id'
					),
					'BetfairMarket'=>array(
						'required'=>true,
						'homeKey'=>'betfairMarketId',
						'foreignKey'=>'betfairId'
					),
					'BetType'=>array(
						'required'=>true
					)
		);
		
		
		function save(){
			
			//Manually set the log time incase mysql in different timezone
			if( !isset($id) )
				$this->logTime = date('Y-m-d H:i:s');
			
			//Settle the bet if status is different
			if( $this->BetStatusId!=0 && isset($this->BetStatusId))
				$this->isSettled=1;
			
			
			parent::save();
		}
		
		
		function getReturn(){
			
			if($this->BetfairMarket->hasResults){
				
				if( $this->BetfairRunner->isNonRunner==1){
					
					return $this->stake;
				}
				
				if( $this->BetTypeId == 1){
					
					
					if( $this->BetfairRunner->result == 1 ){
						
						return $this->stake*($this->price);
						
					}else{
						
						return 0;
					}
					
				}else if( $this->BetTypeId == 2){
					
					
					$return = 0;
					
					if( $this->BetfairRunner->result == 1 ){
						
						$return += 0.5*$this->stake*($this->price);
						
					}
					
					//Get pace terms
					$BetfairHelper = new BetfairHelper;
					
					$eachWayTerms = $BetfairHelper->eachWayTerms($this->BetfairMarket->runners, $this->BetfairMarket->isHandicap);
					
					if( $this->BetfairRunner->result!=0 && $this->BetfairRunner->result <= $eachWayTerms['places'] ){
						
						$return+= 0.5*$this->stake*((($this->price-1)*$eachWayTerms['multiplier'])+1);
					}
					
					return $return;
				}				
				
			}else{
				
				return 0;
			}
		}
	}