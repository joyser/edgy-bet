<?
	
	class BetfairEvent extends Model{
		
		protected	$hasMany = array(
					'BetfairMarket'=>array(
						'autoLoad'=>true,
						'homeKey'=>'betfairId',
						'foreignKey'=>'betfairEventId'
					)
		);
		
		
		
	}