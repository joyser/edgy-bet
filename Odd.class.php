<?
	
	class Odd extends Model{
		
		
		public function getFractional($decimal){
			
			
			$Odds = $this->find(array(
				'conditions'=>array(
					
				),
				'orderBy'=>'abs(Odd.decimalValue - '.$decimal.') ASC'
			));			
			
			return $Odds[0]->fractionalValue;
		}
		
	}