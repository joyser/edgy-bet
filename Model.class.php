<?php
	

abstract class Model{
	
	//These will hold arrays of relationships of a given model
	protected $hasMany;
	protected $belongsTo;
	protected $hasAndbelongsToMany;
	protected $fields;
	
	private $DBUsername;
	private $DBPassword;
	private $DBHost;
	private $DBDatabase;
	
	private $mysqli;
	
	//Auto-load function
	
	//If the user supplies an id when initiating the object, then the object with that id is loaded
	public function __construct($arg = null){
				
		global $DBUsername;
		global $DBPassword;
		global $DBHost;
		global $DBDatabase;
		global $DBMYSQLI;
		
		
		$this->DBUsername = $DBUsername;
		$this->DBPassword = $DBPassword;
		$this->DBHost = $DBHost;
		$this->DBDatabase = $DBDatabase;
		
		//Find the name of the object..
		$this->objectName = get_class($this);
		
		//add an s to the table unless there is a custom name supplied
		if( !isset( $this->tableName ) )
			$this->tableName = $this->objectName."s";
		
		//If there is an ID supplied with the constructor then load the item with the ID
		## TODO: Implement; setup this model or return new one? ##
		if( $id != null )
			$this->find( $id );
		
		
		//Update the fields of this model
		$this->updateFields();
	}
	
	//Find a given object
	//The $arguments here can be:
	//1. Integer - Id of a single item to be loaded into model
	//2. String, string argument of what to load ('all', 'first')
	//3. Array, array of arguments which are applied as filters, with following attributes:
		//conditions - array of filter conditions
		
		 
		
