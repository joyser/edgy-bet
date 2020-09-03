<?
	
	class RunnerSnapShot extends Model{
		
		
		//This function deletes all values before a certain time...
		function clear($lookBack){
			
			$sqlQuery = "DELETE FROM RunnerSnapShots WHERE time < ?";
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
		
	}