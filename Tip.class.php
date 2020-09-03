<?
	
	class Tip extends Model{
		
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
					'Tipster'=>array(
						'required'=>true
					),
					'BetfairRunner'=>array(
						'required'=>true
					)
		);
		
		
		//This fucntion takes a value bet, and makes Tips where it can...
		public function ProcessValue( ValueBet $ValueBet ){
			
			
			//Load all tipsters and save them in the object, as this will make repeated calls easier, and there are not a lot of 
			if(!isset($this->Tipsters)){
				
				$Tipster = new Tipster;
			
				$this->Tipsters = $Tipster->find();
			}
			
			$Tip = new Tip;
			
			foreach( $this->Tipsters as $Tipster ){
				
				//Check that growth, liquid and bookmaker are present...				
				if( $ValueBet->growth > $Tipster->minimumGrowth && $ValueBet->liquid > $Tipster->minimumLiquid && ( strlen($Tipster->bookmaker)<2 || strpos($ValueBet->bestBookmakerList, $Tipster->bookmaker)!==false ) ){
					
					
					$minimumRepeatKelly = $ValueBet->kellyPercent+0.5;
										
					
					//Check if a tip has been issued (if the price is lower and kelly is 0.5% greater, then we will re-issue)
					$Tips = $Tip->find(array('conditions'=>array(
						'BetfairRunnerId'=>$ValueBet->BetfairRunnerId,
						'TipsterId='=>$Tipster->id,
						'OR'=>array(
							array('kellyPercent<'=>$minimumRepeatKelly),
							array('bestBookmakerPrice>'=>$ValueBet->bestBookmakerPrice)
						)						
					)));
					

					if(count($Tips) == 0 ){
						
						$NewTip = clone $Tip;
						
						//Issue new tip...
						$NewTip->TipsterId = $Tipster->id;
						$NewTip->BetfairRunnerId = $ValueBet->BetfairRunnerId;
						$NewTip->betfairMarketId = $ValueBet->betfairMarketId;
						$NewTip->betfairEventId = $ValueBet->betfairEventId;
						$NewTip->bestBookmakerPrice = $ValueBet->bestBookmakerPrice;
						$NewTip->bestBookmakerList = $ValueBet->bestBookmakerList;
						$NewTip->edge = $ValueBet->edge;
						$NewTip->kellyPercent = $ValueBet->kellyPercent;
						$NewTip->lastUpdate = $ValueBet->lastUpdate;
						$NewTip->edgeType = $ValueBet->edgeType;
						$NewTip->liquid = $ValueBet->liquid;
						$NewTip->time = date('Y-m-d H:i:s');

						$NewTip->save();
						
					}	
				}
			}
		}
		
		
	}