	public function find( $arguments = null ){
		
		//Ensure connection is established to the database..
		$this->mysqliConnect();
		
		//Build the fields string for the sql command..
		$fields = array();
		$fieldString = "";
		
		//Add the fields of the current model to the $fields array
		foreach( $this->getFields() as $fieldName ){
			
			$fields[$this->objectName.".".$fieldName] = $this->objectName.".".$fieldName;
		}
		
		
		
		//This string will keep the join string which is appended to the sql..
		$leftJoinSQL = "";
		
		//This array will hold blank copies of all the objects this model belongsTo
		$belongsTo = array();
		
		//Add the fields of the belongsTo..
		//See whats specified in the parent model
		if( is_array($this->belongsTo) ){
			foreach( $this->belongsTo as $parentModel => $values ){
				
				//Check if the model speficies whether the parent should be laoded.. (ie. not set or true) 
				if( !isset( $values ['autoLoad'] ) || $values['autoLoad'] ){
					
					//Initiate the model to get the fields
					$parentObject = new $parentModel;
					
					//Check if there is define values for the belongs to join fields...
					if( !isset( $values['foreignKey'] ) )
						$values['foreignKey'] = "id";
						
					if( !isset( $values['homeKey'] ) )
						$values['homeKey'] = $parentModel.ucfirst($values['foreignKey']);
					
					//Add the table to the join string..
					$leftJoinSQL.=" LEFT JOIN ".$parentObject->tableName." as ".$parentObject->objectName." on ".$parentObject->objectName.".".$values['foreignKey']." = ".$this->objectName.".".$values['homeKey']." ";
					
					foreach( $parentObject->getFields() as $fieldName ){
						
						$fields[$parentModel.".".$fieldName] = $parentModel.".".$fieldName;
					}
					
					//Add the object to the belongsTo array for use when returning values from SQL..
					$belongsTo[$parentModel] = $parentObject;
				}
			}
		}
		
		//This string will keep the join string which is appended to the sql..
		$fullJoinSQL = "";
		
		//This array will hold blank copies of this models children
		$hasMany = array();
		
		//Add the fields of the hasMany..
		//See whats specified in the parent model
		if( is_array($this->hasMany) ){
			foreach( $this->hasMany as $childModel => $values ){
				
				//Check if the model speficies whether the parent should be loaded.. (ie. not set or true) 
				##To-do the load value should be able to overrive the specified 'autoLoad'
				if( $values['autoLoad'] ){
					
					//Initiate the model to get the fields
					$childObject = new $childModel;
					
					//Check if pair keys are defined
					if( !isset( $values['homeKey'] ) )
						$values['homeKey'] = 'id';
						
					if( !isset( $values['foreignKey'] ) )
						$values['foreignKey'] = $this->objectName.ucfirst($values['homeKey']);
						
					
					
					//Add the table to the join string..
					$leftJoinSQL.=" LEFT JOIN ".$childObject->tableName." as ".$childObject->objectName." on ".$childObject->objectName.".".$values['foreignKey']." = ".$this->objectName.".".$values['homeKey']." ";
					
					foreach( $childObject->getFields() as $fieldName ){
						
						$fields[$childModel.".".$fieldName] = $childModel.".".$fieldName;
					}
					
					//Add the object to the hasMany array for use when returning values from SQL..
					$hasMany[$childModel] = $childObject;
				}
			}
		}
		
		//Add all the fields to the field string..
		foreach( $fields as $field=>$as ){
			
			$fieldString.= " ".$field." as '".$as."',";	
		}
		
		
		//Remove trailing commas
		$fieldString = rtrim($fieldString, ",");
		
		//Build query
		$sqlQuery = " SELECT ".$fieldString." FROM ".$this->tableName." as ".$this->objectName ;
		
		//Add the left join to the query..
		$sqlQuery .= $leftJoinSQL;
		$sqlQuery .= $fullJoinSQL;
		
		//Add the conditions to the SQL
		$sqlConditions = $this->processConditions( $arguments['conditions'] );
		
		//print_r($sqlConditions);
		
		if( strlen( $sqlConditions['conditionString'] ) > 0 )
			$sqlQuery .= " WHERE ".$sqlConditions['conditionString'];
		
		//check for orderby
		if( isset($arguments['orderBy']) ){
			
			//Check if there is a table specified in orderBy, and if not add the current model..
			if( strpos( $arguments['orderBy'], '.' ) === false )
				$arguments['orderBy'] = $this->objectName.".".$arguments['orderBy'];
				
			$sqlQuery.=" ORDER BY ".$arguments['orderBy'];
		}
		
		
		//Check for limit..
		if( isset($arguments['limit']) ){
			
			if(isset($arguments['limit']['start']) && isset($arguments['limit']['length'])){
				
				$sqlQuery.=" LIMIT ".$arguments['limit']['start'].", ".$arguments['limit']['length'];
			}else{
				
				$sqlQuery.=" LIMIT ".$arguments['limit'];
			}
		}
		
		
		//Print the sql needed..
		if( $arguments['debug'])
			echo $sqlQuery;
		
		
		//Prepare the query..
		if( !$mysqlProcedure = $this->mysqli->prepare( $sqlQuery ) )
			throw new Exception( "(" . $this->mysqli->errno . ") " . $this->mysqli->error . $sqlQuery);
			
		//Bind the condition values to the SQL query (if there is any)
		if( !empty($sqlConditions['parameters'][0]))
			call_user_func_array(array(&$mysqlProcedure,'bind_param'), $this->refValues($sqlConditions['parameters']));
		
		
		//Get the meta data for query (for field names)
		$meta = $mysqlProcedure->result_metadata();
		
	    // Dynamically create the array of fields returned
	    while ($field = $meta->fetch_field()) { 
	        $var = $field->name; 
	        $$var = null; 
	        $returnedFields[$var] = &$$var;
	    }
	    
		//print_r($returnedFields);
		//print_r($fields);
	    // Bind Results
	    call_user_func_array(array($mysqlProcedure,'bind_result'),$returnedFields);
		    
	    //Exectue the query..
	    if( !$mysqlProcedure->execute() )
			throw new Exception( "(" . $mysqlProcedure->errno . ") " . $mysqlProcedure->error);
			
		
		//this array will hold the objects which are returned
		$objects = array();
		
		//This array will hold the Ids of the obejcts for referencing..
		$objectIds = array();
		
		//This will be the base object which will be cloned for loading properties
		$baseObject = clone $this;
		
		foreach( $hasMany as $childModel => $childObject ){
				
			$baseObject->$childModel = array();
		}
		
		$result = $mysqlProcedure->get_result();
		
		//Loop through each result
		while( $row = $result->fetch_array() ) {
			
			//print_r($row);
			
			//Check if this object has been seen in the previous rowS..
			if( !isset($objects[$row[$this->objectName.'.id']])  ){
			
				$object = clone $baseObject;
				
				//Check if there is hasMany objects, and add blank clones
				foreach( $hasMany as $childModel => $childObject ){
					
					$object->{$childModel}[] = clone $childObject;
				}
				
				//Set up the belongsTo objects if any exist...
				foreach( $belongsTo as $parentModel => $parentObject ){
					
					$object->$parentModel = clone $parentObject;
				}
				
				
				//Get every value returned from the databae and add it to the object
				foreach( $row as $key => $value ){
					
					//Find what object and field it is..
					$returnField = explode(".",$key);
					
					if( $returnField[0] == $this->objectName ){
						
						$object->$returnField[1] = $value;
					
						//Check what the relationship os between the return table and current obejct..
					}else if( isset( $belongsTo[$returnField[0]] ) ){
						
						$object->{$returnField[0]}->$returnField[1] = $value;
						
					}else if( isset( $hasMany[$returnField[0]] ) ){
						
						$object->{$returnField[0]}[count($object->{$returnField[0]})-1]->$returnField[1] = $value;
						
					}
				}
				
				//Add the item the index set as the id..
				$objects[$row[$this->objectName.'.id']] = $object;
			}else{
				
				//Sometimes the main objects can be mixed up, and won't be one after the order..
				//..so we need to point back to the object
				$object = $objects[$row[$this->objectName.'.id']];
				
				//Add the hasMany objects
				//Get every value returned from the databae and add it to the object
				//Check if there is hasMany objects, and add blank clones
				foreach( $hasMany as $childModel => $childObject ){
					
					$object->{$childModel}[] = clone $childObject;
				}
					
				foreach( $row as $key => $value ){
					
					//Find what object and field it is..
					$returnField = explode(".",$key);
					
					if( isset( $hasMany[$returnField[0]] ) ){
						
						//Found duplicate row, with hasMany
						$object->{$returnField[0]}[count($object->{$returnField[0]})-1]->$returnField[1] = $value;
						
					}
				}
			}
		}
		
		//The index of objects are the Ids of individual items..
		//want to reset these to numeric arrays..
		$objects = array_values($objects);
		
		//print_r($objects);
		//Return array of initiated objects
		return $objects;
	}
	
