<?
	
	class SystemVariable extends Model{
		
		
		
		//Returns the value of a system variable
		function get($name){
			
			$SystemVariable = $this->find(array(
				'conditions'=>array(
					'name'=>$name
				)
			))[0];
			
						
			return $SystemVariable;
			
		}
		
		function set($name, $value){
			
			$SystemVariable = $this->find(array(
				'conditions'=>array(
					'name'=>$name
				)
			))[0];
			
			$SystemVariable->value = $value;
			$SystemVariable->save();
			
		}
		
	}