	//Save the given object
	public function save(){
		
		//Ensure connection is established to the database..
		$this->mysqliConnect();
		
		//Check if the current object is new or not
		if( isset($this->id) ){
			
			//Update fields of current model only...
			#TODO - possibly can update the belongsTo and hasMany objects
			
			$sqlQuery = "UPDATE ".$this->tableName." SET ";
			$sqlValues = array('');
			
			foreach( $this->getFields() as $field){
				
				//If this obejct has newly been added, all fields may not be set, so only do it for set fields..
				if( isset($this->$field )){
					//Add field to query string
					$sqlQuery .= "$field = ?,";
					
					//get the actual value form field
					$value = $this->$field;
					
					//check the data type of the field:
					if( isset($this->fields[$field]['queryType']) ){
								
						$type = $this->fields[$field]['queryType'];
					}else{
						
						$type = $this->dataType($value);
					}
					
					//Add the value and its datatype to the sqlvalues array
					
					$sqlValues[0] .= $type;
					$sqlValues[] = $value;
				}
				
			}
			
			//Remove trailing commas
			$sqlQuery = rtrim($sqlQuery, ",");
			
			$sqlQuery.= " WHERE id=".$this->id;
			
			//Prepare the query..
			if( !$mysqlProcedure = $this->mysqli->prepare( $sqlQuery ) )
				throw new Exception( "(" . $this->mysqli->errno . ") " . $this->mysqli->error . $sqlQuery);

			//Bind the updated values to the SQL query (if there is any)
			if( !empty($sqlValues[0]))
				call_user_func_array(array(&$mysqlProcedure,'bind_param'), $this->refValues($sqlValues));
			
			 //Exectue the query..
			 if( !$mysqlProcedure->execute() )
			 	throw new Exception( "(" . $mysqlProcedure->errno . ") " . $mysqlProcedure->error);
			
			
			
		}else{
			
			//Save as new model...			
			$sqlQuery = "INSERT INTO ".$this->tableName." ( ";
			$valueString="";
			$sqlValues = array('');
			
			foreach( $this->getFields() as $field){
				
				//Dont try and add an id
				if( $field != "id" ){
					
					//Only add if there is a value to add:
					if( isset($this->$field )){
						//Add field to query string
						$sqlQuery .= "$field,";
						$valueString.="?,";
						
						//get the actual value form field
						$value = $this->$field;
				
						//check the data type of the field:
						if( isset($this->fields[$field]['queryType']) ){
									
							$type = $this->fields[$field]['queryType'];
						}else{
							
							$type = $this->dataType($value);
						}
				
						//Add the value and its datatype to the sqlvalues array
						$sqlValues[0] .= $type;
						$sqlValues[] = $value;
					}
				}
				
			}
			
			//Remove trailing commas
			$sqlQuery = rtrim($sqlQuery, ",");
			$valueString = rtrim($valueString, ",");
			
			$sqlQuery.= " ) VALUES ($valueString)";
				
			//Prepare the query..
			if( !$mysqlProcedure = $this->mysqli->prepare( $sqlQuery ) )
				throw new Exception( "(" . $this->mysqli->errno . ") " . $this->mysqli->error . $sqlQuery);
				
			//Bind the updated values to the SQL query (if there is any)
			if( !empty($sqlValues[0]))
				call_user_func_array(array(&$mysqlProcedure,'bind_param'), $this->refValues($sqlValues));
			
			
			 //Exectue the query..
		    if( !$mysqlProcedure->execute() )
				throw new Exception( "(" . $mysqlProcedure->errno . ") " . $mysqlProcedure->error);
			$this->id = $this->mysqli->insert_id;
		}
	}
	
	//Delete a given object
	public function delete(){
		
		
	}
	
	private function mysqliConnect(){
		
		if( !isset( $this->mysqli ) ){
			
			global $DBMYSQLI;
			if( !isset($DBMYSQLI) ){
				$DBMYSQLI=$this->mysqli();
			}
		
			$this->mysqli = &$DBMYSQLI;
			
		}	
	}
	
	private function mysqliDisconnect(){
		
		if( isset( $this->mysqli ) ){
			
			//$this->mysqli->close();
			//unset( $this->mysqli );
		}	
	}
	
	public function mysqli(){
		
		return new mysqli($this->DBHost, $this->DBUsername, $this->DBPassword, $this->DBDatabase);
	}
	
	
	//Update the fields of a model based on the database schema
	private function updateFields(){
		
		//check there is no values already in the fields array
		if( !isset( $this->fields ) ){
		
			//Connect to the database
			$this->mysqliConnect();
			
			$sqlQuery = "SHOW COLUMNS FROM ".$this->tableName."";
			
			
			if( !$result = $this->mysqli->query( $sqlQuery ) ){
				
				throw new Exception( "(" . $this->mysqli->errno . ") " . $this->mysqli->error);
			}
			
			//	
			
			$this->fields = array();
			
			while( $row = $result->fetch_assoc() ) {
				
				//Check what type of data this is for prepared statements..
				if( substr($row['Type'],0,3) == "int" || substr($row['Type'],0,3) == "tinyint"){
					
					$queryType = 'i';
					
				}else if( substr($row['Type'],0,5) == "float" || substr($row['Type'],0,6) == "double"){
					
					$queryType = 'd';
					
				}else {
					
					$queryType = 's';
				}
				
				$this->fields[$row['Field']] = array('type'=>$row['Type'],'queryType'=>$queryType);
			}
			
			//Disconnect from database
			$this->mysqliDisconnect();
		}
	}
	
	
	//Returns an array of fieldname for this model...
	public function getFields(){
		
		$this->updateFields();
		$fields = array();
		
		//Loop through each field..
		foreach( $this->fields as $key => $value ){
			
			//Check if name of the field is the index, or the value..
			if( is_numeric( $key ) ){
				
				$fieldName = $value;	
				
			}else{
				
				$fieldName = $key;	
			}
			
			$fields[] = $fieldName;
		}
			
		return $fields;
	}
	
	//This fucntion checks the relationship of a given object or table name against the current modell
	public function getRelationship(){
		
		
	}
	
	//takes an array of conditions and returns an SQL conditions string.
	private function processConditions( $conditions ){
		
		
		//Process conditions..
		//The conditions array is an array containing any of these:
		//1. A field name as the index, and a value it should equal as value
		//Example: 'Book.id' => 10
		//2. A field name with an operator, and a value..
		//Example: 'Book.id >' => 20
		//3. A numeric index, meaning that there is a sub array in value to be processed further...
			//The subarray will have one element, the index of each will be the operator (ie. AND or OR)
			//The sub elements of this array will be processed as conditions in turn...
		
		//This string will hold the conditions..
		$conditionString = "";
		
		//This array's first element holds the string of types of the parameters which will be passed to the prepared statement
		//The values after this will be the values corresponding to each letter in the types string..
		//This way we can pass this array directly into the call_user_function when needed.
		$parameters = array();
		$parameters[0]='';
		
		if( is_array($conditions)){
			
			foreach( $conditions as $index => $value ){
				
				if( strtoupper($index) == "OR" ){
					
					//If there is an or, it will contain an array or arrays, with the first element being one of the conditions
					if( is_array( $value ) ){
						
						$orCondition = " ( ";
						
						foreach( $value as $subConditions ){

							if( is_array( $subConditions ) ){
								
								$subProcessConditions = $this->processConditions( $subConditions );

								$orCondition .= $subProcessConditions['conditionString']. " OR ";
								$parameters[0] .= $subProcessConditions['parameters'][0];
								
								for( $i=1; $i<sizeof($subProcessConditions['parameters']);$i++){
									
									$parameters[] = $subProcessConditions['parameters'][$i];
								}
							}
							
						}
						//Remove ORs and append the finish
						$orCondition = rtrim($orCondition, " OR ");
						$orCondition .= " ) AND ";
					
					}
					
					$conditionString.= $orCondition;
					
				
				//Check if the index is a LIKE...
				}else if( strtoupper($index) == "LIKE" ){
					
					//If there is a LIKE, it should be an array with one element, key = field, and invalue is the value.. 
					if( is_array( $value ) ){
						
						foreach($value as $field=>$likeValue){
							
							$fieldName = $field;
							if( strpos( $fieldName, '.' ) === false )
								$fieldName = $this->objectName.".".$fieldName;
							
						}
						
						//If its like, it should definately be a string
						$parameters[0].='s';
						
						//Add the condition
						$conditionString.= "$fieldName LIKE (?) AND ";
						$parameters[] = $likeValue;
					}
					
					
					
				
				//Check if the index is a string (not numeric)...
				}else if( !is_numeric( $index ) ){
					
					//If there is no operators in the index string then add the equals operator..
					if( strpos( $index, '<' ) === false	&& strpos( $index, '>' ) === false && strpos( $index, '=' ) === false	&& strpos( $index, '!' ) === false && strpos($index, 'LIKE') === FALSE){
						$index.="=";
					}
					
					//Check if there is a table specified in index, and if not add the current model..
					if( strpos( $index, '.' ) === false )
						$index = $this->objectName.".".$index;
					
					//In some cases the value may be a reference to another field, so we just manually add it..
					if( is_array($value) && isset($value['field'])){
						
						$conditionString .= " $index ".$value['field']." AND ";
					}else{
						
												
						//We need to find the data type of the field $index
						$fieldParts = explode('.', $index);
						$fieldParts[1] = rtrim($fieldParts[1], '=<>!');
						
						//Pressume the object is self:
						$TestObject = $this;
						
						//Check if the field belongs to different object:
						if( $fieldParts[0] != $this->objectName ){
							
							$TestObject = new $fieldParts[0];
						}
					
						$TestObject->updateFields();
						if( isset($TestObject->fields[$fieldParts[1]]['queryType']) ){
							
							$type = $TestObject->fields[$fieldParts[1]]['queryType'];
						}else{
							
							$type = $TestObject->dataType($value);
						}
												
						##faster less durbale code##
						//Determine what type of data is being subbed in, and add it to the parameters string.
						//Then add the value to the array..
						/*
						$type = $this->dataType($value);
						*/
						
						$parameters[0] .= $type;
						
						$conditionString .= " $index ? AND ";
						$parameters[] = $value;
					}
					
				}else{
					
					
					//If there was a numeric index given further inspection required
					if( is_array($value) ){
						
						$innerConditions = $this->processConditions( $value );	
					}
	
					//Need to add the inner conditions to condition return
					$parameters[0] .= $innerConditions['parameters'][0];
					$parameters = array_merge ( $parameters , array_slice($innerConditions['parameters'],1 ));
					$conditionString .= $innerConditions['conditionString'];
					
					//Because these are sub conditions, we must append an AND:
					$conditionString .= " AND ";
				}
			}
		}
		
		//Remove trailing ANDs
		$conditionString = rtrim($conditionString, " AND ");
				
		return array('conditionString'=>$conditionString, 'parameters'=>$parameters);
	}
	
	
	//This function makes an assumption on the type of data 
	private function dataType($data){
		
		// Try to convert the string to a float
		$floatVal = floatval($data);
		
		// If the parsing succeeded and the value is not equivalent to an int
		if($floatVal && intval($floatVal) != $floatVal){
		    
		    // $num is a float
		    return 'd';
		}else if( is_numeric( $data )){
			
			return 'i';
		}else{
			
			return 's';
		}
		
	}
	
	//This function is included to use call_user_func_array in conjunction with mysqli_bind_parm: 
	//http://stackoverflow.com/questions/16120822/mysqli-bind-param-expected-to-be-a-reference-value-given
	//http://php.net/manual/de/mysqli-stmt.bind-param.php
	private function refValues($arr){
	    if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
	    {
	        $refs = array();
	        foreach($arr as $key => $value)
	            $refs[$key] = &$arr[$key];
	        return $refs;
	    }
	    return $arr;
	}
	
	
	//This function sorts a hasmany array by a specified field...
	public function sortHasMany($childName, $field, $type = 0){
		
		$this->compareField = $field;
		$this->compareFieldType = $type;
		
		usort($this->$childName, array($this, "compare"));
		
	}
	
	//Compare function used for comparing to values
	public function compare($a, $b){
		
		if( strtolower($this->compareFieldType) == 'date' ){
			
			if(strtotime($a->{$this->compareField}) > strtotime($b->{$this->compareField})){
				
				return 1;
			}else{
				
				return 0;
			}
		}else{
			
			return strcmp($a->{$this->compareField}, $b->{$this->compareField});
		}
		
	}
}
